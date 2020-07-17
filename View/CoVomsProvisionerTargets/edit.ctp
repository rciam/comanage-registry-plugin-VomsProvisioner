<link rel="stylesheet" type="text/css" href="<?php print $this->request->webroot . 'voms_provisioner' ?>/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?php print $this->request->webroot . 'voms_provisioner' ?>/css/voms-provisioner.css">

<script src="<?php print $this->request->webroot . 'voms_provisioner' ?>/js/bootstrap.min.js"></script>
<script src="<?php print $this->request->webroot . 'voms_provisioner' ?>/js/jquery/jquery.base64.js"></script>
<script src="<?php print $this->request->webroot . 'voms_provisioner' ?>/js/server.js"></script>

<?php
  // Get a pointer to our model
  $model = $this->name;
  $req = Inflector::singularize($model);

  // Add page title & page buttons
  $params = array();
  $params['title'] = "Edit "._txt('ct.co_voms_provisioner_targets.1');
  if(!empty($this->plugin)) {
    if(file_exists(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc")) {
      include(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc");
    } elseif(file_exists(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc")) {
      include(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc");
    }
  } else {
    if(file_exists(APP . "View/" . $model . "/buttons.inc")) {
      include(APP . "View/" . $model . "/buttons.inc");
    }
  }
  print $this->element("pageTitleAndButtons", $params);

  $submit_label = _txt('op.save');
  print $this->Form->create($req,
                            // CO-1274
                            array('inputDefaults' => array('label' => false, 'div' => false)));
  if(!empty($this->plugin)) {
    if(file_exists(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc")) {
      include(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc");
    } elseif(file_exists(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc")) {
      include(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc");
    }
  } else {
    include(APP . "View/" . $model . "/fields.inc");
  }
  print $this->Form->end();
?>
