<?php

namespace Object\HTML\Form\Row;
class Types extends \Object\Data {
	public $column_key = 'no_html_form_row_type_code';
	public $column_prefix = 'no_html_form_row_type_';
	public $columns = [
		'no_html_form_row_type_code' => ['name' => 'Row Type', 'type' => 'varchar', 'length' => 30],
		'no_html_form_row_type_name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'grid' => ['no_html_form_row_type_name' => 'Grid'],
		'table' => ['no_html_form_row_type_name' => 'Table'],
		'details' => ['no_html_form_row_type_name' => 'Details'],
		'tabs' => ['no_html_form_row_type_name' => 'Tabs']
	];
}