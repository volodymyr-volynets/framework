<?php

class object_controller {

	/**
	 * Controller's title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Icon
	 *
	 * @var string 
	 */
	public $icon;

	/**
	 * Acl settings
	 *
	 * @var array
	 */
	public $acl = [
		'public' => 1
		//'authorized' => 1,
		//'permission' => 1
		//'tokens' => ['token1', 'token2'],
	];

	/**
	 * Breadcrumbs
	 *
	 * @var string 
	 */
	public $breadcrumbs = [];
}