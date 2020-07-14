<?php
//include_once 'VomsRestClient.php';
//include_once 'enum.php';

class VomsClient
{
  private $host = null;
  private $port = null;
  private $vo_name = null;
  private $robot_cert = null;
  private $robot_key = null;
  private $rest_client = null;
  private $_config = array();

  public function __construct($host, $port, $vo_name, $robot_cert, $robot_key)
  {
    $this->host = $host;
    $this->port = $port;
    $this->vo_name = $vo_name;
    $this->robot_cert = $robot_cert;
    $this->robot_key = $robot_key;
    $this->_config = [
      $this->host,
      $this->port,
      $this->vo_name,
      $this->robot_cert,
      $this->robot_key,
    ];
  }

  /**
   * Get an instance of the HttpClient
   * @return object GuzzleHttp\Client VomsRestClient
   */
  protected function restClient() {
    if($this->rest_client === null) {
      $this->rest_client = new VomsRestClient(...$this->_config);
    }
    return $this->rest_client;
  }

  protected function checkAlive() {

  }


  /**
   * @param array $user_data   ['user' => [
                                    'emailAddress' => 'john.doe@test.com',
                                    'institution' => 'Dummy Test',
                                    'phoneNumber' => '6936936937',
                                    'surname' => 'Doe',
                                    'name' => 'John',
                                    'address' => 'No where....',
                                  ],
                                  'certificateSubject' => $subject_dn,
                                  'caSubject' => $certificate_auhority,
                               ];
   * @return array [status_code, body|msg] | null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function createUser($user_data){
    if(empty($user_data)) {
      return null;
    }
    if(empty($user_data['user'])) {
      // todo: Use the soap client
    }
    return $this->restClient()->vomsRequest(VomsRestActionsEnum::CREATE_USER, $user_data);
  }

  public function deleteUser() {

  }

  public function createRole() {

  }

  public function deleteRole() {

  }

  public function addMemberToGroup() {

  }

  public function suspendUser() {

  }

  /**
   * @return array [status_code, body|msg]
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getUserStats() {
    return $this->restClient()->vomsRequest(VomsRestActionsEnum::USER_STATS);
  }

  /**
   * @return array [status_code, body|msg]
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getSuspendedUsers() {
    return $this->restClient()->vomsRequest(VomsRestActionsEnum::GET_SUSPENDED_USERS);
  }

  /**
   * @return array [status_code, body|msg]
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getExpiredUsers() {
    return $this->restClient()->vomsRequest(VomsRestActionsEnum::GET_EXPIRED_USERS);
  }

  public function restoreUser() {

  }

  /**
   * @return array [status_code, body|msg]
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function restoreAllSuspendedUsers() {
    return $this->restClient()->vomsRequest(VomsRestActionsEnum::RESTORE_ALL_SUSPENDED_USERS);
  }
}