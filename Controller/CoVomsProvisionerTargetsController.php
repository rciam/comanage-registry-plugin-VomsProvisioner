<?php
/**
 * COmanage Registry CO VOMs Provisioner Targets Controller
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
 * @package       registry
 * @since         COmanage Registry v3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses("SPTController", "Controller");

class CoVomsProvisionerTargetsController extends SPTController {
  // Class name, used by Cake
  public $name = "CoVomsProvisionerTargets";

  public $uses = array(
    'VomsProvisioner.CoVomsProvisionerTarget',
  );


  // Create notification pop up after saving
  public function checkWriteFollowups($reqdata, $curdata = null, $origdata = null) {
    $this->Flash->set(_txt('rs.updated-a3', array(_txt('ct.co_voms_provisioner_targets.1'))), array('key' => 'success'));
    return true;
  }

  /**
   * @param null $data
   * @return int|mixed|null
   */
  public function parseCOID($data = null) {
    if (!$this->requires_co) {
      // Controllers that don't require a CO generally can't imply one.
      return null;
    }
    $coid = null;
    if(!empty($this->request->params["named"]["co"])) {
      $coid = $this->request->params["named"]["co"];
    }
    return $coid;
  }

  /**
   * @return bool
   */
  public function verifyRequestedId() {
    if(!empty($this->cur_co)
       && !empty($this->request->params["pass"][0])) {
      $args = array();
      $args['conditions']['CoVomsProvisionerTarget.id'] = $this->request->params["pass"][0];
      $args['contain'] = false;
      $voms_provisioner_target = $this->CoVomsProvisionerTarget->find('first', $args);
      if(!empty($voms_provisioner_target["CoVomsProvisionerTarget"])) {
        return true;
      }
    }
    return false;
  }

  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v3.1.x
   * @return Array Permissions
   */

  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform

    // Delete an existing CO Provisioning Target?
    $p['delete'] = ($roles['cmadmin'] || $roles['coadmin']);

    // Edit an existing CO Provisioning Target?
    $p['edit'] = ($roles['cmadmin'] || $roles['coadmin']);

    // View all existing CO Provisioning Targets?
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin']);

    // View an existing CO Provisioning Target?
    $p['view'] = ($roles['cmadmin'] || $roles['coadmin']);

    $this->set('permissions', $p);
    return($p[$this->action]);
  }
}
