<?php

namespace Object\Data\Model;
class Inactive extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Data Inactive';
	public $column_key = 'no_data_model_inactive_id';
	public $column_prefix = 'no_data_model_inactive_';
	public $columns = [
		'no_data_model_inactive_id' => ['name' => '#', 'type' => 'smallint', 'default' => 0],
		'no_data_model_inactive_name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $options_map = [
		'no_data_model_inactive_name' => 'name'
	];
	public $data = [
		0 => ['no_data_model_inactive_name' => 'No'],
		1 => ['no_data_model_inactive_name' => 'Yes']
	];
}