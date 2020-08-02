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
// Load my css
$this->Html->css('VomsProvisioner.bootstrap.min', array('inline' => false));
$this->Html->css('VomsProvisioner.voms-provisioner',  array('inline' => false));
// Load my Javascript
$this->Html->script('VomsProvisioner.bootstrap.min',  array('inline' => false));
$this->Html->script('VomsProvisioner.jquery/jquery.base64',  array('inline' => false));
$this->Html->script('VomsProvisioner.voms_provisioner_target',  array('inline' => false));
?>

<script type="text/javascript">
    // Global javascript variables
    var vo_name = '<?php print trim(filter_var($co_voms_provisioner_targets[0]["CoVomsProvisionerTarget"]["vo"]));?>';

    function cert_preview(){
      // Load the Modal
      if($('#certPreviewModal').length < 1) {
        $("body").append(`<?php print $this->element('certPreview')?>`);
      }
      $('#certPreviewModal').modal();
    }
    // Tongle between single and bulk load
    function request_mode() {
        if ($('.bulk-mode').is(':visible')
            && $('.single-mode').is(':hidden')) {
            bulkVomsGet($('#bulkURL').val().trim());
        } else {
            let port = $('.single-mode').find('#port').val().trim();
            if(port === '' || port == null){
                generateLinkFlash('<?php print _txt("op.voms_provisioner.noport")?>', "error", 5000);
                $('#vomsModal').modal('hide');
            }
            let host = $('.single-mode').find('#host').val().trim();
            if(host === '' || host == null){
                generateLinkFlash('<?php print _txt("op.voms_provisioner.nohost")?>', "error", 5000);
                $('#vomsModal').modal('hide');
            }
            let dn = $('.single-mode').find('#dn').val().trim();
            let protocol = $('.single-mode').find('#protocol-toggler option:selected').val().trim();
            // Get the id of the provisioner target
            action_tbl = $('#CoVomsProvisionerTargetEditForm').attr('action').split('edit');
            params = action_tbl[action_tbl.length-1].split('/');
            let cvpt_id = params[1];
            // Get the index
            add_single_voms(cvpt_id, protocol, host, port, dn);
            $('#vomsModal').modal('hide');
        }
    }

    // Add a Single entry via the Modal
    function add_single_voms(cvpt_id, protocol, host, port, dn) {
        var base_uri = protocol + '://' + host + ':' + port + '/' + vo_name;
        let base_uri_elem = '<span class="voms-server-uri">' + base_uri + '</span>';
        if($('.voms-server-list').length < 1) {
            const actions = `<?php print $this->element('servrow', array('single_srv_add' => true)); ?>`;
            $('#co_voms_provisioner_servers_list').prepend('<li class="voms-server-list">' + actions + base_uri_elem + '</li>');
            var li = $('.voms-server-list');
        } else {
            const actions = `<?php print $this->element('servrow', array('single_srv_add' => true)); ?>`;
            var li = $('<li class="voms-server-list">' + actions + base_uri_elem + '</li>');
            li.insertAfter('.voms-server-list:last');
        }

        let index = $('.voms-server-list').length -1;
        if(cvpt_id !== '') {
            li.append('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][co_voms_provisioner_target_id]" value=' + cvpt_id + ' id="CoVomsProvisionerServer' + index + 'CoVomsProvisionerTargetId">');
        }
        li.append('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][protocol]" value="' + protocol + '" id="CoVomsProvisionerServer' + index + 'Protocol">');
        li.append('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][host]" value="' + host + '" id="CoVomsProvisionerServer' + index + 'Host">');
        li.append('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][port]" value=' + port + ' id="CoVomsProvisionerServer' + index + 'Port">');
        li.append('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][dn]" value="' + dn + '" id="CoVomsProvisionerServer' + index + 'Dn">');
    }

    // Remove a single entry
    function rmv_voms_entry(element) {
        if($(element).data("db")===false) {
          $(element).closest('li').remove();
          return;
        }
        const voms_row_id = $(element).closest('li').data('server_id');
        let voms_data = {
            _Token: {}
        };
        voms_data.id = voms_row_id;
        voms_data._Token.key = '<?php echo $token_key;?>';
        const url_str = '<?php echo $this->Html->url(array(
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
                $(element).closest('li').remove();
                // Hide clear if the Server list is empty
                if($('.voms-server-list').length < 1) {
                    $('#voms-server-clr-btn').hide();
                }
                generateLinkFlash("<?php print _txt('rs.voms_provisioner.cleared.1') ?>","success", 2000);
            },
            error: function(response) {
                generateLinkFlash("Delete Failed","error", 2000);
                console.log(response.responseText);
            }
        });
    }

    // Edit/Load a single VOMS entry UI side
    function edit_voms_field(element) {
        const $closest_li = $(element).closest('li');
        const voms_row_id = $closest_li.attr('id');
        // Load the Modal
        if($('#vomsModal').length < 1) {
            $("body").append(`<?php print $this->element('vomsImport')?>`);
        }
        $('#vomsModal').modal('show');
        // Handle the Modal
        $('#vomsModal h4').hide();
        $('.mode-toggler-div').hide();
        $('#voms-server-modal-btn-add').hide();
        $('.modal-title-edit').show();
        $('#voms-server-modal-btn-edit').show();
        // Get the values from the hidden fields
        $closest_li.find('input[type="hidden"]').each((item, value) => {
            let id = $(value).attr('id');
            if(id.includes('CoVomsProvisionerServer')) {
                if(id.includes('Protocol')) {
                    let stored_value = $(value).val();
                    $("#protocol-toggler option[value='" + stored_value + "']").attr("selected", "selected");
                }
                if(id.includes('Port')) {
                    $('#port').val($(value).val());
                }
                if(id.includes('Host')) {
                    $('#host').val($(value).val());
                }
                if(id.includes('Dn')) {
                    $('#dn').val($(value).val());
                }
            }
        });
        // Keep track of the entry i want to update
        $('#voms-server-modal-btn-edit').attr('data-voms_id', voms_row_id);
    }

    // Update the UI side of a single VOMS field
    function update_voms_field() {
        host = $('#host').val().trim();
        port = $('#port').val().trim();
        dn = $('#dn').val().trim();
        protocol = $('#protocol-toggler option:selected').val().trim();
        server_id = $('#voms-server-modal-btn-edit').data('voms_id');
        // Find and update the base uri
        base_uri = protocol + '://' + host + ':' + port + '/' + vo_name;
        $('#' + server_id).find('span').text(base_uri);
        // Update the hidden fields
        $('#' + server_id).closest('li').find('input[type="hidden"]').each((item, value) => {
            let id = $(value).attr('id');
            if(id.includes('CoVomsProvisionerServer')) {
                if(id.includes('Protocol')) {
                    $(value).val(protocol);
                }
                if(id.includes('Port')) {
                    $(value).val(port);
                }
                if(id.includes('Host')) {
                    $(value).val(host);
                }
                if(id.includes('Dn')) {
                    $(value).val(dn);
                }
            }
        });
        $('#vomsModal').modal('hide');
    }

    // Remove all VOMS entries from the database
    function clear_voms_entries() {
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
                // Remove all the li elements
                $('.voms-server-list').remove();
                // Remove all the VomsServer input elements
                $('input[type="hidden"][id^="CoVomsProvisionerServer"]').remove();
                // Disable clear button
                $('#voms-server-clr-btn').hide();
                generateLinkFlash("<?php print _txt('rs.voms_provisioner.cleared') ?>","success", 5000);
            },
            error: function(response) {
                generateLinkFlash("<?php print _txt('rs.voms_provisioner.cleared.fail') ?>","error", 5000);
                console.log(response.responseText);
            }
        });
    }

    $(function() {
        if(vo_name == null
            || vo_name === '') {
            vo_name = $('#edit_co_voms_provisioner_target').find('.field-info').first().text().trim();
        }

        // Hide clear if the Server list is empty
        if($('.voms-server-list').length > 0) {
            $('#voms-server-clr-btn').show();
        }

        // Clear/Delete All Dialog Configuration
        $("#server-list-clear-dialog").dialog({
            dialogClass: "no-close",
            autoOpen: false,
            buttons: {
                "<?php print _txt('fd.voms_provisioner.cancel'); ?>": function() {
                    $(this).dialog("close");
                },
                "<?php print _txt('fd.voms_provisioner.delall'); ?>": function() {
                    $(this).dialog("close");
                    clear_voms_entries();
                }
            },
            modal: true,
            show: {
                effect: "fade"
            },
            hide: {
                effect: "fade"
            }
        });

        // Read the Robot Cert
        $('#robot_cert[type=file]').change((event) => {
            $('#cert-done').hide();
            $(this).removeClass('success failed');
            read_file(event, 'BEGIN CERTIFICATE', $('#CoVomsProvisionerTargetRobotCert'), 'cert');
        });

        // Read the Robot Key
        $('#robot_key[type=file]').change((event) => {
            $('#key-done').hide();
            $(this).removeClass('success failed');
            read_file(event, 'BEGIN PRIVATE KEY', $('#CoVomsProvisionerTargetRobotKey'), 'key');
        });

        // Open the Modal and add one or multiple VOMS
        $('#voms-server-add-btn').on('click', () => {
            // XXX Use ES6 template literal(Template Strings) to load the template
            // Load the Modal
            if($('#vomsModal').length < 1) {
                $("body").append(`<?php print $this->element('vomsImport')?>`);
            }
            $('#vomsModal').modal('show');
            // Handle the Modal
            $('#vomsModal h4').hide();
            $('#voms-server-modal-btn-edit').hide();
            $('.mode-toggler-div').show();
            $('#voms-server-modal-btn-add').show();
            $('.modal-title-add').show();
            // Reset fields
            $('#port').val('');
            $('#host').val('');
            $('#dn').val('');
        });

        // Clear the whole VOMS list
        $('#voms-server-clr-btn').on('click', () => {
            // Open the dialog to confirm VOMS clear
            $("#server-list-clear-dialog").dialog("open");
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
    <div class="field-info" style="width: auto !important;">
      <ul id="co_voms_provisioner_servers_list" style="padding-inline-start:unset;">
        <?php
            print $this->element('servrow', array(
              'co_voms_provisioner_targets' => $co_voms_provisioner_targets,
              'single_srv_add' => false,
            ));
        ?>
        <li class="voms-server-list-btn">
          <a href="#"
             id='voms-server-add-btn'
             class='ui-button ui-corner-all ui-widget'>
            <span class="ui-button-icon ui-icon ui-icon-circle-plus"></span><span class="ui-button-icon-space"> </span>
            <?php print _txt('fd.voms_provisioner.add'); ?>
          </a>
          <a href="#"
             style="display:none;"
             id='voms-server-clr-btn'
             class='ui-button ui-corner-all ui-widget'>
            <span class="ui-button-icon ui-icon ui-icon-circle-close"></span><span class="ui-button-icon-space"> </span>
            <?php print _txt('fd.voms_provisioner.clear'); ?>
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
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.robot_cert.desc'); ?>
        <?php if(!empty($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_cert'])): ?>
          <span class="info-box neutral">
            <a href="#" onclick="cert_preview();return false;" style="color:#fff"><i class="material-icons">info</i></a>
            <?php print _txt('fd.voms_provisioner.floaded'); ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
    <div class="field-info">
      <label id="cert-load-btn" for="robot_cert" class="loadbutton ui-button ui-corner-all ui-widget"">
        <span class="ui-button-icon ui-icon ui-icon-transferthick-e-w"></span><span class="ui-button-icon-space"> </span>
        <input type="file" id="robot_cert" accept="application/x-x509-ca-cert, .pem" style="display: none"/>
        <?php
          print (empty($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_cert'])) ? _txt('fd.voms_provisioner.load')
                                                                                                  : _txt('fd.voms_provisioner.update');
        ?>
      </label>
      <div id="cert-progress" class="progress volume-slider">
        <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
      <?php
      $options = array(
        'default' => $co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_cert'],
        'style'   => 'display:none',
        'type'    => 'hidden',
      );
      print $this->Form->input('robot_cert', $options) . "\n";
      ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.voms_provisioner.robot_key'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.voms_provisioner.robot_key.desc'); ?>
        <?php if(!empty($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_key'])): ?>
          <span class="info-box neutral">
            <?php print _txt('fd.voms_provisioner.floaded'); ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
    <div class="field-info">
      <label id="key-load-btn" for="robot_key"  class="loadbutton ui-button ui-corner-all ui-widget">
        <span class="ui-button-icon ui-icon ui-icon-transferthick-e-w"></span><span class="ui-button-icon-space"> </span>
        <input type="file" id="robot_key" accept="application/x-x509-ca-cert, .pem" style="display: none"/>
        <?php
          print (empty($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_key'])) ? _txt('fd.voms_provisioner.load')
                                                                                                 : _txt('fd.voms_provisioner.update');
        ?>
      </label>
      <div id="key-progress" class="progress volume-slider">
        <div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
      <?php
        $options = array(
          'default' => $co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_key'],
          'style'   => 'display:none',
          'type'    => 'hidden',
        );
        print $this->Form->input('robot_key', $options) . "\n";
      ?>
    </div>
  </li>
  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit(_txt('fd.voms_provisioner.save')); ?>
      </div>
    </li>
  <?php endif; ?>
</ul>

<?php
  print $this->element('dialog');
?>

