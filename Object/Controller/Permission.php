<?php

namespace Object\Controller;
class Permission extends \Object\Controller {

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