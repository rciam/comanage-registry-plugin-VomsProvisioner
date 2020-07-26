<?php
?>

<?php if(!empty($base_uri)): ?>
<li id="voms-server-entry<?php print $id; ?>" data-server_id="<?php print $id; ?>" class="voms-server-list">
  <a href="#"
     id='voms-server-list-edit'
     onclick="edit_voms_field(this)"
     class='ui-button ui-corner-all ui-widget'>
    <span class="ui-button-icon ui-icon ui-icon-pencil"></span>
  </a>
  <a href="#"
     id='voms-server-list-delete'
     onclick="rmv_voms_entry(this)"
     class='ui-button ui-corner-all ui-widget'>
    <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
  </a>
  <span class="voms-server-uri"><?php print $base_uri;?></span>
  <em id="server-alive" class="material-icons status-icon success">done</em>
  <em id="server-error" class="material-icons status-icon failed">close</em>
  <em id="server-neutral" class="material-icons status-icon neutral"></em>
</li>
<?php else: ?>
  <a href="#"
     id='voms-server-list-edit'
     class='ui-button ui-corner-all ui-widget ui-state-disabled'>
    <span class="ui-button-icon ui-icon ui-icon-pencil"></span>
  </a>
  <a href="#"
     id='voms-server-list-delete'
     class='ui-button ui-corner-all ui-widget ui-state-disabled'>
    <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
  </a>
<?php endif; ?>

