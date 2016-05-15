<?php

class object_data_model_inactive extends object_data {
	public $column_key = 'no_data_model_inactive_id';
	public $column_prefix = null; // you must not change it !!!
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