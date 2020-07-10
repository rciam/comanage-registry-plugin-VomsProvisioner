<?php

class HttpCurlClient
{

  /**
   * HttpCurlClient
   *
   * @param $url      The URL used to address the request
   * @param $fields   List of query parameters in a key=>value array format
   * @param $error
   * @param $info
   * @param array $options
   * @return bool|string
   * @throws Exception
   */
  public static function SoapHttpCurlClient($url, $xml_post_string, $user_fcert, $user_fkey, &$error, &$info, $options = NULL)
  {

    // open connection
    $ch = curl_init();

    // set the url, number of POST vars, POST data
    // Content-type: application/x-www-form-urlencoded => is the default approach for post requests
    if (empty($options['curlType']) || isset($options['curlType']) == 'POST') {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
      curl_setopt($ch, CURLOPT_HTTPHEADER, !empty($options) && isset($options['header']) ? $options['header'] : FALSE);
      curl_setopt($ch, CURLOPT_SSLCERT, $user_fcert);
      curl_setopt($ch, CURLOPT_SSLKEY, $user_fkey);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !empty($options) && isset($options['sslVerifypeer']) ? $options['sslVerifypeer'] : FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, !empty($options) && isset($options['returnTransfer']) ? $options['returnTransfer'] : TRUE);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, !empty($options) && isset($options['followLocation']) ? $options['followLocation'] : FALSE);
      curl_setopt($ch, CURLOPT_VERBOSE, !empty($options) && isset($options['verbose']) ? $options['verbose'] : TRUE);
      curl_setopt($ch, CURLOPT_TIMEOUT, !empty($options) && isset($options['timeout']) ? $options['timeout'] : 3000);
    }
    // execute post
    $response = curl_exec($ch);
    echo $response;
    // fixme: Make curl throw an dnot return the errors
    $error = "";
    if (empty($response)) {
      // probably connection error
      $error = curl_error($ch);
      echo $error;
      // if (Configure::read('debug')) {
      //  CakeLog::write('error', __METHOD__ . ':: Http Request Failed::' . $error);
      // }
    }

    $info = curl_getinfo($ch);

    // close connection
    curl_close($ch);
    // return success
    return $response;
  }


  public static function RestHttpCurlClient($url, $post_fields, $user_cert, $user_key, &$error, &$info, $options = NULL)
  {

    var_dump($post_fields);
    $response_headers = [];

    $ch = curl_init();
    // Construct the action url
    //$url = $url . '/' . $action;
    // set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($post_fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, !empty($options) && isset($options['header']) ? $options['header'] : FALSE);
    curl_setopt($ch, CURLOPT_SSLCERT, $user_cert);
    curl_setopt($ch, CURLOPT_SSLKEY, $user_key);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !empty($options) && isset($options['sslVerifypeer']) ? $options['sslVerifypeer'] : FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, !empty($options) && isset($options['returnTransfer']) ? $options['returnTransfer'] : TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, !empty($options) && isset($options['followLocation']) ? $options['followLocation'] : FALSE);
    curl_setopt($ch, CURLOPT_VERBOSE, !empty($options) && isset($options['verbose']) ? $options['verbose'] : TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, !empty($options) && isset($options['timeout']) ? $options['timeout'] : 3000);
    // this function is called by curl for each header received
    curl_setopt(
      $ch,
      CURLOPT_HEADERFUNCTION,
      function ($curl, $header) use (&$response_headers) {
        $len = strlen($header);
        $header = trim($header);
        if (!empty($header)) {
          $header = explode(':', $header, 2);
          if (count($header) < 2) {
            // This is the summary
            $header = implode('', $header);
            $header = explode(' ', $header, 3);
            $response_headers['server_msg'] = $header[2];
          } else {
            $response_headers[strtolower(trim($header[0]))][] = trim($header[1]);
          }
        }

        return $len;
      }
    );

    // execute post
    $response = curl_exec($ch);
    $status_code = "";
    $error = "";
    if (empty($response)) {
      // probably connection error
      $error = curl_error($ch);
    }

    $info = curl_getinfo($ch);
    if ($info["http_code"] !== 200) {
      echo $response_headers["server_msg"];
    }

    // close connection
    curl_close($ch);
  }
}
