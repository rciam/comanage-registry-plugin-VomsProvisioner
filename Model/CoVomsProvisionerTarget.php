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
  private $_subject_col = null;
  private $_issuer_col = null;
  private $_Cert = null;
  private $_assurance_level = null;

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
    'cert_mdl' => array(
      'rule' => '/.*/',
      'required' => true,
      'allowEmpty' => false
    ),
    'subject_col_name' => array(
      'rule' => '/.*/',
      'required' => true,
      'allowEmpty' => false
    ),
    'issuer_col_name' => array(
      'rule' => '/.*/',
      'required' => true,
      'allowEmpty' => false
    ),
    'assurance_level' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => true
    ),
    'assurance_level_type' => array(
      'rule' => 'notBlank',
      'required' => true,
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

    // Certificate
    $this->_Cert = $coProvisioningTargetData["CoVomsProvisionerTarget"]["cert_mdl"];
    $this->_subject_col = $coProvisioningTargetData["CoVomsProvisionerTarget"]["subject_col_name"];
    $this->_issuer_col = $coProvisioningTargetData["CoVomsProvisionerTarget"]["issuer_col_name"];
    if(!empty($coProvisioningTargetData["CoVomsProvisionerTarget"]["assurance_level_type"])
       && !empty($coProvisioningTargetData["CoVomsProvisionerTarget"]["assurance_level"])) {
      $this->_assurance_level =
        $coProvisioningTargetData["CoVomsProvisionerTarget"]["assurance_level_type"]
        . "@"
        . $coProvisioningTargetData["CoVomsProvisionerTarget"]["assurance_level"];
    }

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
    $request_keys = array_keys($_REQUEST);
    // Is the request part of a petition
    $is_petition = array_filter(
      $request_keys,
      function($val) {
        return strpos($val, 'co_petitions') !== false;
      }
    );
    if(empty($_REQUEST["data"]["CoPerson"])
       && empty($is_petition)) {
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
    if(empty($user_cou_related_profile[$this->_Cert])) {
      $this->log(__METHOD__ . '::No valid certificate. Aborting provisioning.', LOG_DEBUG);
      // fixme: Even though i am throwing an exception this is not working
      throw new RuntimeException(_txt('op.voms_provisioner.nocert'));
    }

    ///// MOVE TO SEPARATE FUNCTION getCertificateByLoA($user_cou_related_profile, $assurance_prerequisite)
    // XXX Check the level of assurance
    $org_list = Hash::extract($user_cou_related_profile[$this->_Cert], '{n}.OrgIdentity.id');
    $cert_list = Hash::combine(
      $user_cou_related_profile[$this->_Cert],
      '{n}.' . $this->_Cert . '.id',
      array( '%s@separator@%s@separator@%d',
        '{n}.' . $this->_Cert . '.' . $this->_subject_col,
        '{n}.' . $this->_Cert . '.' . $this->_issuer_col,
        '{n}.' . $this->_Cert . '.ordr'),
      '{n}.' . $this->_Cert . '.org_identity_id');
    $ident_list = Hash::combine(
      $user_cou_related_profile[$this->_Cert],
      '{n}.OrgIdentity.Identifier.{n}.id',
      '{n}.OrgIdentity.Identifier.{n}.identifier',
      '{n}.OrgIdentity.Identifier.{n}.org_identity_id');
    $assurance_list = Hash::combine(
      $user_cou_related_profile[$this->_Cert],
      '{n}.OrgIdentity.Assurance.{n}.id',
      array( '%s@%s', '{n}.OrgIdentity.Assurance.{n}.type', '{n}.OrgIdentity.Assurance.{n}.value'),
      '{n}.OrgIdentity.Assurance.{n}.org_identity_id');

    $processed_list = array();
    foreach( $org_list as $org_id) {
      $processed_list[$org_id][$this->_Cert] = !empty($cert_list[$org_id]) ? $cert_list[$org_id] : array();
      $processed_list[$org_id]['Identifier'] = !empty($ident_list[$org_id]) ? $ident_list[$org_id] : array();
      $processed_list[$org_id]['Assurance'] = !empty($assurance_list[$org_id]) ? $assurance_list[$org_id] : array();
    }

    // Explode certificate information
    foreach($processed_list as $org_id => $orgid_models) {
      if(!empty($orgid_models[$this->_Cert])) {
        foreach($orgid_models[$this->_Cert] as $certid => $denseval) {
          list($subjectex, $issuerex, $ordrex) = explode('@separator@', $denseval);
          $processed_list[$org_id][$this->_Cert][$certid] = array(
            $this->_issuer_col => $issuerex,
            $this->_subject_col => $subjectex,
            'ordr' => (int)$ordrex,
          );
        }
      }
    }

    // Order paths according to Certificate Ordering
    $flattened_proc_list = Hash::flatten($processed_list);
    $ordering_flatten = array_filter(
      $flattened_proc_list,
      function ($value, $key) {
        return (strpos($key, '.ordr') !== false);
      },
      ARRAY_FILTER_USE_BOTH
    );
    asort($ordering_flatten);

    // XXX Iterate over OrgIdentities and get the first which:
    // * assurane level matches the level requested by the configuration
    // * Has a certificate
    // Store the orgId into a variable in order to use below
    $issuer = null;
    $subject = null;
    $org_id_picked = null;
    $cert_id_picked = null;
    foreach($ordering_flatten as $path => $order) {
      $full_path = Hash::expand(array($path => $order));
      $org_id = key($full_path);
      $cert_id = key($full_path[$org_id][$this->_Cert]);
      $orgid_models = $processed_list[$org_id];
      $has_assurance = in_array($this->_assurance_level, $orgid_models['Assurance']) ? true : false;

      if(!$has_assurance
         && $this->assuranceValueOrder($this->_assurance_level) > 0
         && $this->assuranceValueOrder($orgid_models['Assurance']) > 0 ) {
        $required_assurance_order = $this->assuranceValueOrder($this->_assurance_level);
        $org_assurance_order = $this->assuranceValueOrder($orgid_models['Assurance']);
        if($org_assurance_order >= $required_assurance_order) {
          $has_assurance = true;
        }
      }
      $has_certificate = false;

      if(!empty($processed_list[$org_id][$this->_Cert])) {
        $certificate = $processed_list[$org_id][$this->_Cert][$cert_id];
        if(!empty($certificate[$this->_subject_col])
          && !empty($certificate[$this->_issuer_col])) {
          $has_certificate = true;
        }
      }
      if($has_assurance && $has_certificate) {
        $issuer = $certificate[$this->_issuer_col];
        $subject = $certificate[$this->_subject_col];
        $org_id_picked = $org_id;
        $cert_id_picked = $cert_id;
        break;
      }
    }

    // SEPARATE FUNCTION ABOVE //

    // Found no matching assurance - cert bundle
    if(is_null($issuer) || is_null($subject)) {
      $this->log(__METHOD__ . '::No valid certificate. Aborting provisioning.', LOG_DEBUG);
      // fixme: Even though i am throwing an exception this is not working
      throw new RuntimeException(_txt('op.voms_provisioner.nocert'));
      return false;
    }

    // Get the index(idx) that match the search from above and use it in $user_cou_related_profile array
    $user_cou_related_profile_flatten = Hash::flatten($user_cou_related_profile);
    $keys_found = array_filter(
      $user_cou_related_profile_flatten,
      function ($value, $key) use ($cert_id_picked) {
        return ((int)$value === (int)$cert_id_picked
                 && strpos($key, 'CoOrgIdentityLink.') === false);
      },
      ARRAY_FILTER_USE_BOTH
    );
    $idx = -1;
    foreach($keys_found as $path => $value) {
      if(strpos($path, $this->_Cert . '.') !== false) {
        $re = '/Cert.(\d+).(?:.*)/m';
//        preg_match($re, $path, $match, PREG_UNMATCHED_AS_NULL); // php 7.2+
        preg_match($re, $path, $match);
        $idx = ( !empty($match) && isset($match[1]) ) ? (int)$match[1] : -1;
        break;
      }
    }

    // Found no matching assurance - cert bundle
    if($idx < 0) {
      $this->log(__METHOD__ . '::No valid certificate. Aborting provisioning.', LOG_DEBUG);
      // fixme: Even though i am throwing an exception this is not working
      throw new RuntimeException(_txt('op.voms_provisioner.nocert'));
      return false;
    }


    // XXX Now perform an action

    // The CO Person MUST BE DELETED/REMOVED from the VO
    if((empty($user_cou_related_profile["CoPersonRole"])
        && ($modify || $voremove))                                                                             // Removed from COU/VO
            || ( !empty($user_cou_related_profile["CoPersonRole"])
                  && ( ($user_cou_related_profile["CoPersonRole"][0]["status"] !== StatusEnum::Active                // COU/VO Active
                        && $user_cou_related_profile["CoPersonRole"][0]["status"] !== StatusEnum::GracePeriod)       // COU/VO GracePeriod
                        || ($user_cou_related_profile["CoPerson"]["status"] !== StatusEnum::Active                   // COPerson Active
                            && $user_cou_related_profile["CoPerson"]["status"] !== StatusEnum::GracePeriod)))) {     // COPerson GracePeriod
      // Get the Certificate from the CO Person Role
      $co_person_role_id = $this->getRoleIDromRequest($provisioningData, $coProvisioningTargetData);
      $subject_linked = null;
      $issuer_linked = null;
      if(!empty($_SESSION['ProvisionerCertRecord'])) {
        $session_coperson_role_id = !empty($_SESSION['ProvisionerCertRecord']['co_person_role_id']) ? $_SESSION['ProvisionerCertRecord']['co_person_role_id'] : -1;
        if($session_coperson_role_id === $co_person_role_id
           && !empty($_SESSION['ProvisionerCertRecord']['cert_id'])) {
          $Cert = ClassRegistry::init($this->_Cert);
          $Cert->id = $_SESSION['ProvisionerCertRecord']['cert_id'];
          $subject_linked = $Cert->field($this->_subject_col);
          $issuer_linked = $Cert->field($this->_issuer_col);
        }
        unset($_SESSION['ProvisionerCertRecord']);
      }

      // Delete the user
      $removed_from_vo = false;
      if (!is_null($subject_linked) && !is_null($issuer_linked)) {
        $response = $this->_voms_client->deleteUser($subject_linked,$issuer_linked);
        $removed_from_vo = true;
      } else {
        $response = $this->_voms_client->deleteUser($user_cou_related_profile[$this->_Cert][$idx][$this->_Cert][$this->_subject_col],
                                                    $user_cou_related_profile[$this->_Cert][$idx][$this->_Cert][$this->_issuer_col]);
        $removed_from_vo = true;
      }

      // XXX In case the CoPersonRole gets update in a status other than Active or Grace Period, we need to remove
      // the linked Certificate manually
      if( $removed_from_vo ) {
        $cert_records = ClassRegistry::init('ProvisionerCertRecord');
        $crf_args = array();
        $crf_args['conditions']['ProvisionerCertRecord.co_person_role_id'] = $co_person_role_id;
        $crf_args['fields'] = array('ProvisionerCertRecord.id');
        $crf_args['contain'] = false;
        $prov_cert_records = $cert_records->find('all', $crf_args);

        // Delete the linked Certificate
        foreach ($prov_cert_records as $clinks) {
          $cert_records->delete($clinks['ProvisionerCertRecord']["id"]);
        }
      }

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
      $user_payload = $this->getUserData($user_cou_related_profile, $provisioningData, $idx);
      $response = $this->_voms_client->createUser($user_payload);
      $user_invo = false;
      // User already registered
      if(!empty($response["msg"])
         && strpos($response["msg"], "A user holding a certificate with the following subject") !== false ) {
        $user_invo = true;
      }
      // Registration failed
      $reg_ok = true;
      if(!empty($response["data"])
        && strpos($response["data"], "exception") !== false ) {
        $reg_ok = false;
      }
      // On a successful provisioning create a new entry in the database
      if(!empty($response["status_code"])
         && $response["status_code"] === 200
         && $reg_ok
         && !$user_invo) {
        // Create an entry in Provisioner Cert Records
        $co_person_role_id = $this->getRoleIDromRequest($provisioningData, $coProvisioningTargetData);
        // Check if we already have an entry in the database for this CO Person Role and Cert
        $cert_records = ClassRegistry::init('ProvisionerCertRecord');
        $crf_args = array();
        $crf_args['conditions']['ProvisionerCertRecord.cert_id'] = $cert_id;
        $crf_args['conditions']['ProvisionerCertRecord.co_person_role_id'] = $co_person_role_id;
        $crf_args['contain'] = false;
        $prov_cert_record_count = $cert_records->find('count', $crf_args);
        // If we have no prior record of this Certificate/Role combination, create one
        if($prov_cert_record_count == 0) {
          $cert_entry = array(
            'ProvisionerCertRecord' => array(
              'cert_id' => $cert_id,
              'co_person_role_id' => $co_person_role_id,
              'actor_identifier' => $_SESSION["Auth"]["User"]["username"]
            ),
          );

          $save_options = array(
            'validate' => true,
            'atomic' => true,
            'provisioning' => false,
          );

          if($cert_records->save($cert_entry, $save_options)) {
            $this->log(__METHOD__ . "::Provisioner Cert Record saved successfully ", LOG_DEBUG);
          } else {
            $invalidFields = $cert_records->invalidFields();
            $this->log(__METHOD__ . "::exception error => " . print_r($invalidFields, true), LOG_DEBUG);
          }
        }
      }

      // handle the response
      $this->plogs(__METHOD__, $response);
      $this->handleResponse($response);
    }

    return true;
  }

  /**
   * @param $level
   * @return int
   */
  public function assuranceValueOrder($level) {
    // Undefined value leve
    if(empty($level)) {
      return -1;
    }
    // Non recognized level in order array
    if(empty($order[$level])) {
      return -1;
    }

    $order = array(
      'profile@https://aai.egi.eu/LoA#Low' => 1,
      'profile@https://aai.egi.eu/LoA#Substantial' => 2,
    );

    return $order[$level];
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
    // todo: Check if the Cert is linked under OrgIdentity or CO Person
    $args['contain']['CoOrgIdentityLink']['OrgIdentity'] = array(
      'Assurance',                                                // Include Assurances
      $this->_Cert => array(                                      // Include Certificates
        'conditions' => [$this->_Cert . '.' . $this->_issuer_col . ' is not null'],
      ),
    );

    // XXX Filter with this $user_profile["CoOrgIdentityLink"][2]["OrgIdentity"][$this->_Cert]
    // XXX We can not perform any action with VOMS without a Certificate having both a subjectDN and an Issuer
    // XXX Keep in depth level 1 only the non empty Certificates
    $user_profile = $this->CoProvisioningTarget->Co->CoPerson->find('first', $args);

    foreach($user_profile["CoOrgIdentityLink"] as $link) {
      if(!empty($link["OrgIdentity"][$this->_Cert])) {
        foreach ($link["OrgIdentity"][$this->_Cert] as $cert) {
          $user_profile[$this->_Cert][] = $cert;
        }
      }
    }

    // Fetch the orgidentities linked with the certificates
    if(!empty($user_profile[$this->_Cert])) {
      // Extract the Certificate ids
      // todo: Check if the Model is linked to CO Person, OrgIdentity or Both
      $cert_ids = Hash::extract($user_profile[$this->_Cert], '{n}.id');
      $args=array();
      $args['conditions'][$this->_Cert . '.id'] = $cert_ids;
      $args['contain'] = array('OrgIdentity');
      $args['contain']['OrgIdentity'][] = 'TelephoneNumber';
      $args['contain']['OrgIdentity'][] = 'Address';
      $args['contain']['OrgIdentity'][] = 'Assurance';
      $args['contain']['OrgIdentity'][] = 'Identifier';
      $this->Cert = ClassRegistry::init($this->_Cert);
      $user_profile[$this->_Cert] = $this->Cert->find('all', $args);
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
    // XXX Handle the Robot private key
    $key = Configure::read('Security.salt');
    Configure::write('Security.useOpenSsl', true);
    if(!empty($this->data["CoVomsProvisionerTarget"]["robot_key"])) {
      $stored_key = (!is_null($this->id)) ? $this->field('robot_key', ['id' => $this->id]) : '';
      if($stored_key !== $this->data["CoVomsProvisionerTarget"]["robot_key"]) {
        $robot_key = base64_encode(Security::encrypt(base64_decode($this->data["CoVomsProvisionerTarget"]["robot_key"]), $key));
        $this->data["CoVomsProvisionerTarget"]["robot_key"] = $robot_key;
      }
    }

    // XXX Check that the Cert Model exists and has the required fields. Subject and Issuer
    if(!empty($this->data["CoVomsProvisionerTarget"]["cert_mdl"])) {
      $Cert = $this->data["CoVomsProvisionerTarget"]["cert_mdl"];
      $this->_subject_col = $this->data["CoVomsProvisionerTarget"]["subject_col_name"];
      $issuer_clmn = $this->data["CoVomsProvisionerTarget"]["issuer_col_name"];
      $this->_Cert = ClassRegistry::init($Cert);
      // XXX Check if the Model Exists
      if(empty($this->_Cert)) {
        return false;
      }
      $validate = $this->_Cert->validate;
      $columns = array_keys($validate);
      // XXX Check if there is a Subject column
      if(!in_array($this->_subject_col, $columns)) {
        return false;
      }
      // XXX Check if there is an Issuer column
      if(!in_array($issuer_clmn, $columns)) {
        return false;
      }
    }

    return true;
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
   * @param integer $index
   * @return array
   * @todo constuct the correct format of user data. We need different format for Soap and Rest
   */
  protected function getUserData($user_profile, $provisioningData, $idx = 0) {
    $user_data = array();
    $user_data['user']['emailAddress'] = !empty($provisioningData["EmailAddress"][0]["mail"])
                                         ? $provisioningData["EmailAddress"][0]["mail"]
                                         : 'unknown@mail.com';
    $user_data['user']['surname'] = $provisioningData["PrimaryName"]["family"];
    $user_data['user']['name'] = $provisioningData["PrimaryName"]["given"];
    $user_data['user']['phoneNumber'] = !empty($user_profile[$this->_Cert][$idx]["OrgIdentity"]["TelephoneNumber"])
                                        ? $user_profile[$this->_Cert][$idx]["OrgIdentity"]["TelephoneNumber"][0]['number']
                                        : '696969699';
    $user_data['user']['institution'] = !empty($user_profile[$this->_Cert][$idx]["OrgIdentity"]["o"])
                                        ? $user_profile[$this->_Cert][$idx]["OrgIdentity"]["o"]
                                        : $this->getOFromSbjtDN($user_profile[$this->_Cert][$idx][$this->_Cert][$this->_subject_col]);
    $user_data['certificateSubject'] = $user_profile[$this->_Cert][$idx][$this->_Cert][$this->_subject_col];
    $user_data['caSubject'] = $user_profile[$this->_Cert][$idx][$this->_Cert][$this->_issuer_col];
    $user_data['user']['address'] = 'Unknown';

    if(!empty($user_profile[$this->_Cert][$idx]["OrgIdentity"]["Address"])) {
      $street = $user_profile[$this->_Cert][$idx]["OrgIdentity"]["Address"][0]['street'];
      $country = $user_profile[$this->_Cert][$idx]["OrgIdentity"]["Address"][0]['country'];
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
        if(!empty($response["status_code"])
           && $response["status_code"] === 200) {
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


  /**
   * @param $provisioningData
   * @return int|null
   */
  private function getRoleIDromRequest($provisioningData, $coProvisioningTargetData) {
    // 'CoPersonRole.{n}.Cou.name'
    $cou_name = $coProvisioningTargetData["CoVomsProvisionerTarget"]["vo"];
    $provisioningData_flatten = Hash::flatten($provisioningData);
    $cou_paths = array_filter(
      $provisioningData_flatten,
      function ($val, $path) use ($cou_name) {
        return ($cou_name === $val
                && strpos($path, '.Cou.name') !== false);
      },
      ARRAY_FILTER_USE_BOTH
    );
    if(!empty($_REQUEST["data"]["CoPersonRole"]["cou_id"])
       && $_REQUEST["_method"] == "POST") { // Post Actions
      $cou_id = $_REQUEST["data"]["CoPersonRole"]["cou_id"];
      $flatten_prov_data = Hash::flatten($provisioningData);
      $keys_found = array_filter(
        $flatten_prov_data,
        function ($value, $key) use ($cou_id) {
          return ((int)$value === (int)$cou_id
                  && strpos($key, 'Cou.id') !== false);
        },
        ARRAY_FILTER_USE_BOTH
      );
      $full_path = Hash::expand($keys_found);
      $role_idx = key($full_path['CoPersonRole']);

      return (int)$provisioningData['CoPersonRole'][$role_idx]['id'];
    } elseif(!empty($cou_paths)) {
      $personrole_expand = Hash::expand($cou_paths);
      $role_idx = key($personrole_expand['CoPersonRole']);
      return $provisioningData['CoPersonRole'][$role_idx]['id'];
    } elseif(is_array($_REQUEST)) {                           // Delete, Put Actions
      $request = array_keys($_REQUEST);
      $req_path = explode('/', $request[0]);
      $req_path = array_filter($req_path); // removing blank, null, false, 0 (zero) values
      // XXX We only want to move forward if this refers to CoPersonRole or CoPerson(?)
      if(!in_array('co_person_roles', $req_path)) {
        return null;
      }
      return (int)end($req_path);
    }
    return null;
  }
}
