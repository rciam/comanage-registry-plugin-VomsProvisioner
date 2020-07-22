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

