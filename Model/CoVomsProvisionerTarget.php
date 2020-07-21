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

  public $duplicatableModels = array(
    // Must explicitly list this model in the order it should be duplicated
    "CoVomsProvisionerTarget" => array(
      "parent" => "CoProvisioningTarget",
      "fk"     => "co_provisioning_target_id"
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
    'co_voms_provisioning_server_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO VOMS PROVISIONING SERVER ID must be provided'
    ),
    'vo' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'robot_cert' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'robot_key' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    )
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

    $user_cou_related_profile = $this->retrieveUserCouRelatedStatus($provisioningData, $coProvisioningTargetData);

    // XXX In order to perform any action we need at least on valid certificate. If none is provided then
    // XXX throw an error
    if(empty($user_cou_related_profile['Cert'])) {
      // fixme: Even though i am throwing an exception this is not working
      throw new RuntimeException(_txt('op.voms_provisioner.nocert'));
    }

    //XXX Get an instance to the Rest and Soap Clients
    if($modify || $voremove || $voadd){
      // Get my certificates
      $robot_cert = $this->getRobotCert($coProvisioningTargetData);
      $robot_key = $this->getRobotKey($coProvisioningTargetData);
      // Instantiate VOMS Client
      $this->_voms_client = $this->getVomsClient($coProvisioningTargetData["CoVomsProvisionerTarget"]['host'],
        $coProvisioningTargetData["CoVomsProvisionerTarget"]['port'],
        $coProvisioningTargetData["CoVomsProvisionerTarget"]['vo'],
        $robot_cert,
        $robot_key);
    } else {
      return true;
    }

    // XXX Now perform an action

    // The CO Person is not part of the COU
    if(empty($user_cou_related_profile["CoPersonRole"])
       && ($modify || $voremove)) {
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
      if($modify
         && ($user_cou_related_profile["CoPersonRole"][0]["status"] === StatusEnum::Expired
             || $user_cou_related_profile["CoPersonRole"][0]["status"] === StatusEnum::Suspended)) {
          // XXX I will translate this as suspended
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
    if(empty($coProvisioningTargetData["CoVomsProvisionerTarget"]['host'])
       || empty($coProvisioningTargetData["CoVomsProvisionerTarget"]['port'])){
      throw new InvalidArgumentException(_txt('er.notfound',
        array(_txt('ct.co_voms_provisioner_targets.1'), _txt('er.voms_provisioner.nohst_prt'))));
    }
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
      // XXX Do not set the cou_id unless you are certain of it value
      $cou_id = $user_membership_status["CoGroup"]["cou_id"];
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
    $cert_base64 = $coProvisioningTargetData["CoVomsProvisionerTarget"]["robot_cert"];
    return base64_decode($cert_base64);
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
    $key_base64 = $coProvisioningTargetData["CoVomsProvisionerTarget"]["robot_key"];
    return base64_decode($key_base64);
  }

  /**
   * @param string $host
   * @param integer $port
   * @param string $vo_name
   * @param string $robot_cert
   * @param string $robot_key
   * @return Object VomsClient
   */
  protected function getVomsClient($host, $port, $vo_name, $robot_cert, $robot_key) {
    if(is_null($this->_voms_client)) {
      $this->_voms_client = new VomsClient($host, $port, $vo_name, $robot_cert, $robot_key);
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
}
