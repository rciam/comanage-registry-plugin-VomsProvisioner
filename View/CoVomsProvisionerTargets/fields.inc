<!--
/**
 * COmanage Registry CO VOMs Provisioning Target Fields
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
 * @since         COmanage Registry 3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
-->
<?php
// Determine if fields are editable
$e = false;

if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
  $e = true;

// We shouldn't get here if we don't have at least read permission, but check just in case

if(!$e && !$permissions['view'])
  return false;

print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
print $this->Form->hidden('co_provisioning_target_id', array('default' => $vv_ptid)) . "\n";
?>

<script src="<?= $this->request->webroot . 'voms_provisioner' ?>/jquery/jquery.base64.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('[type=file]').change(function() {
            // Get the id of the input and retrieve the corresponding input textbox
            id = $(this).attr('id').toString().toLowerCase();
            let textbox = id.indexOf("key")>=0 ? $('CoVomsProvisionerTargetRobotKey') : $('CoVomsProvisionerTargetRobotCert');
            //console.log(textbox);

            if (!("files" in this)) {
                alert("File reading not supported in this browser");
            }
            let file = this.files && this.files[0];
            if (!file) {
                return;
            }
            let fileReader = new FileReader();
            fileReader.onload = function(e) {
                let text = e.target.result;
                // console.log(id.indexOf("private"));
                // console.log(text.indexOf("BEGIN RSA PRIVATE KEY"));
                // console.log(text.indexOf("ssh-rsa"));
                if(text.indexOf("BEGIN PRIVATE KEY") >= 0 && id.indexOf("key") >= 0) {
                    let li = textbox.closest('li');
                    //console.log(li);
                    let description = li.find('.field-desc');
                    //console.log(description);
                    let description_text = description.html().split('-')[0].trim();
                    description.html(description_text);
                }else{
                    //console.log('Wrong type of file');
                    let li = textbox.closest('li');
                    //console.log(li);
                    let description = li.find('.field-desc');
                    //console.log(description);
                    let description_text = description.html().split('-')[0].trim();
                    description_text = description_text + "<span style='color:red'>  - Wrong File Input</span>";
                    description.html(description_text);
                    return;
                }
                //Encode to base64
                //console.log(text);
                let result = $.base64.encode(text);
                //console.log(result);
                // Add base64 encoded value in the textbox
                textbox.val(result);
            };
            fileReader.readAsText(this.files[0]);
        });

    });
</script>

<div class="co-info-topbox">
  <i class="material-icons">info</i>
  <?php print _txt('pl.voms_provisioner.info'); ?>
</div>
<ul id="<?php print $this->action; ?>_co_voms_provisioner_target" class="fields form-list form-list-admin">
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.voms_provisioner.host'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.host.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('host', array('size' => 50, 'maxlength'=>'256')) : filter_var($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['host'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.voms_provisioner.port'); ?>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.port.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('port', array('type' => 'number')) : filter_var($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['port'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.voms_provisioner.vo'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.vo.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('vo', array('size' => 50, 'maxlength'=>'96')) : filter_var($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['vo'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.voms_provisioner.robot_cert'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.robot_cert.desc'); ?></div>
    </div>
    <div class="field-info">
      <label for="robot_cert" class="loadbutton ui-button ui-corner-all ui-widget">
        <span class="ui-button-icon ui-icon ui-icon-transferthick-e-w"></span>
        <input type="file" id="robot_cert" style="display: none"/>
        Load
      </label>
      <?php print $this->Form->hidden('robot_cert', array('default' => $co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_cert'])) . "\n";?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.voms_provisioner.robot_key'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.robot_key.desc'); ?></div>
    </div>
    <div class="field-info">
      <label for="robot_key"  class="loadbutton ui-button ui-corner-all ui-widget">
        <span class="ui-button-icon ui-icon ui-icon-transferthick-e-w"></span>
        <input type="file" id="robot_key" style="display: none"/>
        Load
      </label>
      <?php print $this->Form->hidden('robot_key', array('default' => $co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_key'])) . "\n";?>
    </div>
  </li>
  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
    </li>
  <?php endif; ?>
</ul>