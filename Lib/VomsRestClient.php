<?php
//require_once 'VomsHttp.php';

class VomsRestClient extends VomsHttp {

  /**
   * @return string Request Location
   */
  protected function getReqLocation() {
    return '/voms/' . $this->vo_name . '/apiv2';
  }

  /**
   * @return string[] Array of Http Headers
   */
  protected function constructHeaders($content = false) {
    // Create HttpHeaders
    $http_headers = ['X-VOMS-CSRF-GUARD' => ''];
    if ($content) {
      $http_headers['Content-Type'] = 'application/json; charset=utf-8';
      $http_headers['Accept'] = 'application/json';
    }

    return $http_headers;
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
      if (!empty($post_fields)) {
        $options['json'] = $post_fields;
      }
      $response = $client->request('POST', $this->getReqLocation() . '/' . $action, $options);
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
