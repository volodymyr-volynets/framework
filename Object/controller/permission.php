<?php

class object_controller_permission extends object_controller {

	/**
	 * Acl settings
	 *
	 * Permissions only
	 *
	 * @var array
	 */
	public $acl = [
		'public' => false,
		'authorized' => false,
		'permission' => true
	];
}