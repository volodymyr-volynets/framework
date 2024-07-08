<?php

namespace Object\Controller;
class Callable2 extends \Object\Controller {

	/**
	 * Acl settings
	 *
	 * Public only
	 *
	 * @var array
	 */
	public $acl = [
		'public' => true,
		'authorized' => true,
		'permission' => false
	];

	/**
	 * Callable action
	 *
	 * @param array $input
	 */
	public function actionCallable() {
		$input = \Request::input();
		return call_user_func_array($this->route->callable, [$this, $input]);
	}
}