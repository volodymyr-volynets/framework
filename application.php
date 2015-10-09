<?php

/**
 * Application Class
 */
class application {

	/**
	 * Application settings
	 *
	 * @var array
	 */
	protected static $settings = array();

	/**
	 * Process ini file
	 *
	 * @param string $ini_file
	 * @param string $environment
	*/
	public static function ini($ini_file, $environment) {
		$result = array();
		$data = parse_ini_file($ini_file, true);
		foreach ($data as $section=>$values) {
			$sections = explode(',', $section);
			if (empty($values) || (!in_array($environment, $sections) && !in_array('*', $sections))) continue;
			foreach ($values as $k=>$v) {
				array_key_set($result, explode('.', $k), $v);
			}
		}
		return $result;
	}

	/**
	 * Access to settings, we can get a set of keys
	 * @param mixed $key
	 * @param array $decrypt_keys
	 * @return mixed
	 */
	public static function get($keys = null, $decrypt_keys = array()) {
		$options = array_key_get(self::$settings, $keys);
		// decrypting certain columns
		if (!empty($decrypt_keys)) {
			array_walk_recursive($options, create_function('&$v, $k, $fn', 'if (in_array($k, $fn)) $v = cryptography::decrypt($v);'), $decrypt_keys);
		}
		return $options;
	}

	/**
	 * Set value in settings
	 * 
	 * @param unknown_type $keys
	 * @param unknown_type $value
	 */
	public static function set($keys, $value, $options = array()) {
		array_key_set(self::$settings, $keys, $value, $options);
	}

	/**
	 * Run application
	 *
	 * @param string $location - where to load application from
	 * @param string $environment - production, staging, testing, development
	 */
	public static function run($application_name, $application_path, $environment = 'production', $options = array()) {
		// support functions
		require("functions.php");

		// fixing location paths
		$application_path = rtrim($application_path, '/') . '/';

		// we need to solve chicken and egg problem so we load cache first and then run application
		cache::create('php', array('type'=>'php', 'dir'=>'../app/cache'));

		// loading ini files
		do {
			// see if we have cached version
			$cache_id = cache::id('application.ini.php');
			$data = cache::get($cache_id, $options['cache']);
			if ($data !== false) {
				self::$settings = $data;
				self::$settings['cache']['php'] = cache::$adapters['php'];
				break;
			}

			// loading and processing ini files
			$ini_folder = isset($options['ini_folder']) ? (rtrim($options['ini_folder'], '/') . '/') : $application_path;
			$ini_files = array($ini_folder . 'application.ini', $ini_folder . 'localhost.ini');
			foreach ($ini_files as $ini_file) {
				if (file_exists($ini_file)) {
					$ini_data = self::ini($ini_file, $environment);
					self::$settings = array_merge2(self::$settings, $ini_data);
				}
			}

			// at this point we need to store data in cache
			cache::set($cache_id, self::$settings, 0, null, 'php');
		} while(0);

		// making variables accesible though settings function
		self::$settings['environment'] = $environment;
		self::$settings['application']['name'] = $application_name;
		self::$settings['application']['path'] = $application_path;

		// settings system variables
		self::$settings['layout'] = array();

		// processing php settings
		if (isset(self::$settings['php'])) {
			foreach (self::$settings['php'] as $k=>$v) {
				if (is_array($v)) {
					foreach ($v as $k2=>$v2) {
						ini_set($k . '.' . $k2, $v2);
					}
				} else {
					ini_set($k, $v);
				}
			}
		}

		// Main Try Catch block
		try {

			// working directory is location of the application
			chdir($application_path);
			$application_path = getcwd();

			// setting include_path
			$paths = array();
			$paths[] = __DIR__;
			$paths[] = __DIR__.'/..';
			$paths[] = $application_path;
			set_include_path(implode(PATH_SEPARATOR, $paths));

			// Destructor
			register_shutdown_function(array('bootstrap', 'destroy'));

			// Bootstrap Class
			$bootstrap = new bootstrap();
			$bootstrap_methods = get_class_methods($bootstrap);
			foreach ($bootstrap_methods as $method) {
				if (strpos($method, 'init')===0) call_user_func(array($bootstrap, $method));
			}

			// processing mvc settings
			self::set_mvc();

			// special handling for captcha
			if (strpos(self::$settings['mvc']['controller_class'], 'captcha.jpg')!==false) {
				$type = str_replace(array('controller_', '_captcha.jpg'), '',self::$settings['mvc']['controller_class']);
				require('./controller/captcha.jpg');
				exit;
			}

			// check if controller exists
			$file = './' . str_replace('_', '/', self::$settings['mvc']['controller_class'] . '.php');
			if (!file_exists($file)) {
				Throw new Exception('File not found!');
			}

			// initialize the controller
			$controller_class = self::$settings['mvc']['controller_class'];
			$controller = new $controller_class;
			self::$settings['controller'] = get_object_vars($controller);

			// dispatch before, we need some settings from the controller
			if (!empty(self::$settings['application']['dispatch']['before_controller'])) {
				call_user_func(self::$settings['application']['dispatch']['before_controller']);
			}

			// singleton start
			if (!empty(self::$settings['controller']['singleton_flag'])) {
				$message = !empty(self::$settings['controller']['singleton_message']) ? self::$settings['controller']['singleton_message'] : 'This script is being run by another user!';
				$lock_id = "singleton_" . $controller_class;
				if (lock::process($lock_id)===false) {
					Throw new Exception($message);
				}
			}

			self::process();

			// release singleton lock
			if (!empty(self::$settings['controller']['singleton_flag'])) {
				lock::release($lock_id);
			}

			// dispatch after controller
			if (!empty(self::$settings['application']['dispatch']['after_controller'])) {
				call_user_func(self::$settings['application']['dispatch']['after_controller']);
			}

		} catch (Exception $e) {
			$previous_output = @ob_get_clean();
			self::set_mvc('/error/~error/500');
			self::process(array('exception'=>$e));
		}

		// Headers
		if (!empty(self::$settings['header']) && !headers_sent()) {
			foreach (self::$settings['header'] as $k=>$v) header($v);
		}
	}

	/**
	 * Load classes
	 *
	 * @param string $class
	 */
	public static function autoloader($class) {
		if (class_exists($class, false) || interface_exists($class, false)) {
			return;
		}
		// we need to check if we have customization for classes, we only allow 
		// customizaton for models and controllers
		$file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
		if (strpos($class, 'model_')!==false || strpos($class, 'controller_')!==false) {
			// todo: refactor code here
			$company_id = session::get('company_id');
			if (!empty($company_id)) {
				$custom_file = self::$settings['application']['path'] . 'custom/'  . $company_id . '/' . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
				$cached_file = self::$settings['application']['path'] . 'cache/custom/' . $company_id . '/'  . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
				$cached_dir = pathinfo($cached_file, PATHINFO_DIRNAME);

				// if we have custom file
				if (file_exists($custom_file)) {
					// generate cached version of the file
					if (!file_exists($cached_file)) {
						$content = file_get_contents($file);
						$content = str_replace('class ' . $class . ' {', 'class cache_custom_' . $company_id . '_' . $class . ' {', $content);
						if (!file_exists($cached_dir)) file::mkdir($cached_dir);
						file::write($cached_file, $content, 0777);
					}
					$file = $custom_file;
				}
			}
		}
		// we need to store class path so we can load js and css files
		global $__class_paths;
		$__class_paths[$class] = $file;
		require_once($file);
	}

	/**
	 * Parse request string into readable array
	 *
	 * @param string $url
	 * @return array
	 */
	public static function mvc($url = '') {
		$result = array(
			'controller' => '',
			'action' => '',
			'id' => 0,
			'controllers' => array(),
		);

		// remove an extra backslashes from left side
		$request_uri = explode('?', trim($url, '/'));
		$request_uri = @$request_uri[0];

		// determine action and controller
		$parts = explode('/', $request_uri);
		$flag_action_found = false;
		$flag_id_found = false;
		foreach ($parts as $part) {
			if (empty($part)) continue;
			if (strpos($part, '~')!==false) {
				$flag_action_found = true;
				$result['action'] = str_replace('~', '', $part);
				continue;
			}
			if (!$flag_action_found) {
				$result['controllers'][] = $part;
			}
			if ($flag_action_found) {
				$result['id'] = $part;
			}
		}

		// set default values for action and controller
		if (empty($result['controllers'])) {
			$result['controllers'][] = 'index';
		}
		$result['controller'] = '/' . implode('/', $result['controllers']);
		$result['controller'] = str_replace('_', '/', $result['controller']);
		if (empty($result['action'])) {
			$result['action'] = 'index';
		}
		// full string
		$result['full'] = $result['controller'] . '/~' . $result['action'];
		return $result;
	}

	/**
	 * Setting Mvc
	 *
	 * @param string $request_uri
	 */
	private static function set_mvc($request_uri = null) {
		// storing previous mvc settings
		if (!empty(self::$settings['mvc']['module'])) {
			if (!isset(self::$settings['mvc_prev'])) {
				self::$settings['mvc_prev'] = array();
			}
			self::$settings['mvc_prev'][] = self::$settings['mvc'];
		}
		// processing
		$request_uri = !empty($request_uri) ? $request_uri : $_SERVER['REQUEST_URI'];

		// routing based on rules
		$request_uri = self::route($request_uri);

		// parsing request
		$data = self::mvc($request_uri);

		// forming class name and method
		$controller_class = 'controller_' . str_replace(' ', '_', implode(' ', $data['controllers']));
		$controller_action = 'action_' . $data['action'];
		self::$settings['mvc'] = $data;
		self::$settings['mvc']['controller_class'] = $controller_class;
		self::$settings['mvc']['controller_action'] = $controller_action;
		self::$settings['mvc']['controller_view'] = $data['action'];
		self::$settings['mvc']['controller_layout'] = 'index';
	}

	/*
	 * Processing and generating layout
	 * 
	 * @return string
	 */
	private static function process($options = array()) {

		// get buffer content in case it is auto mode
		$buffer = @ob_end_clean();

		// start buffering
		ob_start();

		$controller_class = self::$settings['mvc']['controller_class'];
		$controller = new $controller_class;

		// processing options
 		if (!empty($options)) {
 			foreach ($options as $k=>$v) $controller->{$k} = $v;
 		}

 		// auto populating input property in controller
 		if (!empty(self::$settings['application']['controller']['input'])) {
 			$controller->input = request::input(null, true, true);
 		}

		// init method
		if (method_exists($controller, 'init')) {
			call_user_func(array($controller, 'init'));
		}

		// check if action exists
		$action = self::$settings['mvc']['controller_action'];
		if (!method_exists($controller, $action)) {
			Throw new Exception('Action does not exists!');
		}

		// calling action
		call_user_func(array($controller, $action));

		// auto rendering view only if view exists, processing extension order as specified in .ini file
		global $__class_paths;
		$controller_dir = pathinfo($__class_paths[$controller_class], PATHINFO_DIRNAME) . '/';
		$controller_file = end(self::$settings['mvc']['controllers']);
		$view = self::$settings['mvc']['controller_view'];
		if (!empty($view)) {
			$extensions = explode(',', @self::$settings['application']['view']['extension'] ? self::$settings['application']['view']['extension'] : 'html');
			$flag_view_found = false;
			foreach ($extensions as $extension) {
				$file = $controller_dir  . $controller_file . '.' . $view . '.' . $extension;
				if (file_exists($file)) {
					$controller = new view($controller, $file, $extension);
					$flag_view_found = true;
					break;
				}
			}
			// if views are mandatory
			if (@self::$settings['application']['view']['mandatory'] && !$flag_view_found) {
				Throw new Exception('View ' . $view . ' does not exists!');
			}
		}

		// autoloading media files
		if (!empty(self::$settings['application']['controller']['media'])) {
			$company_id = session::get('company_id');
			$extensions = explode(',', self::$settings['application']['controller']['media']);
			foreach ($extensions as $extension) {
				$file = $controller_dir . $controller_file . '.' . $extension;
				$cache_id = cache::id($file, $company_id);
				$http_file = '/cache/' . $cache_id;
				if (file_exists($file)) {
					cache::set($cache_id, file_get_contents($file), null, null, 'media');
					// including media into layout
					if ($extension=='css') layout::add_css($http_file);
					if ($extension=='js') layout::add_js($http_file);
				}
			}
		}

		// appending view after controllers output
		$controller->view = @$controller->view . @ob_get_clean();

		// rendering layout
		if (!empty(self::$settings['mvc']['controller_layout'])) {
			ob_start();
			$file = './layout/' . self::$settings['mvc']['controller_layout'] . '.' . @self::$settings['application']['layout']['extension'];
			if (file_exists($file)) {
				$controller = new layout($controller, $file);
			}
			// buffer output and handling javascript files
			echo str_replace('<!-- JavaScript Files -->', layout::render_js(), @ob_get_clean());
		} else {
			echo $controller->layout;
		}
		flush();
	}

	/**
	 * Changing view or layout
	 * @param string $what [layout,view]
	 * @param string $how
	 */
	public static function change($what, $how) {
		switch ($what) {
			case 'layout':
				self::$settings['mvc']['controller_layout'] = $how;
				break;
			case 'view':
				self::$settings['mvc']['controller_view'] = $how;
				break;
		}
	}

	/**
	 * Routing, allow re-routing
	 *
	 * @param string $uri
	 * @return string
	 */
	private static function route($uri) {
		$result = $uri;
		if (!empty(self::$settings['routes'])) {
			foreach (self::$settings['routes'] as $v) {
				$regex = '#^' . $v['regex'] . '#i';
				if (preg_match($regex, $result, $values)) {
					$result = $v['new'];
				}
			}
		}
		return $result;
	}
}
