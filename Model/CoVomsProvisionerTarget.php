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
    $this->log(__METHOD__ . "::target data => ".print_r($coProvisioningTargetData,true),LOG_DEBUG);
    $this->log(__METHOD__ . "::provision data => ".print_r($provisioningData,true),LOG_DEBUG);
    switch ($op) {
      case ProvisioningActionEnum::CoPersonUpdated:
        $this->log(__METHOD__ . "::Person Updated", LOG_DEBUG);
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        // When deleted remove all the entries in the file by epuid
        $this->log(__METHOD__ . "::Person deleted", LOG_DEBUG);
        break;
      default:
        // Log noop and fall through.
        $this->log(__METHOD__ . "::Provisioning action $op not allowed/implemented", LOG_DEBUG);
    }

    return true;
  }
}
