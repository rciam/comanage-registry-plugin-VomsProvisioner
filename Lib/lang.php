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
  'pl.voms_provisioner.serveruri'           => 'Server URI',
  'pl.voms_provisioner.serveruri.desc'      => 'VOMS Base Server URI(s)',
  'pl.voms_provisioner.importmode'          => 'Import Mode',
  'pl.voms_provisioner.importmode.desc'     => 'VOMS append or overwrite:',
  'pl.voms_provisioner.bulkurl'             => 'Bulk URL',
  'pl.voms_provisioner.bulkurl.desc'        => 'This is a URL containing a JSON formatted list of provided VOMS for each VO.<br>Check <a href="#">link</a> for further info.',
  'pl.voms_provisioner.proxyurl'            => 'Proxy URL',
  'pl.voms_provisioner.proxyurl.desc'       => 'Use a Proxy to by pass Cross Domain request errors.<br>Tested with <a href="https://jsonp.afeld.me/">https://jsonp.afeld.me/</a>.',
  'pl.voms_provisioner.host'                => 'Domain',
  'pl.voms_provisioner.host.desc'           => 'VOMS domain name that will be used to form the VOMS base URI',
  'pl.voms_provisioner.dn'                  => 'DN',
  'pl.voms_provisioner.dn.desc'             => 'VOMS subject DN',
  'pl.voms_provisioner.protocol'            => 'Connection Protocol',
  'pl.voms_provisioner.protocol.desc'       => 'VOMS HTTP Connection Protocol:',
  'pl.voms_provisioner.vo'                  => 'Vo name',
  'pl.voms_provisioner.vo.desc'             => 'Name of the virtual organization to be used alongside with the host name for the url construction',
  'pl.voms_provisioner.port'                => 'Port',
  'pl.voms_provisioner.port.desc'           => 'VOMs HTTP port we will use to connect',
  'pl.voms_provisioner.robot_cert'          => 'Robot Certificate',
  'pl.voms_provisioner.robot_cert.desc'     => 'Certificate of VOMS robot user with administrator priviledges',
  'pl.voms_provisioner.robot_key'           => 'Robot Key',
  'pl.voms_provisioner.robot_key.desc'      => 'Key of VOMS robot user with administrator priviledges',
  'pl.voms_provisioner.openssl_syntax'      => 'Openssl Syntax',
  'pl.voms_provisioner.openssl_syntax.desc' => 'Enable Openssl syntax',
  'pl.voms_provisioner.ca_dn_default'       => 'Certificate Authority DN',
  'pl.voms_provisioner.assurance_req'       => 'Assurance Requirement',
  'pl.voms_provisioner.assurance_level'     => 'Assurance Value',
  'pl.voms_provisioner.assurance_level.desc'=> 'Assurance Value Prerequisite for the configured CoGroup',
  'pl.voms_provisioner.assurance_level_type'     => 'Assurance Value Type',
  'pl.voms_provisioner.assurance_level_type.desc'=> 'Assurance Value Type Prerequisite for the configured CoGroup',
  'pl.voms_provisioner.ca_dn_default.desc'  => 'Provide the default DN of the CA in case not available in User\'s Certificate.<br><b>Suggestion:</b> Use the Subject DN of the Actor.',
  'pl.voms_provisioner.cert_mdl'            => 'Certificate Model',
  'pl.voms_provisioner.cert_mdl.desc'       => 'Provide the name of the Certificate Model used to store user\'s certificates.<br>In case of plugin use the syntax Plugin.Model',
  'pl.voms_provisioner.subject_col_name'      => 'Certificate Subject',
  'pl.voms_provisioner.subject_col_name.desc' => 'Certificate Subject DN Column Name',
  'pl.voms_provisioner.issuer_col_name'       => 'Certificate Issuer',
  'pl.voms_provisioner.issuer_col_name.desc'  => 'Certificate Issuer DN Column Name',
  'pl.voms_provisioner.info'                => 'Define the VOMs where you will push/sync the status of the associated Group/COU',

  // Cert Preview
  'pl.voms_provisioner.cn'                 => 'Common Name',
  'pl.voms_provisioner.org'                => 'Organization',
  'pl.voms_provisioner.cntr'               => 'Country',
  'pl.voms_provisioner.vlfr'               => 'Valid From',
  'pl.voms_provisioner.vlto'               => 'Valid To',
  'pl.voms_provisioner.issuer'             => 'Issuer',
  'pl.voms_provisioner.sn'                 => 'Serial Number',
  'pl.voms_provisioner.x509'               => 'Certificate X509',

  'fd.voms_provisioner.add'                => 'Add',
  'fd.voms_provisioner.update'             => 'Update',
  'fd.voms_provisioner.load'               => 'Load',
  'fd.voms_provisioner.clear'              => 'Clear',
  'fd.voms_provisioner.edit'               => 'Edit',
  'fd.voms_provisioner.cancel'             => 'Cancel',
  'fd.voms_provisioner.del'                => 'Delete',
  'fd.voms_provisioner.save'               => 'Save',
  'fd.voms_provisioner.close'              => 'Close',
  'fd.voms_provisioner.delall'             => 'Delete all items',
  'fd.voms_provisioner.srv_add'            => 'Add VOMs Server(s)',
  'fd.voms_provisioner.srv_edit'           => 'Edit VOMs Server',
  'fd.voms_provisioner.floaded'            => 'File Loaded',

  // Database operations
  'rs.voms_provisioner.cleared'            => 'Entries Removed',
  'rs.voms_provisioner.cleared.1'          => 'Entry Removed',
  'rs.voms_provisioner.cleared.fail'       => 'Clearing Entries Failed',

  // Operation messages
  'op.voms_provisioner.blackhauled'        => '%1$s Request Blackhauled(t:"%2$s")',
  'op.voms_provisioner.nogroup'            => 'No Group/VO configured',
  'op.voms_provisioner.nocert'             => 'No Valid certificate found',
  'op.voms_provisioner.srv_add_mode'       => 'Choose Server Import Mode:',
  'op.voms_provisioner.noport'             => 'No Port provided',
  'op.voms_provisioner.nohost'             => 'No Host provided',

  // Known VOMs error
  'op.voms_provisioner.vo_existing_person' => 'A user holding a certificate with the following subject %1$s already exists in this VO.',
  'op.voms_provisioner.clear'              => 'VOMS entries will be permanently deleted and cannot be recovered. Are you sure?',
  'op.voms_provisioner.remove'             => 'Delete VOMS',

);
