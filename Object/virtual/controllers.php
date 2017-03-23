<?php

class object_virtual_controllers extends object_data {
	public $column_key = 'no_virtual_controller_code';
	public $column_prefix = 'no_virtual_controller_';
	public $columns = [
		'no_virtual_controller_code' => ['name' => 'Controller Code', 'type' => 'varchar', 'length' => 100],
		'no_virtual_controller_name' => ['name' => 'Name', 'type' => 'text'],
		// full controller path, for example /numbers/backend/misc/tinyurl/db/controller/tinyurl
		'no_virtual_controller_path' => ['name' => 'Path', 'type' => 'text'],
	];
	public $data = [
		//'[code]' => ['name' => '[name]', 'path' => '[path]'],
	];
}