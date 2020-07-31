<?php
  $cert_preview = openssl_x509_parse(base64_decode($co_voms_provisioner_targets[0]['CoVomsProvisionerTarget']['robot_cert']));
  $now = time();
  $validTo = $cert_preview['validTo_time_t'];
  $datediff = ($validTo - $now) / (60 * 60 * 24);
  if($datediff>30) {
    $class_modal = 'bg-success';
  }
  else if($datediff<30 && $datediff>0) {
    $class_modal = 'bg-warning';
  }
  else {
    $class_modal = 'bg-alert';
  }
?>

<!-- The Modal -->
<!-- Modal -->
<div class="modal" id="certPreviewModal" tabindex="-1" role="dialog" aria-labelledby="certPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header <?php print $class_modal;?>">
        <h4 class="modal-title">Certificate X509</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body border-bottom-0 cert-preview">
        <!--    todo: Put the header in the center    -->
        <?php 
          print '<div class="row"><div class="col-md-4"><span>Common Name: </span></div><div class="col-md-8">'.$cert_preview['subject']['CN'].'</div></div>';
          print '<div class="row"><div class="col-md-4"><span>Organisation:  </span></div><div class="col-md-8">'.$cert_preview['subject']['O'].'</div></div>';
          print '<div class="row"><div class="col-md-4"><span>Country:  </span></div><div class="col-md-8">'.$cert_preview['subject']['C'].'</div></div>';
          print '<div class="row"><div class="col-md-4"><span>Valid From:  </span></div><div class="col-md-8">'.date("Y-m-d", $cert_preview['validFrom_time_t']).'</div></div>';
          print '<div class="row"><div class="col-md-4"><span>Valid To:  </span></div><div class="col-md-8">'.date("Y-m-d", $cert_preview['validTo_time_t']).'</div></div>';
          print '<div class="row"><div class="col-md-4"><span>Issuer:  </span></div><div class="col-md-8">'.$cert_preview['issuer']['CN'].', '.$cert_preview['issuer']['O'].'</div></div>';
          print '<div class="row"><div class="col-md-4"><span>Serial Number:  </span></div><div class="col-md-8 serial-number">'.$cert_preview['serialNumber'].'</div></div>';
        ?>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
