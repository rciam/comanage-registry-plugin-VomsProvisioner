<?php

class VomsClient
{
  private $host = null;
  private $port = null;
  private $vo_name = null;
  private $robot_cert = null;
  private $robot_key = null;
  private $rest_client = null;

  public function __construct($host, $port, $vo_name, $robot_cert, $robot_key)
  {
    $this->host = $host;
    $this->port = $port;
    $this->vo_name = $vo_name;
    $this->robot_cert = $robot_cert;
    $this->robot_key = $robot_key;
  }

  protected function restClient() {
    if($this->rest_client === null) {
      $this->rest_client = new VomsRestClient();
    }
  }

  protected function checkAlive() {

  }


  public function createUser()
  {

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

  public function getUserStats() {

  }

  public function getSuspendedUsers() {

  }

  public function restoreUser() {

  }

  public function restoreAllSuspendedUsers() {

  }
}