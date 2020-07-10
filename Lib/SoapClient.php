<?php
require_once "./Lib/HttpCurlClient.php";
class SoapClient
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
  public function setHeaders($xml_post_string)
  {
    // Create HttpHeaders
    $http_headers = array(
      'X-VOMS-CSRF-GUARD: ""',
    );
    $http_headers[] = 'Content-Type: text/xml;charset="utf-8"';
    $http_headers[] = 'Accept: text/xml';
    $http_headers[] = 'Cache-Control: no-cache';
    $http_headers[] = 'Pragma: no-cache';
    $http_headers[] = 'Content-length: ' . strlen($xml_post_string);
    $http_headers[] = 'SOAPAction: ""';

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

  public function getUser($parameters)
  {
    $url = $this->baseUrl . '/VOMSAdmin';

    if (!empty($parameters) && isset($parameters['certificateSubject']) && isset($parameters['caSubject'])) {
      // construct envelope
      $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
            <getUser xmlns="http://glite.org/wsdl/services/org.glite.security.voms.service.admin">
            <in0>' . $parameters['certificateSubject'] . '</in0> 
            <in1>' . $parameters['caSubject'] . '</in1> 
            </getUser>
            </soap:Body>
            </soap:Envelope>';
    }

    //Set Options
    $options = $this->setCurlOptions(true, false, false, true, 5000);
    $options['header']  = $this->setHeaders($xml_post_string);

    HttpCurlClient::SoapHttpCurlClient($url, $xml_post_string, $this->user_cert, $this->key_cert, $error, $info, $options);
  }

  public function deleteUser($parameters)
  {
    $url = $this->baseUrl . '/VOMSAdmin';
    if (!empty($parameters) && isset($parameters['certificateSubject']) && isset($parameters['caSubject'])) {
      // construct envelope
      $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
            <deleteUser xmlns="http://glite.org/wsdl/services/org.glite.security.voms.service.admin">
            <in0>' . $parameters['certificateSubject'] . '</in0> 
            <in1>' . $parameters['caSubject'] . '</in1> 
            </deleteUser>
            </soap:Body>
            </soap:Envelope>';
    }

    //Set Options
    $options = $this->setCurlOptions(true, false, false, true, 5000);
    $options['header']  = $this->setHeaders($xml_post_string);

    HttpCurlClient::SoapHttpCurlClient($url, $xml_post_string, $this->user_cert, $this->key_cert, $error, $info, $options);
  }

  public function createRole($parameters)
  {
    $url = $this->baseUrl . '/VOMSAdmin';
    if (!empty($parameters) && isset($parameters['roleName'])) {
      // construct envelope
      $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
            <createRole xmlns="http://glite.org/wsdl/services/org.glite.security.voms.service.admin">
            <in0>' . $parameters['roleName'] . '</in0> 
            </createRole>
            </soap:Body>
            </soap:Envelope>';
    }

    //Set Options
    $options = $this->setCurlOptions(true, false, false, true, 5000);
    $options['header']  = $this->setHeaders($xml_post_string);

    HttpCurlClient::SoapHttpCurlClient($url, $xml_post_string, $this->user_cert, $this->key_cert, $error, $info, $options);
  }


  public function deleteRole($parameters)
  {
    $url = $this->baseUrl . '/VOMSAdmin';
    if (!empty($parameters) && isset($parameters['roleName'])) {
      // construct envelope
      $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
            <deleteRole xmlns="http://glite.org/wsdl/services/org.glite.security.voms.service.admin">
            <in0>' . $parameters['roleName'] . '</in0> 
            </deleteRole>
            </soap:Body>
            </soap:Envelope>';
    }

    //Set Options
    $options = $this->setCurlOptions(true, false, false, true, 5000);
    $options['header']  = $this->setHeaders($xml_post_string);

    HttpCurlClient::SoapHttpCurlClient($url, $xml_post_string, $this->user_cert, $this->key_cert, $error, $info, $options);
  }

  public function assignRole($parameters)
  {
    $url = $this->baseUrl . '/VOMSAdmin';
    if (!empty($parameters) && isset($parameters['groupName']) && isset($parameters['roleName']) && isset($parameters['certificateSubject']) && isset($parameters['caSubject'])) {
      // construct envelope
      $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
            <assignRole xmlns="http://glite.org/wsdl/services/org.glite.security.voms.service.admin">
            <in0>' . $parameters['groupName'] . '</in0> 
            <in1>' . $parameters['roleName'] . '</in1> 
            <in2>' . $parameters['certificateSubject'] . '</in2> 
            <in3>' . $parameters['caSubject'] . '</in3> 
            </assignRole>
            </soap:Body>
            </soap:Envelope>';
    }

    //Set Options
    $options = $this->setCurlOptions(true, false, false, true, 5000);
    $options['header']  = $this->setHeaders($xml_post_string);

    HttpCurlClient::SoapHttpCurlClient($url, $xml_post_string, $this->user_cert, $this->key_cert, $error, $info, $options);
  }
}
