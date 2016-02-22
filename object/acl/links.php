<?php

class object_acl_links extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_object_acl_link_';
	public $columns = [
		'code' => ['name' => 'Link Code', 'domain' => 'acl_code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'inactive' => ['name' => 'Inactive', 'type' => 'boolean']  // important we must inactivate and not delete
	];
	public $data = [
		//'[code]' => ['name' => '[name]', 'inactive' => '[inactive]'],
		'general' => ['name' => 'General']
	];
}