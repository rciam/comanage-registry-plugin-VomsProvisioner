<?php
//require_once 'VomsHttp.php';

class VomsSoapClient extends VomsHttp {

  /**
   *
   * @return string Request Location
   */
  protected function getReqLocation() {
    return '/voms/' . $this->vo_name . '/services';
  }

  /**
   * @param boolean $content whether you have a content or not
   * @return string[] Array of Http Headers
   */
  protected function constructHeaders($content = false) {
    // Create HttpHeaders
    $http_headers = [
      'X-VOMS-CSRF-GUARD' => '',
    ];
    if($content) {
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
   * @param mixed $action
   * @param mixed $parameters
   * @return void
   */
  private function constructEnvelope($action, $parameters) {
    $soapEnvelope = new SimpleXMLElement('<soap:Envelope/>', LIBXML_NOERROR, false, 'soap', true);
    $soapEnvelopeBody = $soapEnvelope->addChild('soap:soap:Body');
    $soapEnvelopeAction = $soapEnvelopeBody->addChild($action);
    $soapEnvelopeAction->addAttribute('xmlns', VomsSoapNamespaceEnum::mapToServices[$action]);
    $soapEnvelope->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $soapEnvelope->addAttribute('xmlns:xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
    $soapEnvelope->addAttribute('xmlns:xmlns:soap', 'http://schemas.xmlsoap.org/soap/envelope/');
    $fname = $action . '_payload';
    try {
      $this->$fname($soapEnvelopeAction, $parameters);
    } catch(Exception $e) {
      $this->Flash->set($e->getMessage(), array('key' => 'error'));
    }
    return $soapEnvelope->asXML();
  }

  /**
   * deleteUser_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function deleteUser_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in1', $parameters['caSubject']);
  }

  /**
   * getUser_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function getUser_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in1', $parameters['caSubject']);
  }

  /**
   * createUser_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function createUser_payload(&$soapEnvelopeAction, $parameters) {
    $in0 = $soapEnvelopeAction->addChild('in0');
    $dnNode = $in0->addChild('DN', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $in0->addChild('CA', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $cnNode = $in0->addChild('CN', empty($parameters['cn']) ? null : $parameters['cn']);
    $cnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
  }

  /**
   * createRole_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function createRole_payload(&$soapEnvelopeAction, $parameters) {
    $in0 = $soapEnvelopeAction->addChild('role_name', 'Role=' . $parameters['role']);
  }

  /**
   * assignRole_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function assignRole_payload(&$soapEnvelopeAction, $parameters) {
    $in0 = $soapEnvelopeAction->addChild('in0', $parameters['group']);
    $soapEnvelopeAction->addChild('in1', 'Role=' . $parameters['role']);
    $in2 = $soapEnvelopeAction->addChild('in2', $parameters['certificateSubject']);
    $in2->addAttribute('xsi:xsi:type', 'soapenc:string');
    $in3 = $soapEnvelopeAction->addChild('in3', $parameters['caSubject']);
    $in3->addAttribute('xsi:xsi:type', 'soapenc:string');
  }

  /**
   * dismissRole_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function dismissRole_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['group']);
    $soapEnvelopeAction->addChild('in1', 'Role=' . $parameters['role']);
    $soapEnvelopeAction->addChild('in2', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in3', $parameters['caSubject']);
  }


  /**
   * getCertificates_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function getCertificates_payload(&$soapEnvelopeAction, $parameters) {
    $dnNode = $soapEnvelopeAction->addChild('subject', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $soapEnvelopeAction->addChild('issuer', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
  }

  /**
   * addCertificate_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function addCertificate_payload(&$soapEnvelopeAction, $parameters) {
    $dnNode = $soapEnvelopeAction->addChild('RegisteredCertSubject', $parameters['regCertificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $soapEnvelopeAction->addChild('RegisteredCertIssuer', $parameters['regCaSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $cert = $soapEnvelopeAction->addChild('Cert');
    $dnNode = $cert->addChild('subject', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $cert->addChild('issuer', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
  }

  /**
   * suspendCertificate_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function suspendCertificate_payload(&$soapEnvelopeAction, $parameters) {
    $cert = $soapEnvelopeAction->addChild('Cert');
    $dnNode = $cert->addChild('subject', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $cert->addChild('issuer', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $soapEnvelopeAction->addChild('Reason', $parameters['reason']);
  }

  /**
   * removeCertificate_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function removeCertificate_payload(&$soapEnvelopeAction, $parameters) {
    $cert = $soapEnvelopeAction->addChild('Cert');
    $dnNode = $cert->addChild('subject', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $cert->addChild('issuer', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
  }

  /**
   * restoreCertificate_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function restoreCertificate_payload(&$soapEnvelopeAction, $parameters) {
    $cert = $soapEnvelopeAction->addChild('Cert');
    $dnNode = $cert->addChild('subject', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $cert->addChild('issuer', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
  }

  /**
   * createAttributeClass_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function createAttributeClass_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('name', $parameters['name']);
    $soapEnvelopeAction->addChild('description', $parameters['description']);
    $soapEnvelopeAction->addChild('unique', $parameters['unique']);
  }

  /**
   * listAttributeClasses_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function listAttributeClasses_payload(&$soapEnvelopeAction, $parameters) {
  }

  /**
   * deleteAttributeClass_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function deleteAttributeClass_payload(&$soapEnvelopeAction, $parameters) {
    $attrClass = $soapEnvelopeAction->addChild('attributeClass');
    $attrClass->addChild('name', $parameters['name']);
  }

  /**
   * setUserAttribute_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function setUserAttribute_payload(&$soapEnvelopeAction, $parameters) {
    $in0 = $soapEnvelopeAction->addChild('in0');
    $dnNode = $in0->addChild('DN', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $in0->addChild('CA', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $in1 = $soapEnvelopeAction->addChild('in1');
    $attrClass = $in1->addChild('attributeClass');
    $attrClass->addChild('name', $parameters['name']);
    $in1->addChild('value', $parameters['value']);
  }

  /**
   * deleteUserAttribute_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function deleteUserAttribute_payload(&$soapEnvelopeAction, $parameters) {
    $in0 = $soapEnvelopeAction->addChild('user');
    $dnNode = $in0->addChild('DN', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $in0->addChild('CA', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $soapEnvelopeAction->addChild('name', $parameters['name']);
  }

  /**
   * listUserAttributes_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function listUserAttributes_payload(&$soapEnvelopeAction, $parameters) {
    $in0 = $soapEnvelopeAction->addChild('in0');
    $dnNode = $in0->addChild('DN', $parameters['certificateSubject']);
    $dnNode->addAttribute('xsi:xsi:type', 'soapenc:string');
    $caNode = $in0->addChild('CA', $parameters['caSubject']);
    $caNode->addAttribute('xsi:xsi:type', 'soapenc:string');
  }


  /**
   * createGroup_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function createGroup_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0');
    $soapEnvelopeAction->addChild('in1', $parameters['groupName']);
  }

  /**
   * deleteGroup_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function deleteGroup_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['groupName']);
  }

  /**
   * addMember_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function addMember_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['groupName']);
    $soapEnvelopeAction->addChild('in1', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in2', $parameters['caSubject']);
  }

  /**
   * removeMember_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function removeMember_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['groupName']);
    $soapEnvelopeAction->addChild('in1', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in2', $parameters['caSubject']);
  }

  /**
   * listMembers_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function listMembers_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['groupName']);
  }

  /**
   * listGroups_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function listGroups_payload(&$soapEnvelopeAction, $parameters) {
    $soapEnvelopeAction->addChild('in0', $parameters['certificateSubject']);
    $soapEnvelopeAction->addChild('in1', $parameters['caSubject']);
  }

  /**
   * getVOName_payload
   *
   * @param mixed $soapEnvelopeAction
   * @param mixed $parameters
   * @return void
   */
  private function getVOName_payload(&$soapEnvelopeAction, $parameters) {
  }


  /**
   * @param $action
   * @param array $post_fields
   * @param boolean $debug
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function vomsRequest($action, $post_fields = array(), $debug = false) {
    try {
      $client = $this->httpClient();
      $options = [
        'debug' => $debug,
        'headers' => $this->constructHeaders(!empty($post_fields)),
      ];
      if(!empty($post_fields)) {
        $options['body'] = $this->constructEnvelope($action, $post_fields);
      }
      $response = $client->request(
        'POST',
        $this->getReqLocation() . '/' . VomsSoapActionsEnum::mapToServices[$action],
        $options
      );
      return [
        'status_code' => $response->getStatusCode(),
        'msg' => $response->getReasonPhrase(),
      ];
    } catch(Exception $e) {
      return [
        'status_code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    }
  }
}
