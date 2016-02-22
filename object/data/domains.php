<?php

class object_table_domains {
	// todo: refactor
	public $data = [
		'currency_code' => ['name' => 'Currency Code', 'type' => 'char', 'length' => 3, 'null' => true],
		'currency_rate' => ['name' => 'Currency Rate', 'type' => 'numeric', 'precision' => 16, 'scale' => 8, 'default' => 1],
		'html_color_code' => ['name' => 'HTML Color Code', 'type' => 'char', 'length' => 6, 'null' => true],
		'html_color_group' => ['name' => 'HTML Color Group', 'type' => 'varchar', 'length' => 30, 'null' => true],
		'acl_code' => ['name' => 'Acl Code', 'type' => 'varchar', 'length' => 30],
	];
}