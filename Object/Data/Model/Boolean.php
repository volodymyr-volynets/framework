<?php

namespace Object\Data\Model;
class Boolean extends \Object\Data {
	public $column_key = 'no_data_model_boolean_id';
	public $column_prefix = 'no_data_model_boolean_';
	public $columns = [
		'no_data_model_boolean_id' => ['name' => '#', 'type' => 'smallint'],
		'no_data_model_boolean_name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $options_map = [
		'no_data_model_boolean_name' => 'name'
	];
	public $data = [
		0 => ['no_data_model_boolean_name' => 'False'],
		1 => ['no_data_model_boolean_name' => 'True']
	];
}