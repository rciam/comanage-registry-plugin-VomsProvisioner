<?php if(!empty($co_voms_provisioner_targets) && !$single_srv_add): ?>
<?php foreach($co_voms_provisioner_targets[0]["CoVomsProvisionerServer"] as $sindex => $server): ?>
<li id="voms-server-entry<?php print $server['id']; ?>" data-server_id="<?php print $server['id']; ?>" class="voms-server-list">
  <a href="#"
     id='voms-server-list-edit<?php print $server['id']; ?>'
     onclick="edit_voms_field(this)"
     class='ui-button ui-corner-all ui-widget voms-server-list-edit'>
    <span class="ui-button-icon ui-icon ui-icon-pencil"></span>
  </a>
  <a href="#"
     data-db="true"
     id='voms-server-list-delete<?php print $server['id']; ?>'
     onclick="rmv_voms_entry(this);return false;""
     class='ui-button ui-corner-all ui-widget voms-server-list-delete'>
    <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
  </a>
  <?php
  $sprefix = 'CoVomsProvisionerServer.' . $sindex;
  $base_uri =
    filter_var($server['protocol'], FILTER_SANITIZE_SPECIAL_CHARS) . '://'
    . filter_var($server['host'], FILTER_SANITIZE_SPECIAL_CHARS) . ':'
    . filter_var($server['port'], FILTER_SANITIZE_SPECIAL_CHARS) . '/'
    . 'voms' . '/'
    . filter_var($co_voms_provisioner_targets[0]["CoVomsProvisionerTarget"]["vo"], FILTER_SANITIZE_SPECIAL_CHARS);


  print $this->Form->hidden($sprefix . '.id', array('default' => filter_var($server['id']))) . "\n";
  print $this->Form->hidden($sprefix . '.co_voms_provisioner_target_id', array('default' => filter_var($server['co_voms_provisioner_target_id']))) . "\n";
  print $this->Form->hidden($sprefix . '.protocol', array('default' => filter_var($server['protocol']))) . "\n";
  print $this->Form->hidden($sprefix . '.host', array('default' => filter_var($server['host']))) . "\n";
  print $this->Form->hidden($sprefix . '.port', array('default' => filter_var($server['port']))) . "\n";
  print $this->Form->hidden($sprefix . '.dn', array('default' => filter_var($server['dn']))) . "\n";
  ?>
  <span class="voms-server-uri"><?php print $base_uri;?></span>
</li>
<?php endforeach; ?>
<?php else: ?>
  <a href="#"
     class='voms-server-list-edit ui-button ui-corner-all ui-widget ui-state-disabled'>
    <span class="ui-button-icon ui-icon ui-icon-pencil"></span>
  </a>
  <a href="#"
     data-db="false"
     onclick="rmv_voms_entry(this);return false;"
     class='voms-server-list-delete ui-button ui-corner-all ui-widget'>
    <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
  </a>
<?php endif; ?>
