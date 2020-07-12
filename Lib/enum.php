<?php

class VomsRestActionsEnum
{
  const CREATE_USER = 'create-user.action';
  const GET_EXPIRED_USERS = 'expired-users.action';
  const GET_SUSPENDED_USERS = 'suspended-users.action';
  const USER_STATS = 'user-stats.action';
  const type = array(
    'create-user.action' => 'Create User',
    'expired-users.action' => 'Get Expired Users',
    'suspended-users.action' => 'Get Suspended Users',
    'user-stats.action' => 'Get User Stats',
  );
}

class VomsClientEnum {
  const API_ENPOINT = 'apiv2';
  const REST_LOCATION = 'voms';
}
