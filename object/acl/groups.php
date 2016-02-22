<?php

class object_acl_groups extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_object_acl_group_';
	public $columns = [
		'code' => ['name' => 'Group Code', 'domain' => 'acl_code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'inactive' => ['name' => 'Inactive', 'type' => 'boolean']  // important we must inactivate and not delete
	];
	public $data = [
		//'[code]' => ['name' => '[name]', 'inactive' => '[inactive]'],
		'entities' => ['name' => 'Entities'],
		'admin' => ['name' => 'Admin']
	];
}