<?php

class object_controller_authorized extends object_controller {

	/**
	 * Acl settings
	 *
	 * Authorized only
	 *
	 * @var array
	 */
	public $acl = [
		'public' => false,
		'authorized' => true,
		'permission' => false
	];
}