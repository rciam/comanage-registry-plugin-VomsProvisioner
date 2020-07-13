<?php
//use GuzzleHttp\Client;
// XXX during testing i need to import the Guzzle Vendor library manually
require_once '../Vendor/autoload.php';

class VomsRestClient
{
  private $host = null;
  private $port = null;
  private $vo_name = null;
  private $robot_cert = null;
  private $robot_key = null;
  private $_http_client = null;
  private $_fcert_robot = null;
  private $_fkey_robot = null;
  private $_req_location = null;


  /**
   * VomsRestClient constructor.
   * @param $host
   * @param $port
   * @param $vo_name
   * @param $robot_cert
   * @param $robot_key
   */
  public function __construct($host, $port, $vo_name, $robot_cert, $robot_key)
  {
    $this->host = $host;
    $this->port = $port;
    $this->vo_name = $vo_name;
    $this->robot_cert = $robot_cert;
    $this->robot_key = $robot_key;

    $this->_fkey_robot = $this->sslKey();
    $this->_fcert_robot = $this->sslCert();
    $this->_req_location = '/voms/' . $this->vo_name . '/apiv2';
  }

  /**
   *  Close the ssl_key and ssl_cert temporary files if still open
   */
  public function __destruct()
  {
    if(!is_null($this->_fcert_robot)) {
      fclose($this->_fcert_robot);
    }
    if(!is_null($this->_fkey_robot)) {
      fclose($this->_fkey_robot);
    }
  }


  /**
   * @param bool $json_content
   * @param array $head_fields
   * @return string[] Array of Http Headers
   */
  protected function constructHeaders($json_content=false, $head_fields=array())
  {
    // Create HttpHeaders
    $http_headers = [
      'X-VOMS-CSRF-GUARD' => '',
    ];
    if($json_content){
      $http_headers['Content-Type'] = 'application/json; charset=utf-8';
      $http_headers['Accept'] = 'application/json';
    }

    if(!empty($head_fields)) {
      foreach ($head_fields as $key => $field) {
        $http_headers[$key] = $field;
      }
    }

    return $http_headers;
  }


  /**
   * @return string|null
   * @todo generalize the construction of the endpoint
   */
  protected function baseUri() {
    if(is_null($this->host) || is_null($this->port) || is_null($this->vo_name)) {
      return null;
    }
    return 'https://' . $this->host . ':' . $this->port;
  }

  /**
   * @return Object GuzzleHttp\Client | null
   */
  protected function httpClient() {
    if(is_null($this->baseUri())) {
     return null;
    }
    if(is_null($this->_http_client)) {
      $this->_http_client = new GuzzleHttp\Client($this->getDefaults());
    }
    return $this->_http_client;
  }

  /**
   * @return file handler robot_cert
   */
  protected function sslCert() {
    $handle_fcert = tmpfile();
    fwrite($handle_fcert, $this->robot_cert);
    // XXX Uncomment for debug
//    $user_fcert = stream_get_meta_data($handle_fcert)['uri'];
//    var_dump(file_get_contents($user_fcert));
    return $handle_fcert;
  }

  /**
   * @return file handler robot_key
   */
  protected function sslKey() {
    $handle_fkey = tmpfile();
    fwrite($handle_fkey, $this->robot_key);
    $user_fkey = stream_get_meta_data($handle_fkey)['uri'];
    // XXX uncomment for debug
//    var_dump(file_get_contents($user_fkey));
    chmod($user_fkey, 0644);
    return $handle_fkey;
  }


  /**
   * @return array
   * @todo Extend config page to handle these options. Create two sections. Server config, Http Config
   */
  public function getDefaults()
  {
    return array(
      'base_uri' => $this->baseUri(),
      'timeout' => 5,
      'connect_timeout' => 2,
      'verify' => false,
      'cert' => stream_get_meta_data($this->_fcert_robot)['uri'],
      'ssl_key' => stream_get_meta_data($this->_fkey_robot)['uri']
    );
  }


  /**
   * @param $action
   * @param array $post_fields
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function vomsRestRequest($action, $post_fields=array()) {
    try{
      $client = $this->httpClient();
      $options = [
        'debug' => true,
        'headers' => $this->constructHeaders(!empty($post_fields)),
      ];
      if(!empty($post_fields)) {
        $options['json'] = $post_fields;
      }
      $response = $client->request('POST', $this->_req_location . '/' . $action, $options);
      var_export($response);
      $headers = $response->getHeaders();
//      var_export($headers);

      $body = $response->getBody()->getContents();
      $status = $response->getStatusCode();
      var_export($body);
      var_export($status);

    } catch (\GuzzleHttp\Exception\RequestException $e) {
      var_export($e->xdebug_message);
    } catch (Exception $e) {
      var_export($e);
    }
  }

}
