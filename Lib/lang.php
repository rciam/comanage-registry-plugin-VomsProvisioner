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
  'er.voms_provisioner.ntfnd_api'          => 'Rest API URL could not be constructed',
  'er.voms_provisioner.token.blackhauled'  => 'Token expired.Please try again.',

  // Plugin texts
  'pl.voms_provisioner.serveruri'          => 'Server URI',
  'pl.voms_provisioner.serveruri.desc'     => 'VOMS Base Server URI(s)',
  'pl.voms_provisioner.bulkurl'            => 'Bulk URL',
  'pl.voms_provisioner.bulkurl.desc'       => 'This is a URL containing a JSON formatted list of provided VOMS for each VO.<br>Check <a href="#">link</a> for further info.',
  'pl.voms_provisioner.host'               => 'Domain',
  'pl.voms_provisioner.host.desc'          => 'VOMS domain name that will be used to form the VOMS base URI',
  'pl.voms_provisioner.dn'                 => 'DN',
  'pl.voms_provisioner.dn.desc'            => 'VOMS subject DN',
  'pl.voms_provisioner.vo'                 => 'Vo name',
  'pl.voms_provisioner.vo.desc'            => 'Name of the virtual organization to be used alongside with the host name for the url construction',
  'pl.voms_provisioner.port'               => 'Port',
  'pl.voms_provisioner.port.desc'          => 'VOMs HTTP port we will use to connect',
  'pl.voms_provisioner.robot_cert'         => 'Robot Certificate',
  'pl.voms_provisioner.robot_cert.desc'    => 'Certificate of VOMS robot user with administrator priviledges',
  'pl.voms_provisioner.robot_key'          => 'Robot Key',
  'pl.voms_provisioner.robot_key.desc'     => 'Key of VOMS robot user with administrator priviledges',
  'pl.voms_provisioner.info'               => 'Define the VOMs where you will push/sync the status of the associated Group/COU',

  'fd.voms_provisioner.srv'                => 'Add',
  'fd.voms_provisioner.clr'                => 'Clear',
  'fd.voms_provisioner.srv_add'            => 'VOMs Server',
  'fd.voms_provisioner.add'                => 'Add',

  // Database operations
  'rs.voms_provisioner.cleared'            => 'Entries Removed',
  'rs.voms_provisioner.cleared.fail'       => 'Clearing Entries Failed',

  // Operation messages
  'op.voms_provisioner.blackhauled'        => '%1$s Request Blackhauled(t:"%2$s")',
  'op.voms_provisioner.nogroup'            => 'No Group/VO configured',
  'op.voms_provisioner.nocert'             => 'No Valid certificate found',
  'op.voms_provisioner.srv_add_mode'       => 'Choose Server Import Mode:',

  // Known VOMs error
  'op.voms_provisioner.vo_existing_person'    => 'A user holding a certificate with the following subject %1$s already exists in this VO.',

);
