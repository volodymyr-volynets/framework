<?php

namespace Object\Table;
class Widgets extends \Object\Data {
	public $column_key = 'code';
	public $column_prefix = '';
	public $columns = [
		'code' => ['name' => 'Code', 'domain' => 'code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $data = [
		'attributes' => ['name' => 'Attributes'],
		'addresses' => ['name' => 'Addresses'],
		'audit' => ['name' => 'Audit'],
		'comments' => ['name' => 'Comments'],
		'documents' => ['name' => 'Documents']
	];
}