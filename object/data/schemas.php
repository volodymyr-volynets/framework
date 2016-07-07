<?php

class object_data_schemas extends object_data {
	public $column_key = 'no_data_schema_code';
	public $column_prefix = 'no_data_schema_';
	public $orderby = ['no_data_schema_name' => SORT_ASC];
	public $columns = [
		'no_data_schema_code' => ['name' => 'Schema Code', 'type' => 'char', 'length' => 2],
		'no_data_schema_name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'sm' => ['no_data_schema_name' => 'System (Common)'],
		'sc' => ['no_data_schema_name' => 'System (Cron)'],
		'no' => ['no_data_schema_name' => 'Numbers Objects'],
		'em' => ['no_data_schema_name' => 'Entity Management'],
		'lc' => ['no_data_schema_name' => 'Localization'],
		'of' => ['no_data_schema_name' => 'Optional Fields'],
		'dc' => ['no_data_schema_name' => 'Documents'],
		'ms' => ['no_data_schema_name' => 'Miscellaneous']
	];
}