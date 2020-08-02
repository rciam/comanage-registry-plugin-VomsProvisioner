<?php
$cert_preview = openssl_x509_parse(base64_decode($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_cert']));
$now = time();
$validTo = $cert_preview['validTo_time_t'];
$datediff = ($validTo - $now) / (60 * 60 * 24);
if($datediff >= 30) {
  $class_modal = 'bg-success';
} else if($datediff < 30 && $datediff > 0) {
  $class_modal = 'bg-warning';
} else {
  $class_modal = 'bg-alert';
}
?>

<!-- The Modal -->
<!-- Modal -->
<div class="modal" id="certPreviewModal" tabindex="-1" role="dialog" aria-labelledby="certPreviewModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header <?php print $class_modal; ?>">
        <h4 class="modal-title-x509"><?php print _txt('pl.voms_provisioner.x509'); ?></h4>
      </div>
      <div class="modal-body border-bottom-0 cert-preview">
        <div class="row">
          <div class="col-md-4"><span><?php print _txt('pl.voms_provisioner.cn'); ?>:</span></div>
          <div class="col-md-8"><?php print $cert_preview['subject']['CN']; ?></div>
        </div>
        <div class="row">
          <div class="col-md-4"><span><?php print _txt('pl.voms_provisioner.org'); ?>:</span></div>
          <div class="col-md-8"><?php print $cert_preview['subject']['O']; ?></div>
        </div>
        <div class="row">
          <div class="col-md-4"><span><?php print _txt('pl.voms_provisioner.cntr'); ?>:</span></div>
          <div class="col-md-8"><?php print $cert_preview['subject']['C']; ?></div>
        </div>
        <div class="row">
          <div class="col-md-4"><span><?php print _txt('pl.voms_provisioner.vlfr'); ?>:</span></div>
          <div class="col-md-8"><?php print date("Y-m-d", $cert_preview['validFrom_time_t']); ?></div>
        </div>
        <div class="row">
          <div class="col-md-4"><span><?php print _txt('pl.voms_provisioner.vlto'); ?>:</span></div>
          <div class="col-md-8"><?php print date("Y-m-d", $cert_preview['validTo_time_t']); ?></div>
        </div>
        <div class="row">
          <div class="col-md-4"><span><?php print _txt('pl.voms_provisioner.issuer'); ?>:</span></div>
          <div class="col-md-8"><?php print $cert_preview['issuer']['CN'] . "," . $cert_preview['issuer']['O']; ?></div>
        </div>
        <div class="row">
          <div class="col-md-4"><span><?php print _txt('pl.voms_provisioner.sn'); ?>:</span></div>
          <div class="col-md-8 serial-number"><?php print $cert_preview['serialNumber']; ?></div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><?php print _txt('fd.voms_provisioner.close'); ?></button>
      </div>
    </div>
  </div>
</div>
