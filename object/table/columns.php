<?php

class object_table_columns extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_table_column_';
	public $orderby = ['no_table_column_name' => SORT_ASC];
	public $columns = [
		'code' => ['name' => 'Attribute', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'description' => ['name' => 'Description', 'type' => 'text']
	];
	public $data = [
		'name' => ['name' => 'Name', 'description' => 'Name of a column'],
		// ddl related attributes
		'domain' => ['name' => 'Domain', 'description' => 'Domain from object_type_table_domain'],
		'type' => ['name' => 'Data Type', 'description' => 'Datatype from object_type_table_column'],
		'length' => ['name' => 'Length', 'description' => 'String length'],
		'precision' => ['name' => 'Precision', 'description' => 'Numeric precision'],
		'scale' => ['name' => 'Scale', 'description' => 'Numeric scale'],
		'null' => ['name' => 'Null', 'description' => 'Whether column is null'],
		'default' => ['name' => 'Default', 'description' => 'Default value'],
		// todo: business logic attributes
		//'function' => ['name' => 'Function', 'description' => 'Value to be run though function'],
		//'empty' => ['name' => 'Empty', 'description' => 'Value can not be empty'],
		//'maxlength' => ['name' => 'Max Length', 'description' => 'Values maximum length']
	];
}