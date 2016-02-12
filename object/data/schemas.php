<?php

class object_data_schemas extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_data_schema_';
	public $orderby = ['no_data_schema_name' => SORT_ASC];
	public $columns = [
		'code' => ['name' => 'Schema Code', 'type' => 'char', 'length' => 2],
		'name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'sm' => ['name' => 'System'],
		'no' => ['name' => 'Numbers Objects'],
	];
}