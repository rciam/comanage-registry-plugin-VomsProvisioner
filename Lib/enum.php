<?php

class VomsRestActionsEnum {
  const CREATE_USER = 'create-user.action';
  const CREATE_GROUP = 'create-group.action';
  const GET_EXPIRED_USERS = 'expired-users.action';
  const GET_SUSPENDED_USERS = 'suspended-users.action';
  const USER_STATS = 'user-stats.action';
  const RESTORE_ALL_SUSPENDED_USERS = 'restore-all-suspended-users.action';
  const SUSPEND_USER = 'suspend-user.action';
  const DELETE_USER = 'deleteUser';
  const type = array(
    'create-user.action' => 'Create User',
    'create-group.action' => 'Create Group',
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

class VomsServerHttpProtocolEnum {
  const HTTP = 'http';
  const HTTPS = 'https';
  const type = array(
    'http' => 'Http',
    'https' => 'Https',
  );
}

class VomsServerImportModeEnum {
  const APPEND = 'A';
  const OVERWRITE = 'O';
  const type = array(
    'A' => 'Append',
    'O' => 'Overwrite',
  );
}

class VomsSoapServicesEnum {
  const VOMS_CERTIFICATES = 'VOMSCertificates';
  const VOMS_ADMIN = 'VOMSAdmin';
  const VOMS_ATTRIBUTES = 'VOMSAttributes';
}

class VomsSoapNamespaceEnum {
  const VOMS_ADMIN_NAMESPACE = 'http://glite.org/wsdl/services/org.glite.security.voms.service.admin';
  const VOMS_CERTIFICATES_NAMESPACE = 'http://glite.org/wsdl/services/org.glite.security.voms.service.certificates';
  const VOMS_ATTRIBUTES_NAMESPACE = 'http://glite.org/wsdl/services/org.glite.security.voms.service.attributes';
  const mapToServices = array(
    VomsSoapActionsEnum::ADD_CERTIFICATE => VomsSoapNamespaceEnum::VOMS_CERTIFICATES_NAMESPACE,
    VomsSoapActionsEnum::GET_CERTIFICATES => VomsSoapNamespaceEnum::VOMS_CERTIFICATES_NAMESPACE,
    VomsSoapActionsEnum::SUSPEND_CERTIFICATE => VomsSoapNamespaceEnum::VOMS_CERTIFICATES_NAMESPACE,
    VomsSoapActionsEnum::RESTORE_CERTIFICATE => VomsSoapNamespaceEnum::VOMS_CERTIFICATES_NAMESPACE,
    VomsSoapActionsEnum::REMOVE_CERTIFICATE => VomsSoapNamespaceEnum::VOMS_CERTIFICATES_NAMESPACE,
    VomsSoapActionsEnum::GET_USER => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::DELETE_USER => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::CREATE_USER => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::CREATE_ROLE => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::ASSIGN_ROLE => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::DISMISS_ROLE => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::CREATE_GROUP => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::DELETE_GROUP => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::ADD_MEMBER => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::REMOVE_MEMBER => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::LIST_MEMBERS => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::LIST_USER_GROUPS => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::GET_VONAME => VomsSoapNamespaceEnum::VOMS_ADMIN_NAMESPACE,
    VomsSoapActionsEnum::CREATE_ATTRIBUTE_CLASS => VomsSoapNamespaceEnum::VOMS_ATTRIBUTES_NAMESPACE,
    VomsSoapActionsEnum::DELETE_ATTRIBUTE_CLASS => VomsSoapNamespaceEnum::VOMS_ATTRIBUTES_NAMESPACE,
    VomsSoapActionsEnum::LIST_ATTRIBUTE_CLASSES => VomsSoapNamespaceEnum::VOMS_ATTRIBUTES_NAMESPACE,
    VomsSoapActionsEnum::SET_USER_ATTRIBUTE => VomsSoapNamespaceEnum::VOMS_ATTRIBUTES_NAMESPACE,
    VomsSoapActionsEnum::DELETE_USER_ATTRIBUTE => VomsSoapNamespaceEnum::VOMS_ATTRIBUTES_NAMESPACE,
    VomsSoapActionsEnum::LIST_USER_ATTRIBUTES => VomsSoapNamespaceEnum::VOMS_ATTRIBUTES_NAMESPACE
  );
}

class VomsSoapActionsEnum {
  const ADD_CERTIFICATE = 'addCertificate';
  const GET_CERTIFICATES = 'getCertificates';
  const SUSPEND_CERTIFICATE = 'suspendCertificate';
  const RESTORE_CERTIFICATE = 'restoreCertificate';
  const REMOVE_CERTIFICATE = 'removeCertificate';
  const GET_USER = 'getUser';
  const DELETE_USER = 'deleteUser';
  const CREATE_USER = 'createUser';
  const CREATE_ROLE = 'createRole';
  const ASSIGN_ROLE = 'assignRole';
  const DISMISS_ROLE = 'dismissRole';
  const CREATE_GROUP = 'createGroup';
  const DELETE_GROUP = 'deleteGroup';
  const ADD_MEMBER = 'addMember';
  const REMOVE_MEMBER = 'removeMember';
  const LIST_MEMBERS = 'listMembers';
  const LIST_USER_GROUPS = 'listGroups';
  const GET_VONAME = 'getVOName';
  const CREATE_ATTRIBUTE_CLASS = 'createAttributeClass';
  const DELETE_ATTRIBUTE_CLASS = 'deleteAttributeClass';
  const LIST_ATTRIBUTE_CLASSES = 'listAttributeClasses';
  const SET_USER_ATTRIBUTE = 'setUserAttribute';
  const DELETE_USER_ATTRIBUTE = 'deleteUserAttribute';
  const LIST_USER_ATTRIBUTES = 'listUserAttributes';
  const mapToServices = array(
    VomsSoapActionsEnum::ADD_CERTIFICATE => VomsSoapServicesEnum::VOMS_CERTIFICATES,
    VomsSoapActionsEnum::GET_CERTIFICATES => VomsSoapServicesEnum::VOMS_CERTIFICATES,
    VomsSoapActionsEnum::SUSPEND_CERTIFICATE => VomsSoapServicesEnum::VOMS_CERTIFICATES,
    VomsSoapActionsEnum::RESTORE_CERTIFICATE => VomsSoapServicesEnum::VOMS_CERTIFICATES,
    VomsSoapActionsEnum::REMOVE_CERTIFICATE => VomsSoapServicesEnum::VOMS_CERTIFICATES,
    VomsSoapActionsEnum::GET_USER => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::DELETE_USER => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::CREATE_USER => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::CREATE_ROLE => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::ASSIGN_ROLE => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::DISMISS_ROLE => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::CREATE_GROUP => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::DELETE_GROUP => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::ADD_MEMBER => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::REMOVE_MEMBER => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::LIST_MEMBERS => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::LIST_USER_GROUPS => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::GET_VONAME => VomsSoapServicesEnum::VOMS_ADMIN,
    VomsSoapActionsEnum::CREATE_ATTRIBUTE_CLASS => VomsSoapServicesEnum::VOMS_ATTRIBUTES,
    VomsSoapActionsEnum::DELETE_ATTRIBUTE_CLASS => VomsSoapServicesEnum::VOMS_ATTRIBUTES,
    VomsSoapActionsEnum::LIST_ATTRIBUTE_CLASSES => VomsSoapServicesEnum::VOMS_ATTRIBUTES,
    VomsSoapActionsEnum::SET_USER_ATTRIBUTE => VomsSoapServicesEnum::VOMS_ATTRIBUTES,
    VomsSoapActionsEnum::DELETE_USER_ATTRIBUTE => VomsSoapServicesEnum::VOMS_ATTRIBUTES,
    VomsSoapActionsEnum::LIST_USER_ATTRIBUTES => VomsSoapServicesEnum::VOMS_ATTRIBUTES
  );
}
