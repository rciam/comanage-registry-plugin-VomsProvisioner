<?php

class VomsRestActionsEnum
{
  const CREATE_USER = 'create-user.action';
  const GET_EXPIRED_USERS = 'expired-users.action';
  const GET_SUSPENDED_USERS = 'suspended-users.action';
  const USER_STATS = 'user-stats.action';
  const RESTORE_ALL_SUSPENDED_USERS = 'restore-all-suspended-users.action';
  const DELETE_USER = 'deleteUser';
  const type = array(
    'create-user.action' => 'Create User',
    'expired-users.action' => 'Get Expired Users',
    'suspended-users.action' => 'Get Suspended Users',
    'user-stats.action' => 'Get User Stats',
    'restore-all-suspended-users.action' => 'Restore all suspended users',
    'deleteUser' => 'Delete User',
  );
}

class VomsClientEnum {
  const API_ENPOINT = 'apiv2';
  const REST_LOCATION = 'voms';
  const CSRF_GUARD = 'X-voms-csrf-guard';
}

class VomsServerConfigEnum {
  const BULK = 'B';
  const SINGLE = 'S';
  const type = array(
    'B' => 'Bulk',
    'S' => 'Single',
  );
}
