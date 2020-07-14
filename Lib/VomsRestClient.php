<?php
require_once 'VomsHttp.php';

class VomsRestClient extends VomsHttp {

  /**
   * @return string Request Location
   */
  protected function getReqLocation() {
    return '/voms/' . $this->vo_name . '/apiv2';
  }

  /**
   * @param $action
   * @param array $post_fields
   * @param boolean $debug
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function vomsRestRequest($action, $post_fields=array(), $debug=false) {
    try{
      $client = $this->httpClient();
      $options = [
        'debug' => $debug,
        'headers' => $this->constructHeaders(!empty($post_fields)),
      ];
      if(!empty($post_fields)) {
        $options['json'] = $post_fields;
      }
      $response = $client->request('POST', $this->getReqLocation() . '/' . $action, $options);
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
