<?php

class object_data_types extends object_data {
	public $column_key = 'code';
	public $column_prefix = null; // you must not change it !!!
	public $columns = [
		'code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'default' => ['name' => 'Default', 'type' => 'mixed'],
		'php_type' => ['name' => 'PHP Type', 'type' => 'text']
	];
	public $data = [
		'boolean' => ['name' => 'Boolean', 'default' => 0, 'php_type' => 'integer'],
		// numeric types
		'smallint' => ['name' => 'Small Integer', 'default' => 0, 'php_type' => 'integer'],
		'integer' => ['name' => 'Integer', 'default' => 0, 'php_type' => 'integer'],
		'bigint' => ['name' => 'Big Integer', 'default' => 0, 'php_type' => 'integer'],
		'numeric' => ['name' => 'Numeric', 'default' => 0, 'php_type' => 'float'],
		// numbers with sequences, will be converted to autoincrement for some databases
		'smallserial' => ['name' => 'Serial Smallint', 'php_type' => 'integer'],
		'serial' => ['name' => 'Serial Integer', 'php_type' => 'integer'],
		'bigserial' => ['name' => 'Big Serial', 'php_type' => 'integer'],
		// text data types
		'char' => ['name' => 'Character', 'php_type' => 'string'],
		'varchar' => ['name' => 'Character Varying', 'php_type' => 'string'],
		'text' => ['name' => 'Text', 'php_type' => 'string'],
		// json types
		'json' => ['name' => 'JSON', 'php_type' => 'array'],
		// date types
		'date' => ['name' => 'Date', 'php_type' => 'string'],
		'time' => ['name' => 'Time', 'php_type' => 'string'],
		'datetime' => ['name' => 'Date & time', 'php_type' => 'string'],
		'timestamp' => ['name' => 'Timestamp', 'php_type' => 'string'],
		// mixed data type
		'mixed' => ['name' => 'Mixed', 'php_type' => 'mixed']
	];
}