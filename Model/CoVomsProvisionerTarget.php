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
App::uses('HttpSocket', 'Network/Http');

require_once(LOCAL . DS . 'Plugin' . DS . 'VomsProvisioner' . DS . 'Lib' . DS . 'VomsClient.php');

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

  // Validation rules for table elements
  public $validate = array(
    'co_provisioning_target_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO PROVISIONING TARGET ID must be provided'
    ),
    'host' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    ),
    'port' => array(
      'rule' => array('range', 1, 65535),
      'message' => 'Please enter value from 1-65535',
      'required' => false,
      'allowEmpty' => true
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
   * @since  COmanage Registry v0.8
   */

  public function provision($coProvisioningTargetData, $op, $provisioningData)
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $this->log(__METHOD__ . "::action => ".$op, LOG_DEBUG);

    $robot_cert = $this->getRobotCert($coProvisioningTargetData);
    $robot_key = $this->getRobotKey($coProvisioningTargetData);
    $user_cou_related_profile = $this->retrieveUserVoStatus($provisioningData, $coProvisioningTargetData);
    switch ($op) {
      case ProvisioningActionEnum::CoPersonUpdated:
        $this->log(__METHOD__ . "::Person Updated", LOG_DEBUG);
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        // When deleted remove all the entries in the file by epuid
        $this->log(__METHOD__ . "::Person deleted", LOG_DEBUG);
        break;
      case ProvisioningActionEnum::CoPersonPetitionProvisioned:
        break;
      default:
        // Log noop and fall through.
        $this->log(__METHOD__ . "::Provisioning action $op not allowed/implemented", LOG_DEBUG);
    }

    return true;
  }


  /**
   * @param $provisioningData
   * @param $coProvisioningTargetData
   * @throws InvalidArgumentException
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
    $user_memberships_profile = Hash::flatten($provisioningData['CoGroupMember']);

    $in_group = array_search($co_group_id, $user_memberships_profile);
    if(!empty($in_group)){
      $index = explode('.', $in_group, 2)[0];
      $user_membership_status = $provisioningData['CoGroupMember'][$index];
      $cou_id = $user_membership_status["CoGroup"]["cou_id"];
    }

    // Create the profile of the user according to the group_id and cou_id of the provisioned
    // resources that we configured
    $args = array();
    $args['conditions']['CoPerson.id'] = $provisioningData["CoPerson"]["id"];
    $args['contain']['CoPersonRole'] = array(
      'conditions' => ['CoPersonRole.cou_id' => $cou_id],
    );
    $args['contain']['CoGroupMember']= array(
      'conditions' => ['CoGroupMember.co_group_id' => $co_group_id],
    );
    $args['contain']['CoGroupMember']['CoGroup'] = array(
      'conditions' => ['CoGroup.id' => $co_group_id],
    );
    // todo: Test if it fetches the org identities Certs
    $args['contain']['CoOrgIdentityLink']['OrgIdentity']['Cert'] = array(
      'conditions' => ['Cert.issuer is not null'],
    );

    // XXX Filter with this $user_profile["CoOrgIdentityLink"][2]["OrgIdentity"]["Cert"]
    // We can not perform any action with VOMS without a Certificate having both a subjectDN and an Issuer
    // Keep in depth level 1 only the non empty Certificates
    $user_profile = $this->CoProvisioningTarget->Co->CoPerson->find('first', $args);
    foreach($user_profile["CoOrgIdentityLink"] as $link) {
      if(!empty($link["OrgIdentity"]["Cert"])) {
        foreach ($link["OrgIdentity"]["Cert"] as $cert) {
          $user_profile['Cert'][] = $cert;
        }
      }
    }


    // XXX In $provisioningData
    // XXX The user is a member even if suspended.
    // XXX The user's role is not fetched if SUSPENDED
    $this->log(__METHOD__ . "::user membership status". print_r($user_membership_status),LOG_DEBUG);
    $this->log(__METHOD__ . "::user roles status". print_r($user_profile),LOG_DEBUG);
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
}
