<?php
//include_once 'VomsRestClient.php';
//include_once 'VomsSoapClient.php';
//include_once 'enum.php';

class VomsClient {
  private $host = null;
  private $port = null;
  private $vo_name = null;
  private $robot_cert = null;
  private $robot_key = null;
  private $rest_client = null;
  private $soap_client = null;
  private $_config = array();

  public function __construct($host, $port, $vo_name, $robot_cert, $robot_key) {
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
  private function restClient() {
    if($this->rest_client === null) {
      $this->rest_client = new VomsRestClient(...$this->_config);
    }
    return $this->rest_client;
  }

  /**
   * Get an instance of the HttpClient
   * @return object GuzzleHttp\Client VomsSoaptClient
   */
  private function soapClient() {
    if($this->soap_client === null) {
      $this->soap_client = new VomsSoapClient(...$this->_config);
    }
    return $this->soap_client;
  }

  protected function checkAlive() {
  }


  /**
   * @param array $user_data ['user' => [
   * 'emailAddress' => 'john.doe@test.com',
   * 'institution' => 'Dummy Test',
   * 'phoneNumber' => '6936936937',
   * 'surname' => 'Doe',
   * 'name' => 'John',
   * 'address' => 'No where....',
   * ],
   * 'certificateSubject' => $subject_dn,
   * 'caSubject' => $certificate_auhority,
   * ];
   * @return array [status_code, body|msg] | null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function createUser($user_data) {
    if(empty($user_data)) {
      return null;
    }
    if(empty($user_data['user'])) {
      // todo: Use the soap client
    }
    return $this->restClient()->vomsRequest(VomsRestActionsEnum::CREATE_USER, $user_data);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function deleteUser($dn, $ca) {
    if(empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::DELETE_USER, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getUser($dn, $ca) {
    if(empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::GET_USER, $post_fields, false);
  }

  /**
   * @param string $roleName to be created
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function createRole($roleName) {
    if(empty($roleName)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'role' => $roleName,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::CREATE_ROLE, $post_fields, false);
  }

  /**
   * @param string $groupName Group Name user is member (e.g /checkin-integration)
   * @param string $roleName Role Name to be assigned
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function assignRole($groupName, $roleName, $dn, $ca) {
    if(empty($groupName) || empty($roleName) || empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'group' => $groupName,
      'role' => $roleName,
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::ASSIGN_ROLE, $post_fields, false);
  }

  /**
   * @param string $groupName Group Name user is member (e.g /checkin-integration)
   * @param string $roleName Role Name to be dismissed
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function dismissRole($groupName, $roleName, $dn, $ca) {
    if(empty($groupName) || empty($roleName) || empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'group' => $groupName,
      'role' => $roleName,
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::DISMISS_ROLE, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCertificates($dn, $ca) {
    if(empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::GET_CERTIFICATES, $post_fields, false);
  }

  /**
   * @param string $regdn User's Subject DN from Registered Certificate
   * @param string $regca User's CA for Registered Certificate
   * @param string $dn User's Subject DN from Certificate to be added
   * @param string $ca User's CA for Certificate to be added
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addCertificate($regdn, $regca, $dn, $ca) {
    if(empty($regdn) || empty($regca) || empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'regCertificateSubject' => $regdn,
      'regCaSubject' => $regca,
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::ADD_CERTIFICATE, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @param string $reason Reason for suspension
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function suspendCertificate($dn, $ca, $reason) {
    if(empty($reason) || empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
      'reason' => $reason,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::SUSPEND_CERTIFICATE, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function removeCertificate($dn, $ca) {
    if(empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::REMOVE_CERTIFICATE, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function restoreCertificate($dn, $ca) {
    if(empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::RESTORE_CERTIFICATE, $post_fields, false);
  }

  /**
   * @param string $name
   * @param string $description
   * @param boolean $unique (Boolean 0|1)
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function createAttributeClass($name, $description, $unique) {
    if(empty($name) || empty($description) || empty($unique)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'name' => $name,
      'description' => $description,
      'unique' => $unique,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::CREATE_ATTRIBUTE_CLASS, $post_fields, false);
  }

  /**
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function listAttributeClass() {
    $post_fields = [];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::LIST_ATTRIBUTE_CLASSES, $post_fields, false);
  }

  /**
   * @param $name AttributeClass Name to be deleted
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function deleteAttributeClass($name) {
    if(empty($name)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }

    $post_fields = [
      'name' => $name,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::DELETE_ATTRIBUTE_CLASS, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @param string $name AttributeClass Name
   * @param string $value AttributeClass Value to be added/ updated
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function setUserAttribute($dn, $ca, $name, $value) {
    if(empty($dn) || empty($ca) || empty($name) || empty($value)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
      'name' => $name,
      'value' => $value,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::SET_USER_ATTRIBUTE, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @param string $name AttributeClass Name to be deleted from User
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function deleteUserAttribute($dn, $ca, $name) {
    if(empty($dn) || empty($ca) || empty($name)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
      'name' => $name,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::DELETE_USER_ATTRIBUTE, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function listUserAttributes($dn, $ca) {
    if(empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::LIST_ATTRIBUTE_CLASSES, $post_fields, false);
  }

  /**
   * @param string $groupName must have the format e.g "/checkin-integration/testGroup"
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function createGroup($groupName) {
    if(empty($groupName)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'groupName' => $groupName,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::CREATE_GROUP, $post_fields, false);
  }

  /**
   * @param string $groupName Must have the format e.g "/checkin-integration/testGroup"
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function deleteGroup($groupName) {
    if(empty($groupName)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'groupName' => $groupName,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::DELETE_GROUP, $post_fields, false);
  }

  /**
   * @param string $groupName Must have the format e.g "/checkin-integration/testGroup"
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addMember($groupName, $dn, $ca) {
    if(empty($groupName) || empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'groupName' => $groupName,
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::ADD_MEMBER, $post_fields, false);
  }

  /**
   * @param string $groupName Must have the format e.g "/checkin-integration/testGroup"
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function removeMember($groupName, $dn, $ca) {
    if(empty($groupName) || empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'groupName' => $groupName,
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::REMOVE_MEMBER, $post_fields, false);
  }

  /**
   * @param string $groupName Must have the format e.g "/checkin-integration/testGroup"
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function listMembers($groupName) {
    if(empty($groupName)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'groupName' => $groupName,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::LIST_MEMBERS, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function listUserGroups($dn, $ca) {
    if(empty($dn) || empty($ca)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
    ];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::LIST_USER_GROUPS, $post_fields, false);
  }

  /**
   * @return array Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getVOName() {
    $post_fields = [];
    return $this->soapClient()->vomsRequest(VomsSoapActionsEnum::GET_VONAME, $post_fields, false);
  }

  /**
   * @param string $dn User's Subject DN from Certificate
   * @param string $ca User's CA for Certificate
   * @param string $reason Reason for user suspension
   * @return void
   */
  public function suspendUser($dn, $ca, $reason) {
    if(empty($dn) || empty($ca) || empty($reason)) {
      throw new NotFoundException(_txt('op.voms_provisioner.nocert'));
    }
    $post_fields = [
      'certificateSubject' => $dn,
      'caSubject' => $ca,
      'suspensionReason' => 'Reason=' . $reason,
    ];
    return $this->restClient()->vomsRequest(VomsRestActionsEnum::SUSPEND_USER, $post_fields);
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
