<?php

class object_controller_public extends object_controller {

	/**
	 * Acl settings
	 *
	 * Public only
	 *
	 * @var array
	 */
	public $acl = [
		'public' => true,
		'authorized' => false,
		'permission' => false
	];
}