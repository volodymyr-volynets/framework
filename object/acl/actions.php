<?php

class object_acl_actions extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_object_acl_action_';
	public $columns = [
		'code' => ['name' => 'Action Code', 'domain' => 'acl_code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'inactive' => ['name' => 'Inactive', 'type' => 'boolean'] // important we must inactivate and not delete
	];
	public $data = [
		// general
		'view' => ['name' => 'View'],
		'new' => ['name' => 'New'],
		'edit' => ['name' => 'Edit'],
		'inactivate' => ['name' => 'Inactivate'],
		'delete' => ['name' => 'Delete'],
		// login related, todo: move them to login module
		'login_freeze_account' => ['name' => 'Login Freeze Account'],
		'login_set_password' => ['name' => 'Login Set Password'],
		'login_reset_password' => ['name' => 'Login Reset Password']
	];
}