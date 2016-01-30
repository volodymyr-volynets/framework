<?php

/**
 * This class is mirror for numbers.js
 */
class numbers {

	/**
	 * Generate url
	 *
	 * @param mixed $controller
	 * @param string $action
	 * @param mixed $id
	 * @param array $options
	 * @return string
	 */
	public static function url($controller, $action = null, $id = null, $options = []) {
		$result = [];
		// processing controller
		if (is_array($controller)) {
			$result = $controller;
		} else {
			$controller = $controller . '';
			if ($controller[0] == '/') {
				$result[] = ltrim($controller, '/');
			} else if (strpos($controller, '.') !== false) {
				$result = explode('.', $controller);
			} else if (strpos($controller, '_') !== false) {
				$result = explode('_', $controller);
			} else {
				if (!$controller) {
					$controller = 'index';
				}
				$result[] = $controller;
			}
		}
		// processing action
		if (isset($action)) {
			$result[] = '~' . $action;
		}
		// processing id
		if (isset($id)) {
			if (!isset($action)) {
				$result[] = '~index';
			}
			$result[] = $id;
		}
		// if we need to include host name
		if (!empty($options['host'])) {
			return request::host() . implode('/', $result);
		} else {
			return '/' . implode('/', $result);
		}
	}
}
