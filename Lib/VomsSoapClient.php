<?php
//require_once 'VomsHttp.php';

class VomsSoapClient extends VomsHttp{

  /**
   * 
   * @return string Request Location
   */
  protected function getReqLocation()
  {
    return '/voms/' . $this->vo_name . '/services';
  }

  /**
   * @param bool $json_content
   * @param boolean $content whether you have a content or not
   * @return string[] Array of Http Headers
   */
  protected function constructHeaders($content = false)
  {
    // Create HttpHeaders
    $http_headers = [
      'X-VOMS-CSRF-GUARD' => '',
    ];
    if ($content) {
      // Create HttpHeaders
      $http_headers['Content-Type'] = 'text/xml;charset="utf-8"';
      $http_headers['Accept'] = 'text/xml';
      $http_headers['Cache-Control'] = 'no-cache';
      $http_headers['Pragma'] = 'no-cache';
      $http_headers['SOAPAction'] = '\'\'';
    }
    return $http_headers;
  }

  /**
   * @param $action
   * @param array $post_fields
   * @param boolean $debug
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function vomsRequest($action, $post_fields = array(), $debug = false)
  {
    try {
      $client = $this->httpClient();
      $options = [
        'debug' => $debug,
        'headers' => $this->constructHeaders(!empty($post_fields)),
      ];
      if (!empty($post_fields)) {

        $options['body'] = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Body>
        <' . $action . ' xmlns="http://glite.org/wsdl/services/org.glite.security.voms.service.admin">
        <in0>' . $post_fields['certificateSubject'] . '</in0> 
        <in1>' . $post_fields['caSubject'] . '</in1> 
        </' . $action . '>
        </soap:Body>
        </soap:Envelope>';
      }

      $response = $client->request('POST', $this->getReqLocation() . '/' . 'VOMSAdmin', $options);
      return [
        'status_code' => $response->getStatusCode(),
        'body' => $response->getBody()->getContents(),
      ];
    } catch (Exception $e) {
      $response = $e->getResponse();

      return [
        'status_code' => $response->getStatusCode(),
        'msg' => $response->getReasonPhrase(),
      ];
    }
  }
}
