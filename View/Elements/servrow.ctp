<?php
?>

<?php if(!empty($base_uri)): ?>
<li class="voms-server-list">
  <a href="#"
     id='voms-server-edit'
     onclick="editSrv(this)"
     class='ui-button ui-corner-all ui-widget'>
    <span class="ui-button-icon ui-icon ui-icon-pencil"></span>
  </a>
  <a href="#"
     id='voms-server-delete'
     onclick="removeSrv(this)"
     class='ui-button ui-corner-all ui-widget'>
    <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
  </a>
  <?php print $base_uri;?>
  <em id="server-alive" class="material-icons status-icon success">done</em>
  <em id="server-error" class="material-icons status-icon failed">close</em>
  <em id="server-neutral" class="material-icons status-icon neutral"></em>
</li>
<?php else: ?>
  <a href="#"
     id='voms-server-edit'
     class='ui-button ui-corner-all ui-widget ui-state-disabled'>
    <span class="ui-button-icon ui-icon ui-icon-pencil"></span>
  </a>
  <a href="#"
     id='voms-server-delete'
     class='ui-button ui-corner-all ui-widget ui-state-disabled'>
    <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
  </a>
<?php endif; ?>

