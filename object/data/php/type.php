<?php

class object_data_php_type {
	public $column_key = 'code';
	public $column_prefix = null; // you must not change it !!!
	public $columns = [
		'code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $data = [
		'integer' => ['name' => 'Integer'],
		'float' => ['name' => 'Float'],
		'string' => ['name' => 'String'],
		'array' => ['name' => 'array'],
		'mixed' => ['name' => 'Mixed']
	];
}