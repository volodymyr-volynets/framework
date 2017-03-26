<?php

class object_type_html_button extends \Object\Data {
	public $column_key = 'no_type_html_button_code';
	public $column_prefix = 'no_type_html_button_';
	public $columns = [
		'no_type_html_button_code' => ['name' => 'Code', 'type' => 'varchar', 'length' => 30],
		'no_type_html_button_name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'default' => ['no_type_html_button_name' => 'Default'],
		'primary' => ['no_type_html_button_name' => 'Primary'],
		'success' => ['no_type_html_button_name' => 'Success'],
		'info' => ['no_type_html_button_name' => 'Info'],
		'warning' => ['no_type_html_button_name' => 'Warning'],
		'danger' => ['no_type_html_button_name' => 'Danger'],
		'link' => ['no_type_html_button_name' => 'Link'],
	];
}