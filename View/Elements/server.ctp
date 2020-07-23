<?php
?>

<div class="modal-body mode-toggler">
  <label><?php print _txt('op.voms_provisioner.srv_add_mode'); ?></label>
  <select id="mode-toggler"
          name="mode-toggler"
          onchange="toggle_server_load_mode(this.value)">
    <?php foreach (VomsServerConfigEnum::type as $value => $text): ?>
      <?php if($value === VomsServerConfigEnum::SINGLE):?>
        <option id="<?php print $value; ?>" selected value="<?php print $value; ?>"><?php print $text; ?></option>
      <?php else:?>
        <option id="<?php print $value; ?>" value="<?php print $value; ?>"><?php print $text; ?></option>
      <?php endif;?>
    <?php endforeach; ?>
  </select>
</div>

<form id="vom_servers" name="voms_servers">
  <!-- Bulk Mode -->
  <div class="modal-body bulk-mode" style="display: none;">
    <div class="form-group">
      <input type="text" class="form-control" id="bulkURL" aria-describedby="bulkHelp" placeholder="<?php print _txt('pl.voms_provisioner.bulkurl'); ?>">
      <small id="bulkHelp" class="form-text text-muted"><?php print _txt('pl.voms_provisioner.bulkurl.desc'); ?></small>
    </div>
  </div>
  <!-- Single Mode -->
  <div class="modal-body single-mode">
    <div class="form-group">
      <input type="text" class="form-control" id="host" aria-describedby="hostHelp" placeholder="<?php print _txt('pl.voms_provisioner.host'); ?>">
      <small id="hostHelp" class="form-text text-muted"><?php print _txt('pl.voms_provisioner.host.desc'); ?></small>
    </div>
    <div class="form-group">
      <input type="number" class="form-control" id="port" aria-describedby="portHelp" placeholder="<?php print _txt('pl.voms_provisioner.port'); ?>">
      <small id="portHelp" class="form-text text-muted"><?php print _txt('pl.voms_provisioner.port.desc'); ?></small>
    </div>
    <div class="form-group">
      <input type="text" class="form-control" id="dn" aria-describedby="dnHelp" placeholder="<?php print _txt('pl.voms_provisioner.dn'); ?>" value="">
      <small id="dnHelp" class="form-text text-muted"><?php print _txt('pl.voms_provisioner.dn.desc'); ?></small>
    </div>
  </div>
  <div class="modal-footer border-top-0 d-flex justify-content-center">
    <a href="#"
       id='voms-server-delete'
       onclick="requestMode()"
       style="text-decoration: none;"
       class="spin submit-button mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect"><?php print _txt('fd.voms_provisioner.add'); ?>
    </a>
  </div>
</form>