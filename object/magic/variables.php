<?php

class object_magic_variables extends object_data {
	public $column_key = 'no_magic_variable_name';
	public $column_prefix = 'no_magic_variable_';
	public $columns = [
		'no_magic_variable_name' => ['name' => 'Name', 'type' => 'varchar', 'length' => 100],
		'no_magic_variable_description' => ['name' => 'Description', 'type' => 'text']
	];
	public $data = [
		'__content_type' => ['no_magic_variable_description' => 'Content Type'],
		'__skip_layout' => ['no_magic_variable_description' => 'Skip Layout']
	];
}