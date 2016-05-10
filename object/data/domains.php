<?php

class object_data_domains extends object_data {
	public $column_key = 'code';
	public $column_prefix = null; // you must not change it !!!
	public $orderby = ['name' => SORT_ASC];
	public $columns = [
		'code' => ['name' => 'Code', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'type' => ['name' => 'Type', 'type' => 'text'],
		'default' => ['name' => 'Default', 'type' => 'mixed'],
		'length' => ['name' => 'Length', 'type' => 'smallint'],
		'null' => ['name' => 'Null', 'type' => 'boolean', 'default' => 0],
		'precision' => ['name' => 'Precision', 'type' => 'smallint'],
		'scale' => ['name' => 'Scale', 'type' => 'smallint']
	];
	// todo: refactor
	public $data = [
		// general
		'name' => ['name' => 'Name', 'type' => 'varchar', 'length' => 100],
		'code' => ['name' => 'Code', 'type' => 'varchar', 'length' => 255],
		'type_id' => ['name' => 'Type #', 'type' => 'smallint'],
		'group_id' => ['name' => 'Group #', 'type' => 'integer'],
		'order' => ['name' => 'Order', 'type' => 'integer', 'default' => 0],
		// system
		'controller_id' => ['name' => 'Controller #', 'type' => 'integer'],
		'controller_id_sequence' => ['name' => 'Controller #', 'type' => 'serial'],
		'action_id' => ['name' => 'Action #', 'type' => 'smallint'],
		// accounting
		'currency_code' => ['name' => 'Currency Code', 'type' => 'char', 'length' => 3, 'null' => true],
		'currency_rate' => ['name' => 'Currency Rate', 'type' => 'numeric', 'precision' => 16, 'scale' => 8, 'default' => 1],
		'html_color_code' => ['name' => 'HTML Color Code', 'type' => 'char', 'length' => 6, 'null' => true],
		'html_color_group' => ['name' => 'HTML Color Group', 'type' => 'varchar', 'length' => 30, 'null' => true]
	];
}