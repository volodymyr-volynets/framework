<?php

class object_data_model_order extends object_data {
	public $column_key = 'no_data_model_order_id';
	public $column_prefix = null; // you must not change it !!!
	public $columns = [
		'no_data_model_order_id' => ['name' => '#', 'type' => 'smallint', 'default' => 0],
		'no_data_model_order_name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $options_map = [
		'no_data_model_order_name' => 'name'
	];
	public $data = [
		SORT_ASC => ['no_data_model_order_name' => 'Ascending'],
		SORT_DESC => ['no_data_model_order_name' => 'Descending']
	];
}