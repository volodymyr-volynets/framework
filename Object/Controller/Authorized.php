<?php

namespace Object\Controller;
class Authorized extends \Object\Controller {

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