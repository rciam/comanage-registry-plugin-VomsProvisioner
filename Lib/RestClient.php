<?php
require_once "./Lib/HttpCurlClient.php";
class RestClient
{
  private $user_cert;
  private $key_cert;
  private $baseUrl;


  public function __construct($baseUrl, $user_cert, $key_cert)
  {
    $this->baseUrl = $baseUrl;
    $this->user_cert = $user_cert;
    $this->key_cert = $key_cert;

  }
  public function setHeaders($post_fields)
  {
    // Create HttpHeaders
    $http_headers = array(
      'X-VOMS-CSRF-GUARD: ""',
    );
    if(!empty($post_fields)) {
      $http_headers[] = 'Content-Type: application/json';
    }

    return $http_headers;
  }
  public function setCurlOptions($returnTransfer, $followLocation, $sslVerifypeer, $verbose, $timeout)
  {
    $options['returnTransfer'] = $returnTransfer;
    $options['followLocation'] = $followLocation;
    $options['sslVerifypeer'] = $sslVerifypeer;
    $options['verbose'] = $verbose;
    $options['timeout'] = $timeout;
    return $options;
  }

  public function createUser($parameters)
  {
    $url = $this->baseUrl . '/create-user.action';

    //Set Options
    $options = $this->setCurlOptions(true, false, false, true, 5000);
    $options['header']  = $this->setHeaders($parameters['post_fields']);

    HttpCurlClient::RestHttpCurlClient($url,  $parameters['post_fields'], $this->user_cert, $this->key_cert, $error, $info, $options);
  }

}
