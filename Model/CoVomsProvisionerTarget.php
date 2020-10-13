<?php

/**
 * COmanage Registry CO VOMs Provisioner Target Model
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses("CoProvisionerPluginTarget", "Model");
App::uses('Security', 'Utility');
App::uses('Hash', 'Utility');

/**
 * Class VomsProvisionerTarget
 */
class CoVomsProvisionerTarget extends CoProvisionerPluginTarget
{
  // XXX All the classes/models that have tables should start with CO for the case of provisioners
  // Define class name for cake
  public $name = "CoVomsProvisionerTarget";

  // Add behaviors
  public $actsAs = array('Containable');

  // Association rules from this model to other models
  public $belongsTo = array('CoProvisioningTarget');

  // Default display field for cake generated views
  public $displayField = "vo";

  public $hasMany = array(
    "CoVomsProvisionerServer" => array(
      'className' => 'VomsProvisioner.CoVomsProvisionerServer',
      'dependent' => true
    ),
  );

  private $_voms_client = null;

  // Validation rules for table elements
  public $validate = array(
    'co_provisioning_target_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO PROVISIONING TARGET ID must be provided'
    ),
    'vo' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    ),
    'robot_cert' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    ),
    'robot_key' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    ),
    'openssl_syntax' => array(
      'rule' => array('boolean'),
      'required' => false,
      'allowEmpty' => true
    ),
    'ca_dn_default' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
  );

  /**
   * Provision for the specified CO Person.
   *
   * @param Array CO Provisioning Target data
   * @param ProvisioningActionEnum Registry transaction type triggering provisioning
   * @param Array Provisioning data, populated with ['CoPerson'] or ['CoGroup']
   * @return Boolean True on success
   * @throws RuntimeException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @since  COmanage Registry v0.8
   */

  public function provision($coProvisioningTargetData, $op, $provisioningData)
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $this->log(__METHOD__ . "::action => ".$op, LOG_DEBUG);

    // First figure out what to do
    $voremove = false;
    $voadd =false;
    $modify = false;

    switch($op) {
      case ProvisioningActionEnum::CoPersonAdded:
        $voadd = true;
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        $voremove = true;
        break;
      case ProvisioningActionEnum::CoPersonUpdated:
      case ProvisioningActionEnum::CoPersonExpired:
      case ProvisioningActionEnum::CoPersonPetitionProvisioned:
        // An update may cause an existing person to be written to VOMS for the first time
        // or for an unexpectedly removed entry to be replaced
        $modify = true;
        break;
      default:
        // Ignore all other actions
        $this->log(__METHOD__ . '::Provisioning action ' . $op . ' not allowed/implemented', LOG_DEBUG);
        return true;
        break;
    }

    // Return since no flag is on
    if(!$modify && !$voremove && !$voadd){
      return true;
    }

    // Get all the linked tables of my Provisioner
    $coProvisioningTargetData = $this->getFullStructure($coProvisioningTargetData["CoVomsProvisionerTarget"]["id"]);

    // XXX For COU Actions allow only the ones matching the COU name in the configuration of the provisioner
    // XXX For CO Person Actions skip
    if(empty($_REQUEST["data"]["CoPerson"])) {
      $cou_name_frm_request = $this->getCouNameFromRequest();
      if($coProvisioningTargetData["CoVomsProvisionerTarget"]["vo"] !== $cou_name_frm_request) {
        return true;
      }
    }

    // Get first VOMS Alive
    $this->_voms_client = $this->getFirstVomsAlive($coProvisioningTargetData, $coProvisioningTargetData["CoVomsProvisionerServer"]);
    if(is_null($this->_voms_client)) {
      // todo: Perhaps return an error here somewhere
      $this->log(__METHOD__ . '::VOMS client Object is null.', LOG_DEBUG);
      return true;
    }
    // Construct the CO Person's profile
    $user_cou_related_profile = $this->retrieveUserCouRelatedStatus($provisioningData, $coProvisioningTargetData);

    // XXX In order to perform any action we need at least on valid certificate. If none is provided then
    // XXX throw an error
    if(empty($user_cou_related_profile['Cert'])) {
      $this->log(__METHOD__ . '::No valid certificate. Aborting provisioning.', LOG_DEBUG);
      // fixme: Even though i am throwing an exception this is not working
      throw new RuntimeException(_txt('op.voms_provisioner.nocert'));
    }
    // XXX Get the first one and do the action needed
    // fixme: Make the Robot CA configuration
    // fixme: I should only do this with Personal Certificates but i do not have this information in the Model
    if(empty($user_cou_related_profile['Cert'][0]['Cert']['issuer'])) {
      if (empty($coProvisioningTargetData["CoVomsProvisionerTarget"]["ca_dn_default"])) {
        // XXX No default DN, break Provisioning
        return;
      }
      $user_cou_related_profile['Cert'][0]['Cert']['issuer'] = $coProvisioningTargetData["CoVomsProvisionerTarget"]["ca_dn_default"];
    }

    // XXX Now perform an action

    // The CO Person MUST BE DELETED/REMOVED from the VO
    if((empty($user_cou_related_profile["CoPersonRole"])
        && ($modify || $voremove))                                                                             // Removed from COU/VO
            || ( !empty($user_cou_related_profile["CoPersonRole"])
                  && ( ($user_cou_related_profile["CoPersonRole"][0]["status"] !== StatusEnum::Active                // COU/VO Active
                        && $user_cou_related_profile["CoPersonRole"][0]["status"] !== StatusEnum::GracePeriod)       // COU/VO GracePeriod
                        || ($user_cou_related_profile["CoPerson"]["status"] !== StatusEnum::Active                   // COPerson Active
                            && $user_cou_related_profile["CoPerson"]["status"] !== StatusEnum::GracePeriod)))) {     // COPerson GracePerio
      // fixme: How to do i know the $dn and $ca that the user used to register
      $response = $this->_voms_client->deleteUser($user_cou_related_profile['Cert'][0]['Cert']['subject'],
                                                  $user_cou_related_profile['Cert'][0]['Cert']['issuer']);

      // todo: handle the response
      $this->plogs(__METHOD__, $response);
      $this->handleResponse($response);
      return true;
    }

    // The user is in the COU
    if(!empty($user_cou_related_profile["CoPersonRole"])) {
      if($modify && $user_cou_related_profile["CoPersonRole"][0]["status"] === StatusEnum::Suspended) {
          // todo: I will translate this as suspended action
          return true;
      }
      // XXX add user into VOMS
      $user_payload = $this->getUserData($user_cou_related_profile, $provisioningData);
      $response = $this->_voms_client->createUser($user_payload);
      // todo: handle the response
      $this->plogs(__METHOD__, $response);
      $this->handleResponse($response);
    }

    return true;
  }


  /**
   * CO Person profile based on COU and Group ID. The profile is constructed based on OrgIdentities linked to COPerson.
   *
   * @param array $provisioningData
   * @param array $coProvisioningTargetData
   * @return array
   */
  protected function retrieveUserCouRelatedStatus($provisioningData, $coProvisioningTargetData) {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $args = array();
    $args['conditions']['CoProvisioningTarget.id'] = $coProvisioningTargetData["CoVomsProvisionerTarget"]["co_provisioning_target_id"];
    $args['fields'] = array('provision_co_group_id');
    $args['contain']= false;
    $provision_group_ret = $this->CoProvisioningTarget->find('first', $args);
    $co_group_id = $provision_group_ret["CoProvisioningTarget"]["provision_co_group_id"];

    $user_memberships_profile = !is_array($provisioningData['CoGroupMember']) ? array()
                                : Hash::flatten($provisioningData['CoGroupMember']);

    $in_group = array_search($co_group_id, $user_memberships_profile, true);

    if(!empty($in_group)){
      $index = explode('.', $in_group, 2)[0];
      $user_membership_status = $provisioningData['CoGroupMember'][$index];
      // XXX Do not set the cou_id unless you are certain of its value
      $cou_id = !empty($user_membership_status["CoGroup"]["cou_id"]) ? $user_membership_status["CoGroup"]["cou_id"] : null;
    }

    // Create the profile of the user according to the group_id and cou_id of the provisioned
    // resources that we configured
    // XXX i can not let COmanage treat $cou_id = null as ok since i allow Null COUs. This means that
    // XXX we will get back the default CO Role, which will be the wrong one.
    $args = array();
    $args['conditions']['CoPerson.id'] = $provisioningData["CoPerson"]["id"];
    if(isset($cou_id)) {
      $args['contain']['CoPersonRole'] = array(
        'conditions' => ['CoPersonRole.cou_id' => $cou_id],  // XXX Be carefull with the null COUs
      );
    }
    $args['contain']['CoGroupMember']= array(
      'conditions' => ['CoGroupMember.co_group_id' => $co_group_id],
    );
    $args['contain']['CoGroupMember']['CoGroup'] = array(
      'conditions' => ['CoGroup.id' => $co_group_id],
    );
    $args['contain']['CoOrgIdentityLink']['OrgIdentity']['Cert'] = array(
      'conditions' => ['Cert.issuer is not null'],
    );

    // XXX Filter with this $user_profile["CoOrgIdentityLink"][2]["OrgIdentity"]["Cert"]
    // XXX We can not perform any action with VOMS without a Certificate having both a subjectDN and an Issuer
    // XXX Keep in depth level 1 only the non empty Certificates
    $user_profile = $this->CoProvisioningTarget->Co->CoPerson->find('first', $args);

    foreach($user_profile["CoOrgIdentityLink"] as $link) {
      if(!empty($link["OrgIdentity"]["Cert"])) {
        foreach ($link["OrgIdentity"]["Cert"] as $cert) {
          $user_profile['Cert'][] = $cert;
        }
      }
    }

    // No lets fetch the orgidentities linked with the certificates
    if(!empty($user_profile['Cert'])) {
      // Extract the Certificate ids
      $cert_ids = Hash::extract($user_profile['Cert'], '{n}.id');
      $args=array();
      $args['conditions']['Cert.id'] = $cert_ids;
      $args['contain'] = array('OrgIdentity');
      $args['contain']['OrgIdentity'][0] = 'TelephoneNumber';
      $args['contain']['OrgIdentity'][1] = 'Address';
      $this->Cert = ClassRegistry::init('Cert');
      $user_profile['Cert'] = $this->Cert->find('all', $args);
    }

    return $user_profile;
  }

  /**
   * @param $coProvisioningTargetData
   * @return false|string
   * @throws InvalidArgumentException
   */
  protected function getRobotCert($coProvisioningTargetData) {
    if(empty($coProvisioningTargetData["CoVomsProvisionerTarget"]["robot_cert"])) {
      throw new InvalidArgumentException(_txt('er.notfound',
        array(_txt('ct.co_voms_provisioner_targets.1'), _txt('pl.voms_provisioner.robot_cert'))));
    }
    $robot_cert = base64_decode($coProvisioningTargetData["CoVomsProvisionerTarget"]["robot_cert"]);
    return $robot_cert;
  }

  /**
   * @param $coProvisioningTargetData
   * @return false|string
   * @throws InvalidArgumentException
   */
  protected function getRobotKey($coProvisioningTargetData) {
    if(empty($coProvisioningTargetData["CoVomsProvisionerTarget"]["robot_key"])) {
      throw new InvalidArgumentException(_txt('er.notfound',
        array(_txt('ct.co_voms_provisioner_targets.1'), _txt('pl.voms_provisioner.robot_key'))));
    }
    Configure::write('Security.useOpenSsl', true);
    $robot_key = Security::decrypt(base64_decode($coProvisioningTargetData["CoVomsProvisionerTarget"]["robot_key"]), Configure::read('Security.salt'));
    return $robot_key;
  }

  /**
   * Actions to take before a save operation is executed.
   *
   * @since  COmanage Registry v3.1.0
   */

  public function beforeSave($options = array())
  {
    $key = Configure::read('Security.salt');
    Configure::write('Security.useOpenSsl', true);
    if(!empty($this->data["CoVomsProvisionerTarget"]["robot_key"])) {
      $stored_key = (!is_null($this->id)) ? $this->field('robot_key', ['id' => $this->id]) : '';
      if($stored_key !== $this->data["CoVomsProvisionerTarget"]["robot_key"]) {
        $robot_key = base64_encode(Security::encrypt(base64_decode($this->data["CoVomsProvisionerTarget"]["robot_key"]), $key));
        $this->data["CoVomsProvisionerTarget"]["robot_key"] = $robot_key;
      }
    }
  }

  /**
   * @param string $protocol
   * @param string $host
   * @param integer $port
   * @param string $vo_name
   * @param string $robot_cert
   * @param string $robot_key
   * @return Object VomsClient
   */
  protected function getVomsClient($protocol, $host, $port, $vo_name, $robot_cert, $robot_key, $openssl_syntax) {
    if(is_null($this->_voms_client)) {
      $this->_voms_client = new VomsClient($protocol, $host, $port, $vo_name, $robot_cert, $robot_key, $openssl_syntax);
    }
    return $this->_voms_client;
  }

  /**
   * array (
      'user' => array(
        'emailAddress' => 'ioigoume@test.com',
        'institution' => 'Dummy Test',
        'phoneNumber' => '6936936937',
        'surname' => 'Igoumenos',
        'name' => 'Ioannis',
        'address' => 'No where....',
      ),
      'certificateSubject' => $dn,
      'caSubject' => $ca,
     );
   * @param array $user_profile
   * @param array $provisioningData
   * @return array
   * @todo constuct the correct format of user data. We need different format for Soap and Rest
   */
  protected function getUserData($user_profile, $provisioningData) {
    $user_data = array();
    $user_data['user']['emailAddress'] = !empty($provisioningData["EmailAddress"][0]["mail"])
                                         ? $provisioningData["EmailAddress"][0]["mail"]
                                         : 'unknown@mail.com';
    $user_data['user']['surname'] = $provisioningData["PrimaryName"]["family"];
    $user_data['user']['name'] = $provisioningData["PrimaryName"]["given"];
    $user_data['user']['phoneNumber'] = !empty($user_profile["Cert"][0]["OrgIdentity"]["TelephoneNumber"])
                                        ? $user_profile["Cert"][0]["OrgIdentity"]["TelephoneNumber"][0]['number']
                                        : '696969699';
    $user_data['user']['institution'] = !empty($user_profile["Cert"][0]["OrgIdentity"]["o"])
                                        ? $user_profile["Cert"][0]["OrgIdentity"]["o"]
                                        : $this->getOFromSbjtDN($user_profile['Cert'][0]['Cert']['subject']);
    $user_data['certificateSubject'] = $user_profile['Cert'][0]['Cert']['subject'];
    $user_data['caSubject'] = $user_profile['Cert'][0]['Cert']['issuer'];
    $user_data['user']['address'] = 'Unknown';

    if(!empty($user_profile["Cert"][0]["OrgIdentity"]["Address"])) {
      $street = $user_profile["Cert"][0]["OrgIdentity"]["Address"][0]['street'];
      $country = $user_profile["Cert"][0]["OrgIdentity"]["Address"][0]['country'];
      $user_data['user']['address'] = $street . '/' . $country;
    }

    return $user_data;
  }

  /**
   * Extract Organization(O) from Subject DN
   * @param string $subjectDN.
   * @return string Organization or empty
   */
  protected function getOFromSbjtDN($subjectDN) {
    $re = '/O=(.*?)[\/,].*/m';
    preg_match_all($re, $subjectDN, $matches, PREG_SET_ORDER, 0);
    if(!empty($matches[0][1])) {
      return $matches[0][1];
    }
    return 'Unknown';
  }

  /**
   * Extract Canonical Name(CN) from Subject DN
   * @param string $subjectDN.
   * @return string Organization or empty
   */
  protected function getCNFromSbjtDN($subjectDN) {
    $re = '/CN=(.*?)[\/,].*/m';
    preg_match_all($re, $subjectDN, $matches, PREG_SET_ORDER, 0);
    if(!empty($matches[0][1])) {
      return $matches[0][1];
    }
    return 'Unknown';
  }

  /**
   * @param $method
   * @param $response
   */
  protected function plogs($method, $response) {
    if(is_array($response)) {
      $this->log($method . "::" . print_r($response, true), LOG_DEBUG);
    } else {
      $this->log($method . "::" . $response, LOG_DEBUG);
    }
  }

  /**
   * @param array $response ['status_code' => integer, 'msg' => string] Response returned by the request action
   */
  protected function handleResponse($response) {
    if(!is_array($response)) {
      return;
    }
    if($response['status_code'] === 0) {
      throw new RuntimeException($response['msg']);
    }
  }

  /**
   * @param integer $id  The $id of the Provisioner's entry in the table
   * @return array|null  Provsioner's (linked) data
   */
  public function getFullStructure($id) {
    $args = array();
    $args['conditions']['CoVomsProvisionerTarget.id'] = $id;
    $args['contains'] = array('CoVomsProvisionerTargetServer');
    $data = $this->find('first', $args);
    return $data;
  }

  /**
   * @param $coProvisioningTargetData
   * @param $serverlist
   * @return Object|VomsClient
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function getFirstVomsAlive($coProvisioningTargetData, $serverlist) {
    // fixme: Make sure that we do not let the user save less than what we need
    if(empty($serverlist)) {
      throw new InvalidArgumentException(_txt('er.notfound',
        array(_txt('ct.co_voms_provisioner_targets.1'), _txt('er.voms_provisioner.nohst_prt'))));
    }
    // Get my certificates
    $robot_cert = $this->getRobotCert($coProvisioningTargetData);
    $robot_key = $this->getRobotKey($coProvisioningTargetData);
    // Instantiate VOMS Client

    foreach($serverlist as $server) {
      $voms_client = $this->getVomsClient(
        $server["protocol"],
        $server["host"],
        $server["port"],
        $coProvisioningTargetData["CoVomsProvisionerTarget"]['vo'],
        $robot_cert,
        $robot_key,
        $coProvisioningTargetData["CoVomsProvisionerTarget"]['openssl_syntax']);
      if(!is_null($voms_client)) {
        $response = $voms_client->getUserStats();
        if($response["status_code"] === 200) {
          $this->plogs(__METHOD__, $server["protocol"] . "//:" . $server["host"] . ':' . $server["port"] . ' is alive.');
          return $voms_client;
        }
        $voms_client = null;
      }
    }
  }

  /**
   * This provisioner refer only to COU entities. As a result if no COU is present we should skip the plugin
   * @return string COU name if exists, empty string otherwise
   */
  private function getCouNameFromRequest() {
    if(!empty($_REQUEST["data"]["CoPersonRole"]["cou_id"])) { // Post Actions
      $this->Cou = ClassRegistry::init('Cou');
      return $this->Cou->field('name', array('id' => $_REQUEST["data"]["CoPersonRole"]["cou_id"]));
    } elseif(is_array($_REQUEST)) {                           // Delete Actions
      $request = array_keys($_REQUEST);
      $req_path = explode('/', $request[0]);
      $req_path = array_filter($req_path); // removing blank, null, false, 0 (zero) values
      // XXX We only want to move forward if this refers to CoPersonRole or CoPerson(?)
      if(!in_array('co_person_roles', $req_path)) {
        return '';
      }
      $co_person_role_id = end($req_path);
      $args = array();
      $args['joins'][0]['table'] = 'cous';
      $args['joins'][0]['alias'] = 'Cou';
      $args['joins'][0]['type'] = 'INNER';
      $args['joins'][0]['conditions'] = 'Cou.id=CoPersonRole.cou_id';
      $args['conditions']['CoPersonRole.id'] = $co_person_role_id;
      $args['contain'] = false;
      $args['fields'] = array('Cou.name');
      $this->CoPersonRole = ClassRegistry::init('CoPersonRole');
      $ret = $this->CoPersonRole->find('first',$args);
      return $ret["Cou"]["name"];
    }
    return '';
  }
}
