<?php

class object_type_html_button extends object_data {

	/**
	 * A list of button types
	 *
	 * @var array
	 */
	public $data = [
		'default' => ['name' => 'Default'],
		'primary' => ['name' => 'Primary'],
		'success' => ['name' => 'Success'],
		'info' => ['name' => 'Info'],
		'warning' => ['name' => 'Warning'],
		'danger' => ['name' => 'Danger'],
		'link' => ['name' => 'Link'],
	];
}