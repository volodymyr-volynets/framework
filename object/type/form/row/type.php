<?php

class object_type_form_row_type extends object_data {

	/**
	 * A list of row types
	 *
	 * @var array
	 */
	public $data = [
		'grid' => ['name' => 'Grid'],
		'table' => ['name' => 'Table'],
		'details' => ['name' => 'Details'],
		'tabs' => ['name' => 'Tabs']
	];
}