<?php

class object_html_form_row_type extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_html_form_row_type_';
	public $columns = [
		'code' => ['name' => 'Row Type', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'grid' => ['name' => 'Grid'],
		'table' => ['name' => 'Table'],
		'details' => ['name' => 'Details'],
		'tabs' => ['name' => 'Tabs']
	];
}