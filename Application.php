<?php

class Application {

	/**
	 * Application settings
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Controller
	 *
	 * @var \object_controller
	 */
	public static $controller;

	/**
	 * Access to settings, we can get a set of keys
	 * @param mixed $key
	 * @param array $options
	 *		array decrypt_keys - if we need to decrypt keys
	 *		boolean class - if we need to return proper class name
	 *		boolean backend_exists - if we need to determine if backend exists
	 *		boolean submodule_exists - check if submodule is available
	 * @return mixed
	 */
	public static function get($key = null, $options = []) {
		// if we need to determine if backend exists
		if (!empty($options['backend_exists'])) {
			$key = str_replace('.', '_', $key);
			$existing = array_key_get(self::$settings, ['backend_exists', $key]);
			if (isset($existing)) {
				return $existing;
			} else {
				$flag = file_exists('./../libraries/vendor/' . str_replace('_', '/', $key));
				array_key_set(self::$settings, ['backend_exists', $key], $flag);
				return $flag;
			}
		}
		// get data from settings
		$result = array_key_get(self::$settings, $key);
		// decrypting certain columns
		/* todo: maybe this is not needed at all
		if (!empty($options['decrypt_keys'])) {
			array_walk_recursive($result, create_function('&$v, $k, $fn', 'if (in_array($k, $fn)) $v = crypt::static_decrypt($v);'), $options['decrypt_keys']);
		}
		*/
		// submodule exists
		if (!empty($options['submodule_exists'])) {
			$temp = explode('.', $result);
			array_pop($temp);
			array_unshift($temp, 'submodule');
			array_unshift($temp, 'dep');
			$exists = array_key_get(self::$settings, $temp);
			if (empty($exists)) return false;
		}
		// if we need to fix class name
		if (!empty($options['class'])) {
			$result = str_replace('.', '_', $result);
		}
		return $result;
	}

	/**
	 * Set value in settings
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param array $options
	 *		boolean append - whether to append value to array
	 */
	public static function set($key, $value, $options = []) {
		array_key_set(self::$settings, $key, $value, $options);
	}

	/**
	 * Run application
	 * 
	 * @param array $options
	 *		string application_name
	 *		string application_path
	 *		string ini_folder
	 *		boolean __run_only_bootstrap
	 * @throws Exception
	 */
	public static function run($options = []) {
		// recort application start time
		$application_request_time = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
		// fixing location paths
		$application_path = isset($options['application_path']) ? (rtrim($options['application_path'], '/') . '/') : '../application/';
		$application_name = isset($options['application_name']) ? $options['application_name'] : 'default';
		$ini_folder = isset($options['ini_folder']) ? (rtrim($options['ini_folder'], '/') . '/') : $application_path . 'config/';
		// working directory is location of the application
		chdir($application_path);
		$application_path_full = getcwd();
		// setting include_path
		$paths = [];
		$paths[] = $application_path_full;
		$paths[] = __DIR__;
		$paths[] = str_replace('/numbers/framework', '', __DIR__);
		set_include_path(implode(PATH_SEPARATOR, $paths));
		// support functions
		require("functions.php");
		// load ini settings
		self::$settings = system_config::load($ini_folder);
		self::$settings['application']['system']['request_time'] = $application_request_time;
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
		self::$settings['application']['path_full'] = $application_path_full . '/';
		self::$settings['application']['loaded_classes'] = []; // class paths
		self::$settings['layout'] = []; // layout settings
		// flags
		self::$settings['flag'] = (isset(self::$settings['flag']) && is_array(self::$settings['flag'])) ? self::$settings['flag'] : [];
		self::$settings['flag']['global']['__run_only_bootstrap'] = !empty($options['__run_only_bootstrap']);
		// magic variables processed here
		self::$settings['flag']['global']['__content_type'] = 'text/html';
		self::process_magic_variables();
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
		error_base::init();
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
		// check if controller exists
		if (!file_exists(self::$settings['mvc']['controller_file'])) {
			Throw new Exception('Resource not found!', -1);
		}
		// initialize the controller
		$controller_class = self::$settings['mvc']['controller_class'];
		self::$controller = new $controller_class;
		// dispatch before, we need some settings from the controller
		if (!empty(self::$settings['application']['dispatch']['before_controller'])) {
			call_user_func(self::$settings['application']['dispatch']['before_controller']);
		}
		// start singleton
		if (!empty(self::$controller->singleton_flag)) {
			$message = self::$controller->singleton_message ?? 'This script is being run by another user!';
			$lock_id = "singleton_" . $controller_class;
			if (lock::process($lock_id)===false) {
				Throw new Exception($message);
			}
		}
		// process parameters and provide output
		self::process();
		// release singleton lock
		if (self::$controller->singleton_flag) {
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
		/* todo: refactor later
		if (strpos($class, 'model_') !== false || strpos($class, 'controller_') !== false) {
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
		*/
		// we need to store class path so we can load js, css and scss files
		self::$settings['application']['loaded_classes'][$class] = [
			'class' => $class,
			'file' => $file,
			'media' => []
		];
		// debuging
		if (class_exists('debug', false) && debug::$debug) {
			debug::$data['classes'][] = ['class' => $class, 'file' => $file];
		}
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
			$virtual_object = new object_virtual_controllers();
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
			$result['controllers'][] = 'index';
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
			$result['action'] = 'index';
		}
		// full string
		$result['full'] = $result['controller'] . '/_' . $result['action'];
		$result['full_with_host'] = rtrim(request::host(), '/') . $result['controller'] . '/_' . $result['action'];
		return $result;
	}

	/**
	 * Setting MVC
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
		// forming class name and file
		// todo: add full path here instead of relative
		if (in_array('controller', $data['controllers'])) {
			// todo: custom modules handling
			$controller_class = str_replace(' ', '_', implode(' ', $data['controllers']));
			$file = './../libraries/vendor/' . str_replace('_', '/', $controller_class . '.php');
		} else {
			$controller_class = 'controller_' . str_replace(' ', '_', implode(' ', $data['controllers']));
			$file = './' . str_replace('_', '/', $controller_class . '.php');
		}
		// assembling everything into settings
		self::$settings['mvc'] = $data;
		self::$settings['mvc']['controller_class'] = $controller_class;
		self::$settings['mvc']['controller_action'] = 'action_' . $data['action'];
		self::$settings['mvc']['controller_action_code'] = $data['action'];
		self::$settings['mvc']['controller_id'] = $data['id'];
		self::$settings['mvc']['controller_view'] = $data['action'];
		self::$settings['mvc']['controller_layout'] = self::$settings['application']['layout']['layout'] ?? 'index';
		self::$settings['mvc']['controller_layout_extension'] = (self::$settings['application']['layout']['extension'] ?? 'html');
		self::$settings['mvc']['controller_layout_file'] = application::get(['application', 'path_full']) . 'layout/' . self::$settings['mvc']['controller_layout'] . '.' . self::$settings['mvc']['controller_layout_extension'];
		self::$settings['mvc']['controller_file'] = $file;
	}

	/*
	 * Processing and generating layout
	 * 
	 * @return string
	 */
	public static function process($options = []) {
		// start buffering
		helper_ob::start(true);
		$controller_class = self::$settings['mvc']['controller_class'];
		// if we are handling error message and controller class has not been loaded
		if ($controller_class == 'controller_error' && error_base::$flag_error_already && !class_exists('controller_error')) {
			require('./controller/error.php');
		}
		// processing options
 		if (!empty($options)) {
			foreach ($options as $k => $v) {
				self::$controller->{$k} = $v;
			}
 		}
		// put action into controller
		self::$controller->action_code = self::$settings['mvc']['controller_action_code'];
		self::$controller->action_method = self::$settings['mvc']['controller_action'];
		// check ACL
		if ($controller_class != 'controller_error') {
			if (!self::$controller->permitted(['redirect' => true])) {
				Throw new Exception('Permission denied!', -1);
			}
		}
		// auto populating input property in controller
 		if (!empty(self::$settings['application']['controller']['input'])) {
			self::$controller->input = request::input(null, true, true);
 		}
		// init method
		if (method_exists(self::$controller, 'init')) {
			call_user_func(array(self::$controller, 'init'));
		}
		// check if action exists
		if (!method_exists(self::$controller, self::$controller->action_method)) {
			Throw new Exception('Action does not exists!');
		}
		// calling action
		echo call_user_func(array(self::$controller, self::$controller->action_method));
		// auto rendering view only if view exists, processing extension order as specified in .ini file
		$temp_reflection_obj = new ReflectionClass(self::$controller);
		$controller_dir = pathinfo($temp_reflection_obj->getFileName(), PATHINFO_DIRNAME) . '/';
		$controller_file = end(self::$settings['mvc']['controllers']);
		$view = self::$settings['mvc']['controller_view'];
		$flag_view_found = false;
		if (!empty($view)) {
			$extensions = explode(',', isset(self::$settings['application']['view']['extension']) ? self::$settings['application']['view']['extension'] : 'html');
			foreach ($extensions as $extension) {
				$file = $controller_dir  . $controller_file . '.' . $view . '.' . $extension;
				if (file_exists($file)) {
					self::$controller = new view(self::$controller, $file, $extension);
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
		layout::include_media($controller_dir, $controller_file, $view, $controller_class);
		// appending view after controllers output
		self::$controller->view = (self::$controller->view ?? '') . helper_ob::clean();
		// if we have to render debug toolbar
		if (debug::$toolbar) {
			helper_ob::start();
		}
		// call pre rendering method in bootstrap
		bootstrap::pre_render();
		// rendering layout
		$__skip_layout = self::get('flag.global.__skip_layout');
		if (!empty(self::$settings['mvc']['controller_layout']) && empty($__skip_layout)) {
			helper_ob::start();
			if (file_exists(self::$settings['mvc']['controller_layout_file'])) {
				self::$controller = new layout(self::$controller, self::$settings['mvc']['controller_layout_file'], self::$settings['mvc']['controller_layout_extension']);
			}
			// session expiry dialog before replaces
			session::expiry_dialog();
			// buffer output and handling javascript files, chicken and egg problem
			$from = [
				'<!-- [numbers: messages] -->',
				'<!-- [numbers: title] -->',
				'<!-- [numbers: document title] -->',
				'<!-- [numbers: actions] -->',
				'<!-- [numbers: breadcrumbs] -->',
				'<!-- [numbers: javascript links] -->',
				'<!-- [numbers: javascript data] -->',
				'<!-- [numbers: css links] -->',
				'<!-- [numbers: layout onload] -->',
				'<!-- [numbers: layout onhtml] -->'
			];
			$to = [
				layout::render_messages(),
				layout::render_title(),
				layout::render_document_title(),
				layout::render_actions(),
				layout::render_breadcrumbs(),
				layout::render_js(),
				layout::render_js_data(),
				layout::render_css(),
				layout::render_onload(),
				layout::$onhtml
			];
			echo str_replace($from, $to, helper_ob::clean());
		} else {
			echo self::$controller->view;
		}
		// ajax calls that has not been processed by application
		if (self::get('flag.global.__ajax')) {
			layout::render_as(['success' => false, 'error' => [i18n(null, 'Could not process ajax call!')]], 'application/json');
		}
	}

	/**
	 * Changing view or layout
	 *
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

	/**
	 * Process magic variables
	 */
	public static function process_magic_variables() {
		$variables_object = new object_magic_variables();
		$variables = $variables_object->get();
		$input = request::input(null, true, true);
		foreach ($variables as $k => $v) {
			if (!array_key_exists($k, $input)) {
				continue;
			}
			if ($k == '__content_type') {
				$object = new object_content_types();
				$data = $object->get();
				if (isset($data[$input[$k]])) {
					self::$settings['flag']['global'][$k] = $input[$k];
				}
			} else {
				self::$settings['flag']['global'][$k] = $input[$k];
			}
		}
	}

	/**
	 * Check if application has been deployed
	 *
	 * @return boolean
	 */
	public static function is_deployed() {
		return (strpos(__FILE__, '/deployments/build.') !== false);
	}
}