<?php
//require_once 'VomsHttp.php';

class VomsSoapClient extends VomsHttp
{

  /**
   * 
   * @return string Request Location
   */
  protected function getReqLocation()
  {
    return '/voms/' . $this->vo_name . '/services';
  }

  /**
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
   * constructEnvelope
   *
   * @param  mixed $action
   * @param  mixed $parameters
   * @return void
   */
  private function constructEnvelope($action, $parameters)
  {
    $soapEnvelope = new SimpleXMLElement('<soap:Envelope/>', LIBXML_NOERROR, false, 'soap', true);
    $soapEnvelopeBody = $soapEnvelope->addChild('soap:soap:Body');
    $soapEnvelopeAction = $soapEnvelopeBody->addChild($action);
    $soapEnvelopeAction->addAttribute('xmlns', 'http://glite.org/wsdl/services/org.glite.security.voms.service.admin');
    $soapEnvelope->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $soapEnvelope->addAttribute('xmlns:xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
    $soapEnvelope->addAttribute('xmlns:xmlns:soap', 'http://schemas.xmlsoap.org/soap/envelope/');
    $fname = $action . '_payload';
    try {
      $this->$fname($soapEnvelopeAction, $parameters);
    } catch (Exception $e) {
      $this->Flash->set($e->getMessage(), array('key' => 'error'));
    }
    return $soapEnvelope->asXML();
  }

  /**
   * deleteUser_payload
   *
   * @param  mixed $soapEnvelopeAction
   * @param  mixed $parameters
   * @return void
   */
  private function deleteUser_payload(&$soapEnvelopeAction, $parameters)
  {
    $soapEnvelopeAction->addChild('in0', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in1', $parameters['caSubject']);
  }

  /**
   * getUser_payload
   *
   * @param  mixed $soapEnvelopeAction
   * @param  mixed $parameters
   * @return void
   */
  private function getUser_payload(&$soapEnvelopeAction, $parameters)
  {
    $soapEnvelopeAction->addChild('in0', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in1', $parameters['caSubject']);
  }

  /**
   * createUser_payload
   *
   * @param  mixed $soapEnvelopeAction
   * @param  mixed $parameters
   * @return void
   */
  private function createUser_payload(&$soapEnvelopeAction, $parameters)
  {
    $in0 = $soapEnvelopeAction->addChild('in0');
    $dnNode = $in0->addChild('DN', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $in0->addChild('CA', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $cnNode = $in0->addChild('CN', empty($parameters['cn']) ? NULL : $parameters['cn']);
    $cnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
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
        $options['body'] = $this->constructEnvelope($action, $post_fields);
      }
      $response = $client->request('POST', $this->getReqLocation() . '/' . 'VOMSAdmin', $options);
      return [
        'status_code' => $response->getStatusCode(),
        'msg' => $response->getReasonPhrase(),
      ];
    } catch (Exception $e) {
      return [
        'status_code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    }
  }
}
