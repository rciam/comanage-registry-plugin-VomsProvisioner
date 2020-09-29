<?php

//require_once "enum.php";
App::uses('CakeLog', 'Log');

class OpenSslNameUtils {

  /**
   * normalizeLabel
   *
   * @param string $label
   * @return void
   */

  public static function normalizeLabel($label) {
    if(!empty(OpenSslNameUtilsEnum::normalized_labels[strtolower($label)])) {
      $normalized = OpenSslNameUtilsEnum::normalized_labels[strtolower($label)];
    }
    return (empty($normalized) ? $label : $normalized);
  }

  /**
   * normalize
   *
   * @param string $legacyDN
   * @return void
   */

  public static function normalize($legacyDN) {
    $output = strtolower(
      preg_replace_callback(
        '/\/([^=]+)+=/m',
        function ($matches) {
          return '/' . $this->normalizeLabel($matches[1]) . '=';
        },
        $legacyDN
      )
    );

    return $output;
  }

  /**
   * opensslToRfc2253
   *
   * @param string $inputDN
   * @param boolean $withWildCards
   * @return void
   */

  public static function opensslToRfc2253($inputDN, $withWildCards) {
    if(strlen($inputDN) < 2 || substr($inputDN, 0, 1) != "/") {
      // throw exception
      throw new InvalidArgumentException(
        "The string '" . $inputDN .
        "' is not a valid OpenSSL-encoded DN"
      );
    }
    $inputDN = str_replace(',', '\\,', $inputDN);
    $parts = explode('/', $inputDN);
    $avas = array();
    array_push($avas, $parts[1]);
    if(count($parts) < 2) {
      return substr($inputDN, 1);
    }
    for($i = 2, $j = 0, $len = count($parts); $i < $len; $i++) {
      if(!(strpos($parts[$i], '=') != false || ($withWildCards && strpos($parts[$i], '*') != false))) {
        $cur = $avas[$j];
        $avas[++$j] = $cur . '/' . $parts[$i];
      } else {
        array_push($avas, $parts[$i]);
      }
    }
    $buf = '';
    for($i = count($avas) - 1; $i > 0; $i--) {
      $buf .= $avas[$i] . ',';
    }
    $buf .= $avas[0];
    return $buf;
  }

  /**
   * convertfromRfc2253
   * This function splits Rfc2253 by '=' and then for each splitted item
   * get the DN type which is between the latest ',' and '='
   * then fills an array with the types and values of the DN
   * and reverse the array to get the openSSL syntax.
   * Use it with caution!
   * @param string $srcDn
   * @return void
   */

  public static function convertfromRfc2253($srcDn) {
    if(strpos($srcDn, '/') === 0) { // we assume is OpenSsl Syntax already
      return $srcDn;
    }
    $avasSeparator = '/';
    preg_match_all('/([^=]+)+=/m', $srcDn, $matches, PREG_SET_ORDER, 0);
    $last_value = strripos($srcDn, '=');
    $openSSL = array();

    $i = 0;
    foreach($matches as $match) {
      $pos = strripos($match[1], ",");
      if($pos != 0) {
        $replace_txt = $avasSeparator . substr($match[1], $pos + 1) . '=';
        $openSSL[$i] = $openSSL[$i] . substr($match[1], 0, $pos);
        $openSSL[$i + 1] = $replace_txt;
        $i++;
      } else {
        $replace_txt = '/' . substr($match[1], $pos) . '=';
        $openSSL[$i] = $replace_txt;
      }
      $match[0] = substr($match[0], $pos);
    }

    $openSSL[$i] = $openSSL[$i] . substr($srcDn, $last_value + 1);
    $openSSL = array_reverse($openSSL);
    $openSslDn = implode($openSSL);

    return $openSslDn;
  }
}
