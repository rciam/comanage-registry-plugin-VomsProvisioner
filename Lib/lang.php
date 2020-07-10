<?php
  
global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_voms_provisioner_texts['en_US'] = array(
  // Titles, per-controller
  'ct.co_voms_provisioner_targets.1'       => 'VOMs Provisioner Target',
  'ct.co_voms_provisioner_targets.pl'      => 'VOMs Provisioner Targets',

  // Error messages
  'er.voms_provisioner.connect'            => 'Failed to connect to VOMs web services server',
  'er.voms_provisioner.subject'            => 'Could not determine co person subject dn',
  'er.voms_provisioner.issuer'             => 'Could not determine certificate issuer',
  'er.voms_provisioner.canonical'          => 'Could not determine co person canonical name',
  'er.voms_provisioner.nohst_prt'          => 'Host/Port missing',

  'pl.voms_provisioner.host'               => 'Base server URL',
  'pl.voms_provisioner.host.desc'          => 'VOMS hostname',
  'pl.voms_provisioner.vo'                 => 'Vo name',
  'pl.voms_provisioner.vo.desc'            => 'Name of the virtual organization to be used alongside with the host name for the url construction',
  'pl.voms_provisioner.port'               => 'Port',
  'pl.voms_provisioner.port.desc'          => 'VOMs HTTP port we will use to connect',
  'pl.voms_provisioner.robot_cert'         => 'Robot Certificate',
  'pl.voms_provisioner.robot_cert.desc'    => 'Certificate of VOs robot user with administrator priviledges.',
  'pl.voms_provisioner.robot_key'          => 'Robot Key',
  'pl.voms_provisioner.robot_key.desc'     => 'Key of VOs robot user with administrator priviledges',
  // Plugin texts
  'pl.voms_provisioner.info'               => 'Define the VOMs where you will push/sync the status of the associated Group/COU',

  // Success messages
  'op.voms_provisioner.blackhauled'        => '%1$s Request Blackhauled(t:"%2$s")',

);
