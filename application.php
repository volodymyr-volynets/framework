<?php

class application {

	/**
	 * Application settings
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Access to settings, we can get a set of keys
	 * @param mixed $key
	 * @param array $decrypt_keys
	 * @return mixed
	 */
	public static function get($key = null, $decrypt_keys = []) {
		$options = array_key_get(self::$settings, $key);
		// decrypting certain columns
		if (!empty($decrypt_keys)) {
			array_walk_recursive($options, create_function('&$v, $k, $fn', 'if (in_array($k, $fn)) $v = crypt::decrypt($v);'), $decrypt_keys);
		}
		return $options;
	}

	/**
	 * Set value in settings
	 * 
	 * @param mixed $key
	 * @param mixed $value
	 */
	public static function set($key, $value, $options = []) {
		array_key_set(self::$settings, $key, $value, $options);
	}

	/**
	 * Run application
	 * 
	 * @param string $application_name
	 * @param string $application_path
	 * @param string $environment
	 * @param array $options
	 * @throws Exception
	 */
	public static function run($options = []) {

		// fixing location paths
		$application_path = isset($options['application_path']) ? (rtrim($options['application_path'], '/') . '/') : '../application/';
		$application_name = isset($options['application_name']) ? $options['application_name'] : 'default';
		$ini_folder = isset($options['ini_folder']) ? (rtrim($options['ini_folder'], '/') . '/') : $application_path . 'config/';

		// working directory is location of the application
		chdir($application_path);
		$application_path_new = getcwd();

		// setting include_path
		$paths = [];
		$paths[] = __DIR__;
		$paths[] = str_replace('/numbers/framework', '', __DIR__);
		$paths[] = $application_path_new;
		set_include_path(implode(PATH_SEPARATOR, $paths));

		// support functions
		require("functions.php");

		// load ini settings
		self::$settings = system_config::load($ini_folder);

		// special handling of media files for development, so there's no need to redeploy application
		if (self::$settings['environment'] == 'development' && isset($_SERVER['REQUEST_URI'])) {
			system_media::serve_media_if_exists($_SERVER['REQUEST_URI'], $application_path);
		}

		// we need to solve chicken and egg problem so we load cache first and then run application
		//cache::create('php', array('type'=>'php', 'dir'=>'../application/cache'));

		// setting variables
		if (!isset(self::$settings['application']) || !is_array(self::$settings['application'])) {
			self::$settings['application'] = [];
		}
		self::$settings['application']['name'] = $application_name;
		self::$settings['application']['path'] = $application_path;
		self::$settings['layout'] = [];
		self::$settings['flag'] = (isset(self::$settings['flag']) && is_array(self::$settings['flag'])) ? self::$settings['flag'] : [];

		// processing php settings
		if (isset(self::$settings['php'])) {
			foreach (self::$settings['php'] as $k=>$v) {
				if (is_array($v)) {
					foreach ($v as $k2=>$v2) {
						if (is_numeric($v2)) {
							$v2 = $v2 * 1;
						}
						ini_set($k . '.' . $k2, $v2);
					}
				} else {
					if (is_numeric($v)) {
						$v = $v * 1;
					}
					ini_set($k, $v);
				}
			}
		}

		// Destructor
		register_shutdown_function(array('bootstrap', 'destroy'));

		// error handler first
		error::init();

		// debug after error handler
		debug::init(self::get('debug'));

		// Bootstrap Class
		$bootstrap = new bootstrap();
		$bootstrap_methods = get_class_methods($bootstrap);
		foreach ($bootstrap_methods as $method) {
			if (strpos($method, 'init')===0) call_user_func(array($bootstrap, $method), $options);
		}

		// if we are calling application from the command line
		if (!empty($options['__run_only_bootstrap'])) {
			// dispatch before, in case if we open database connections in there
			if (!empty(self::$settings['application']['dispatch']['before_controller'])) {
				call_user_func(self::$settings['application']['dispatch']['before_controller']);
			}
			return;
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
			Throw new Exception('Controller not found!');
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

		// headers
		if (!empty(self::$settings['header']) && !headers_sent()) {
			foreach (self::$settings['header'] as $k=>$v) {
				header($v);
			}
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
		// todo: refactor here
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
			'controllers' => [],
		);

		// remove an extra backslashes from left side
		$request_uri = explode('?', trim($url, '/'));
		$request_uri = @$request_uri[0];

		// determine action and controller
		// todo: we need to make it flexible
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
	public static function set_mvc($request_uri = null) {
		// storing previous mvc settings
		if (!empty(self::$settings['mvc']['module'])) {
			if (!isset(self::$settings['mvc_prev'])) {
				self::$settings['mvc_prev'] = [];
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
	public static function process($options = []) {

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
			$extensions = explode(',', isset(self::$settings['application']['view']['extension']) ? self::$settings['application']['view']['extension'] : 'html');
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
			if (!empty(self::$settings['application']['view']['mandatory']) && !$flag_view_found) {
				Throw new Exception('View ' . $view . ' does not exists!');
			}
		}

		// autoloading media files
		if (!empty(self::$settings['application']['controller']['media'])) {
			// todo: refactor here
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
		$controller->view = (isset($controller->view) ? $controller->view : '') . @ob_get_clean();

		// if we have to render debug toolbar
		if (debug::$toolbar) {
			ob_start();
		}

		// rendering layout
		if (!empty(self::$settings['mvc']['controller_layout'])) {
			ob_start();
			$extension = isset(self::$settings['application']['layout']['extension']) ? self::$settings['application']['layout']['extension'] : 'html';
			$file = './layout/' . self::$settings['mvc']['controller_layout'] . '.' . $extension;
			if (file_exists($file)) {
				$controller = new layout($controller, $file);
			}
			// buffer output and handling javascript files, chicken and egg problem
			$from = [
				'<!-- [numbers: javascript links] -->',
				'<!-- [numbers: css links] -->'
			];
			$to = [
				layout::render_js(),
				layout::render_css()
			];
			echo str_replace($from, $to, @ob_get_clean());
		} else {
			echo $controller->view;
		}

		// flushing
		if (!debug::$toolbar) {
			flush();
		}
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