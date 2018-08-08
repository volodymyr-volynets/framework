<?php

namespace Object\Table;
class Constraints extends \Object\Data {
	public $column_key = 'no_table_constraint_type';
	public $column_prefix = 'no_table_constraint_';
	public $orderby = [];
	public $columns = [
		'no_table_constraint_code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
		'no_table_constraint_name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'pk' => ['no_table_constraint_name' => 'Primary Key'],
		'unique' => ['no_table_constraint_name' => 'Unique'],
		'fk' => ['no_table_constraint_name' => 'Foreign Key'],
	];
}