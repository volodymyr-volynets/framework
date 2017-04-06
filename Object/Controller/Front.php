<?php

namespace Object\Controller;
class Front {

	/**
	 * Parse request string into readable array
	 *
	 * @param string $url
	 * @return array
	 */
	public static function mvc($url = '') {
		$result = array(
			'controller' => '',
			'controller_extension' => '',
			'action' => '',
			'id' => 0,
			'controllers' => [],
		);
		// remove an extra backslashes from left side
		$request_uri = explode('?', trim($url, '/'));
		$request_uri = $request_uri[0];
		// determine action and controller
		$parts = explode('/', $request_uri);
		// virtual controller
		if (substr($parts[0], 0, 2) == '__') {
			$virtual_object = new \Object\Virtual\Controllers();
			$virtual_data = $virtual_object->get();
			$key = substr($parts[0], 2);
			if (isset($virtual_data[$key])) {
				$temp = $parts;
				unset($temp[0]);
				$parts = explode('/', trim($virtual_data[$key]['no_virtual_controller_path'], '/'));
				foreach ($temp as $v) {
					$parts[] = $v;
				}
			}
		}
		$flag_action_found = false;
		foreach ($parts as $v) {
			if ($v . '' == '') {
				continue;
			}
			if (isset($v[0]) && $v[0] == '_' && !$flag_action_found) {
				$flag_action_found = true;
				$result['action'] = substr($v, 1);
				continue;
			}
			if (!$flag_action_found) {
				$result['controllers'][] = $v;
			}
			if ($flag_action_found) {
				$result['id'] = $v;
				break;
			}
		}
		// set default values for action and controller
		if (empty($result['controllers'])) {
			$result['controllers'][] = 'Index';
		} else {
			// processing controller extension
			end($result['controllers']);
			$key = key($result['controllers']);
			$last = $result['controllers'][$key];
			if (strpos($last, '.')) {
				$temp = explode('.', $last);
				$result['controllers'][$key] = $temp[0];
				unset($temp[0]);
				$result['controller_extension'] = implode('.', $temp);
			}
		}
		$result['controller'] = '/' . implode('/', $result['controllers']);
		$result['controller'] = str_replace('_', '/', $result['controller']);
		if (empty($result['action'])) {
			$result['action'] = 'Index';
		}
		// full string
		$result['full'] = $result['controller'] . '/_' . $result['action'];
		$result['full_with_host'] = rtrim(\Request::host(), '/') . $result['controller'] . '/_' . $result['action'];
		return $result;
	}

	/**
	 * Setting MVC
	 *
	 * @param string $request_uri
	 */
	public static function setMvc($request_uri = null) {
		// storing previous mvc settings
		if (\Application::get('mvc.module')) {
			$mvc_prev = \Application::get('mvc_prev');
			if (is_array($mvc_prev)) $mvc_prev = [];
			$mvc_prev[] = \Application::get('mvc');
			\Application::set('mvc_prev', $mvc_prev);
		}
		// processing
		$request_uri = !empty($request_uri) ? $request_uri : $_SERVER['REQUEST_URI'];
		// routing based on rules
		$request_uri = self::route($request_uri);
		// parsing request
		$data = self::mvc($request_uri);
		// forming class name and file
		// todo: add full path here instead of relative
		if (in_array('Controller', $data['controllers'])) {
			// todo: custom modules handling
			$controller_class = str_replace(' ', '\\', implode(' ', $data['controllers']));
			$file = './../libraries/vendor/' . str_replace('\\', '/', $controller_class . '.php');
		} else {
			$controller_class = 'Controller\\' . str_replace(' ', '\\', implode(' ', $data['controllers']));
			$file = './' . str_replace('\\', '/', $controller_class . '.php');
		}
		$controller_class = '\\' . $controller_class;
		// assembling everything into settings
		$data = $data;
		$data['controller_class'] = $controller_class;
		$data['controller_action'] = 'action' . str_replace('_', ' ', $data['action']);
		$data['controller_action_code'] = $data['action'];
		$data['controller_id'] = $data['id'];
		$data['controller_view'] = $data['action'];
		$data['controller_layout'] = \Application::get('application.layout.layout') ?? 'index';
		$data['controller_layout_extension'] = \Application::get('application.layout.extension') ?? 'html';
		$data['controller_layout_file'] = \Application::get(['application', 'path_full']) . 'Layout/' . $data['controller_layout'] . '.' . $data['controller_layout_extension'];
		$data['controller_file'] = $file;
		\Application::set('mvc', $data);
	}

	/**
	 * Routing, allow re-routing
	 *
	 * @param string $uri
	 * @return string
	 */
	private static function route($uri) {
		$result = $uri;
		$routes = \Application::get('routes');
		if (!empty($routes)) {
			foreach ($routes as $v) {
				$regex = '#^' . $v['regex'] . '#i';
				if (preg_match($regex, $result, $values)) {
					$result = $v['new'];
				}
			}
		}
		return $result;
	}
}