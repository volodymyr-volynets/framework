<?php

namespace Object\Form\Workflow;
class Base {

	/**
	 * Render
	 */
	public static function render() {
		$model = \Object\ACL\Resources::getStatic('workflow', 'renderer', 'method');
		$workflow = \Session::get(['numbers', 'workflow']);
		if (!empty($model) && !empty($workflow)) {
			$method = \Factory::method($model);
			return call_user_func_array([$method[0], $method[1]], [$workflow]);
		}
		return '';
	}
}