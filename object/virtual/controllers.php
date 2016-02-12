<?php

class object_virtual_controllers extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_virtual_controller_';
	public $columns = [
		'code' => ['name' => 'Controller Code', 'type' => 'varchar', 'length' => 100],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'path' => ['name' => 'Path', 'type' => 'text'],
	];
	public $data = [
		//'[code]' => ['name' => '[name]', 'path' => '[path]'],
	];
}