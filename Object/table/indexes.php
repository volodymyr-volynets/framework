<?php

class object_table_indexes extends object_data {
	public $column_key = 'no_table_index_type';
	public $column_prefix = 'no_table_index_';
	public $orderby = [];
	public $columns = [
		'no_table_index_code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
		'no_table_index_name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'btree' => ['no_table_index_name' => 'Btree'],
		'fulltext' => ['no_table_index_name' => 'Full Text']
	];
}