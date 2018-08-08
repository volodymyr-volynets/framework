<?php

namespace Object\Controller;
class Public2 extends \Object\Controller {

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