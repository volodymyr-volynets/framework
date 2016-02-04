<?php

class object_type_table_domain {

	/**
	 * Domains
	 *
	 * @var array
	 */
	public $data = [
		'currency_code' => ['name' => 'Currency Code', 'type' => 'char', 'length' => 3, 'null' => true],
		'currency_rate' => ['name' => 'Currency Rate', 'type' => 'numeric', 'precision' => 16, 'scale' => 8, 'default' => 1],
	];
}