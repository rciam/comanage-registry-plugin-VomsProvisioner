<?php
require_once "OpenSslNameUtils.php";

$oSsl = new OpenSslNameUtils();
$srcDn = 'CN=NIKOS MASTORIS kokfTSdakods2nTata,O=EGI Foundation,OU=AAI-Pilot,O=EGI';
        
echo $oSsl->convertfromRfc2253($srcDn);
//$oSsl->normalize('/O=EGI/OU=AAI-Pilot/O=EGI Foundation/CN=NIKOS MASTORIS kokfTSdakods2nTATTA');
echo $oSsl->opensslToRfc2253('/O=EGI/OU=AAI-Pilot/O=EGI Foundation/CN=NIKOS MASTORIS kokfTSdakods2nTATTA', false);
?>