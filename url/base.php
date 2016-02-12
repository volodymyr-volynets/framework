<?php

class url_base {

	/**
	 * Generate url
	 *
	 * @param array $options
	 *		controller - array or string controller parts
	 *		action - string action
	 *		id - mixed id
	 *		virtual - boolean flag
	 *		tinyurl - whether to generate tinuurl
	 *		full - boolean flag
	 *		get_params - array of GET parameters
	 * @return string
	 */
	public static function get($options = []) {
		// we need to fix controller, action and id
		$controller = $options['controller'] ?? '';
		if (is_array($controller)) {
			$controller = implode('/', $controller);
		}
		$controller = rtrim($controller, '/');
		$action = $options['action'] ?? null;
		$id = $options['id'] ?? null;
		// processing virtual
		if (!empty($options['virtual'])) {
			$virtual_object = new object_virtual_controllers();
			$virtual_data = $virtual_object->get();
			foreach ($virtual_data as $k => $v) {
				if ($v['no_virtual_controller_path'] == $controller) {
					$controller = '/__' . $v['no_virtual_controller_code'];
					break;
				}
			}
		}
		// assembling
		$result = '';
		$host = '';
		if (!empty($options['full'])) {
			$host = rtrim(request::host(), '/');
		}
		$result.= $host;
		$result.= $controller;
		if (!empty($id) && empty($action)) {
			$action = 'index';
		}
		if (!empty($action)) {
			$result.= '/_' . $action;
		}
		if (!empty($id)) {
			$result.= '/' . $id;
		}
		// if we have GET parameters
		if (!empty($options['get_params'])) {
			$result.= '?' . http_build_query2($options['get_params']);
		}
		// if we need to generate tinyurl
		if (!empty($options['tinyurl'])) {
			$tinyurl_result = url_tinyurl::set($result);
			if ($tinyurl_result['success']) {
				$result = $host . '/__tinyurl/_i/' . $tinyurl_result['data']['hash'];
			}
		}
		return $result;
	}
}