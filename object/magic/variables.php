<?php

class object_magic_variables extends object_data {
	public $column_key = 'name';
	public $column_prefix = 'no_magic_variable_';
	public $columns = [
		'name' => ['name' => 'Name', 'type' => 'varchar', 'length' => 100],
		'description' => ['name' => 'Description', 'type' => 'text']
	];
	public $data = [
		'__content_type' => ['description' => 'Content Type'],
		'__skip_layout' => ['description' => 'Skip Layout']
	];
}