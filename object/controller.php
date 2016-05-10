<?php

class object_controller {

	/**
	 * Controller's title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Acl settings
	 *
	 * @var array
	 */
	public $acl = [
		'level' => ['public']
		//'level' => ['public', 'authorized', 'permission'],
		//'tokens' => ['token1', 'token2'],
	];
}
