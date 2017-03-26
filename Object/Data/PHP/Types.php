<?php

namespace Object\Data\PHP;
class Types extends \Object\Data {
	public $column_key = 'code';
	public $column_prefix = null; // you must not change it !!!
	public $columns = [
		'code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $data = [
		'integer' => ['name' => 'Integer'],
		'float' => ['name' => 'Float'],
		'bcnumeric' => ['name' => 'BC Numeric'], // floats represented as strings
		'string' => ['name' => 'String'],
		'array' => ['name' => 'Array'],
		'mixed' => ['name' => 'Mixed']
	];
}