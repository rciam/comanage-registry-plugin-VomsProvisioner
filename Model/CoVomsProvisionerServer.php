<?php
/**
 * COmanage Registry CO VOMS Provisioner  Model Server
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
 * @since         COmanage Registry v3.1.1
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

class CoVomsProvisionerServer extends AppModel {
  // Define class name for cake
  public $name = "CoVomsProvisionerServer";

  // Add behaviors
  public $actsAs = array('Containable');

  // Association rules from this model to other models
  public $belongsTo = array(
    "VomsProvisioner.CoVomsProvisionerTarget",
  );

  // Default display field for cake generated views
  public $displayField = "host";

  public $validate = array(
    'co_voms_provisioning_target_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO VOMS PROVISIONING TARGET ID must be provided'
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
    'dn' => array(
      'rule' => 'notBlank'
    )
  );
}