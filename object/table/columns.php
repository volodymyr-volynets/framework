<?php

class object_table_columns extends object_data {
	public $column_key = 'no_table_column_code';
	public $column_prefix = 'no_table_column_';
	public $orderby = ['no_table_column_name' => SORT_ASC];
	public $columns = [
		'no_table_column_code' => ['name' => 'Attribute', 'type' => 'varchar', 'length' => 30],
		'no_table_column_name' => ['name' => 'Name', 'type' => 'text'],
		'no_table_column_description' => ['name' => 'Description', 'type' => 'text']
	];
	public $data = [
		'name' => ['no_table_column_name' => 'Name', 'no_table_column_description' => 'Name of a column'],
		// ddl related attributes
		'domain' => ['no_table_column_name' => 'Domain', 'no_table_column_description' => 'Domain from object_type_table_domain'],
		'type' => ['no_table_column_name' => 'Data Type', 'no_table_column_description' => 'Datatype from object_type_table_column'],
		'length' => ['no_table_column_name' => 'Length', 'no_table_column_description' => 'String length'],
		'precision' => ['no_table_column_name' => 'Precision', 'no_table_column_description' => 'Numeric precision'],
		'scale' => ['no_table_column_name' => 'Scale', 'no_table_column_description' => 'Numeric scale'],
		'null' => ['no_table_column_name' => 'Null', 'no_table_column_description' => 'Whether column is null'],
		'default' => ['no_table_column_name' => 'Default', 'no_table_column_description' => 'Default value'],
		// todo: business logic attributes
		//'function' => ['name' => 'Function', 'description' => 'Value to be run though function'],
		//'empty' => ['name' => 'Empty', 'description' => 'Value can not be empty'],
		//'maxlength' => ['name' => 'Max Length', 'description' => 'Values maximum length']
	];
}