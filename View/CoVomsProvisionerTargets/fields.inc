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

// Add breadcrumbs
print $this->element("coCrumb");
// Provisioning Targes Crumb
$args = array();
$args['plugin'] = null;
$args['controller'] = 'co_provisioning_targets';
$args['action'] = 'index';
$args['co'] = $cur_co['Co']['id'];
$this->Html->addCrumb(_txt('ct.co_provisioning_targets.pl'), $args);

// Current Crumb
$crumbTxt = _txt('op.' . $this->action . '-a', array(_txt('ct.co_voms_provisioner_targets.1')));
$this->Html->addCrumb($crumbTxt);

print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
print $this->Form->hidden('co_provisioning_target_id', array('default' => $vv_ptid)) . "\n";

// Variables i want accross the view
$token_key = $this->request->params['_Token']['key'];

// https://dirac.egi.eu/files/diracVOs.json
// todo: Perhaps i could use this to configure the provisioner automagically

?>

<script type="text/javascript">
    // Global javascript variables
    var vo_name = '<?php print trim(filter_var($co_voms_provisioner_targets[0]["CoVomsProvisionerTarget"]["vo"]));?>';

    // todo: Investigate more on filereader. It would be better if we made this a promise
    // todo: move on separate file
    function read_file(event, validation_string, input_textbox, file_type) {
        const fileList = event.target.files;
        if (!fileList || fileList.length < 1) {
            alert("File reading not supported in this browser");
            return;
        }
        const file = fileList[0];

        let fileReader = new FileReader();
        fileReader.onload = (event) => {
            let payload = event.target.result;
            if(payload.includes(validation_string)) {
                let li = input_textbox.closest('li');
                let description = li.find('.field-desc');
                let description_text = description.html().split('-')[0].trim();
                description.html(description_text);
            }else{
                let li = input_textbox.closest('li');
                let description = li.find('.field-desc');
                let description_text = description.html().split('-')[0].trim();
                description_text = description_text + "<span style='color:red'>  - Wrong File Input</span>";
                description.html(description_text);
                return;
            }
            //Encode to base64
            let result = $.base64.encode(payload);
            // Add base64 encoded value in the textbox
            input_textbox.val(result);
            $('#' + file_type + '-done').show();
        }
        fileReader.onprogress = (event) => {
            progress_element = $('#'+file_type + '-progress');
            let progress_bar = progress_element.find('.progress-bar');
            progress_element.show();
            if (event.loaded && event.total) {
                // Add a progressbar
                const percent = Math.round((event.loaded / event.total) * 100);
                progress_bar.width(percent+'%');
                console.log('Progress: ' + percent);
                if( percent === 100) {
                    progress_element.hide();
                }
            }
        }
        fileReader.onerror = (event) => {
            let li = input_textbox.closest('li');
            let description = li.find('.field-desc');
            let description_text = description.html().split('-')[0].trim();
            description_text = description_text + "<span style='color:red'>  - Load File failed</span>";
            description.html(description_text);
        }
        fileReader.readAsText(file);
    }

    // Generate flash notifications for messages
    function generateLinkFlash(text, type, timeout) {
        var n = noty({
            text: text,
            type: type,
            dismissQueue: true,
            layout: 'topCenter',
            theme: 'comanage',
            timeout: timeout
        });
    }

    function requestMode() {
        if ($('.bulk-mode').is(':visible')
            && $('.single-mode').is(':hidden')) {
            bulkVomsGet($('#bulkURL').val().trim());
        } else {
            let port = $('.single-mode').find('#port').val().trim();
            if(port === '' || port === null){
                generateLinkFlash('<?php print _txt("op.voms_provisioner.noport")?>', "error", 5000);
                $('#vomsAddModal').modal('hide');
            }
            let host = $('.single-mode').find('#host').val().trim();
            if(host === '' || host === null){
                generateLinkFlash('<?php print _txt("op.voms_provisioner.nohost")?>', "error", 5000);
                $('#vomsAddModal').modal('hide');
            }
            let dn = $('.single-mode').find('#dn').val().trim();
            // Get the id of the provisioner target
            action_tbl = $('#CoVomsProvisionerTargetEditForm').attr('action').split('edit');
            params = action_tbl[action_tbl.length-1].split('/');
            let cvpt_id = params[1];
            // Get the index
            addSingle(cvpt_id, host, port, dn);
            $('#vomsAddModal').modal('hide');
        }
    }

    function addSingle(cvpt_id, host, port, dn) {
        var base_uri = 'https://' + host + ':' + port + '/' + vo_name;
        if($('.voms-server-list').length < 1) {
            actions = `<?php print $this->element("servrow")?>`;
            $('#co_voms_provisioner_servers_list').prepend('<li class="voms-server-list">' + actions + base_uri + '</li>');
        } else {
            actions = `<?php print $this->element("servrow")?>`;
            $('<li class="voms-server-list">' + actions + base_uri + '</li>').insertAfter('.voms-server-list:last');
        }

        let index = $('.voms-server-list').length -1;
        let last_element = $('.voms-server-list').last();
        if(cvpt_id !== '') {
            $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][co_voms_provisioner_target_id]" value=' + cvpt_id + ' id="CoVomsProvisionerServer' + index + 'CoVomsProvisionerTargetId">').insertBefore(last_element);
        }
        $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][host]" value=' + host + ' id="CoVomsProvisionerServer' + index + 'Host">').insertBefore(last_element);
        $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][port]" value=' + port + ' id="CoVomsProvisionerServer' + index + 'Port">').insertBefore(last_element);
        $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][dn]" value=' + dn + ' id="CoVomsProvisionerServer' + index + 'Dn">').insertBefore(last_element);
    }

    function removeSrv(element) {
        let voms_row_id = $(element).closest('li').prevUntil('li').last().val();
        let voms_data = {
            _Token: {}
        };
        voms_data.id = voms_row_id;
        voms_data._Token.key = '<?php echo $token_key;?>';
        var url_str = '<?php echo $this->Html->url(array(
          'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
          'controller' => 'co_voms_provisioner_servers',
          'action' => 'delete',
          'co'  => $cur_co['Co']['id'])); ?>' + '/' + voms_row_id;
        $.ajax({
            type: "DELETE",
            url: url_str,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-CSRF-Token', '<?php echo $token_key;?>');
            },
            cache:false,
            data: voms_data,
            success: function(response) {
                // Remove the row and the hidden inputs
                debugger;
                let voms_row_html = $(element).closest('li');
                let voms_row_hidden = $(element).closest('li').prevUntil('li');
                $(voms_row_html).remove();
                $.each($(voms_row_hidden), (input) => {
                    $(input).remove();
                });
                generateLinkFlash("<?php print _txt('rs.voms_provisioner.cleared.1') ?>","success", 2000);
            },
            error: function(response) {
                generateLinkFlash("Delete Failed","error", 2000);
                console.log(response.responseText);
            }
        });
    }

    $(function() {
        // Hide clear if the Server list is empty
        if($('.voms-server-list').length > 0) {
            $('#voms-server-clr-btn').show();
        }

        // Read the Robot Cert
        $('#robot_cert[type=file]').change((event) => {
            $('#cert-done').hide();
            read_file(event, 'BEGIN CERTIFICATE', $('#CoVomsProvisionerTargetRobotCert'), 'cert');
        });

        // Read the Robot Key
        $('#robot_key[type=file]').change((event) => {
            $('#key-done').hide();
            read_file(event, 'BEGIN PRIVATE KEY', $('#CoVomsProvisionerTargetRobotKey'), 'key');
        });

        $('#voms-server-add-btn').on('click', () => {
            // XXX Use ES6 template literal(Template Strings) to load the template
            $("body").append(`<?php print $this->element('modal')?>`);
            $('#vomsAddModal').modal('show');
             // $('#myInput').focus();
        });

        $('#voms-server-clr-btn').on('click', () => {
            var $srv_data = {
                _Token: {}
            };
            $srv_data._Token.key = '<?php echo $token_key;?>';
            var url_str = '<?php echo $this->Html->url(array(
              'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
              'controller' => 'co_voms_provisioner_targets',
              'action' => 'clearsrv',
              'co'  => $cur_co['Co']['id'])); ?>';
            $.ajax({
                type: "POST",
                url: url_str,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.setRequestHeader('X-CSRF-Token', '<?php echo $token_key;?>');
                },
                cache:false,
                data: $srv_data,
                success: function(response) {
                    // Remove from document
                    $('.voms-server-list').remove();
                    // Disable clear button
                    $('#voms-server-clr-btn').hide();
                    generateLinkFlash("<?php print _txt('rs.voms_provisioner.cleared') ?>","success", 2000);
                },
                error: function(response) {
                    generateLinkFlash("<?php print _txt('rs.voms_provisioner.cleared.fail') ?>","error", 2000);
                    console.log(response.responseText);
                }
            });
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
      <div class="field-title">
        <?php print _txt('pl.voms_provisioner.vo'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.vo.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php
        if(!empty($vv_cou_name)) {
          print $vv_cou_name;
          print $this->Form->hidden('vo', array('default' => trim($vv_cou_name )));
      } elseif(!empty(filter_var($co_voms_provisioner_targets[0]["CoVomsProvisionerTarget"]["vo"]))) {
          print filter_var($co_voms_provisioner_targets[0]["CoVomsProvisionerTarget"]["vo"]);
          print $this->Form->hidden('vo', array('default' => filter_var($co_voms_provisioner_targets[0]["CoVomsProvisionerTarget"]["vo"])));
        } else {
          $options = array(
            'size'      => 50,
            'maxlength' => 96,
            'type'      => 'text',
          );
          print $this->Form->input('vo', $options);
        }
      ?>
    </div>
  </li>
  <li style="display: flex;">
    <div class="field-name field-name-flex">
      <div class="field-title">
        <?php print _txt('pl.voms_provisioner.serveruri'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.serveruri.desc'); ?></div>
    </div>
    <div class="field-info">
      <ul id="co_voms_provisioner_servers_list" style="padding-inline-start:unset;">
        <?php
          foreach($co_voms_provisioner_targets[0]["CoVomsProvisionerServer"] as $sindex => $server) {
            $sprefix = 'CoVomsProvisionerServer.' . $sindex;
            $base_uri = 'https://' . filter_var($server['host']) . ':'
              . filter_var($server['port'], FILTER_SANITIZE_SPECIAL_CHARS)
              . '/' . filter_var($co_voms_provisioner_targets[0]["CoVomsProvisionerTarget"]["vo"], FILTER_SANITIZE_SPECIAL_CHARS);
            // XXX Do not change the sequence
            // fixme: Get the id more dynamically
            print $this->Form->hidden($sprefix . '.id', array('default' => filter_var($server['id']))) . "\n";
            print $this->Form->hidden($sprefix . '.co_voms_provisioner_target_id', array('default' => filter_var($server['co_voms_provisioner_target_id']))) . "\n";
            print $this->Form->hidden($sprefix . '.host', array('default' => filter_var($server['host']))) . "\n";
            print $this->Form->hidden($sprefix . '.port', array('default' => filter_var($server['port']))) . "\n";
            print $this->Form->hidden($sprefix . '.dn', array('default' => filter_var($server['dn']))) . "\n";
            print $this->element('servrow', array('base_uri' => $base_uri));
          }
        ?>
        <li class="voms-server-list-btn">
          <a href="#"
             id='voms-server-add-btn'
             class='ui-button ui-corner-all ui-widget'>
            <span class="ui-button-icon ui-icon ui-icon-circle-plus"></span><span class="ui-button-icon-space"> </span>
            <?php print _txt('fd.voms_provisioner.srv'); ?>
          </a>
          <a href="#"
             style="display:none;"
             id='voms-server-clr-btn'
             class='ui-button ui-corner-all ui-widget'>
            <span class="ui-button-icon ui-icon ui-icon-circle-close"></span><span class="ui-button-icon-space"> </span>
            <?php print _txt('fd.voms_provisioner.clr'); ?>
          </a>
        </li>
      </ul>
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
      <label for="robot_cert" class="loadbutton ui-button ui-corner-all ui-widget"">
        <span class="ui-button-icon ui-icon ui-icon-transferthick-e-w"></span><span class="ui-button-icon-space"> </span>
        <input type="file" id="robot_cert" accept="application/x-x509-ca-cert, .pem" style="display: none"/>
        Load
      </label>
      <div id="cert-progress" class="progress volume-slider">
        <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
      <?php print $this->Form->hidden('robot_cert', array('default' => $co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_cert'])) . "\n";?>
      <em id="cert-done" class="material-icons upload-done">done</em>
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
      <label for="robot_key"  class="loadbutton ui-button ui-corner-all ui-widget"">
        <span class="ui-button-icon ui-icon ui-icon-transferthick-e-w"></span><span class="ui-button-icon-space"> </span>
        <input type="file" id="robot_key" accept="application/x-x509-ca-cert, .pem" style="display: none"/>
        Load
      </label>
      <div id="key-progress" class="progress volume-slider">
        <div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
      <?php print $this->Form->hidden('robot_key', array('default' => $co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_key'])) . "\n";?>
      <em id="key-done" class="material-icons upload-done">done</em>
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