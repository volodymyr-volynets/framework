<?php

namespace Object\Data\Model;
class Order extends \Object\Data {
	public $column_key = 'no_data_model_order_id';
	public $column_prefix = 'no_data_model_order_';
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