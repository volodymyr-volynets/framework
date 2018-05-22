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
	 * @var \\Object\Controller
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
			$composer = self::get("dep.composer.{$key}");
			if (empty($composer)) return false;
			$key = str_replace('.', '/', $key);
			$existing = array_key_get(self::$settings, ['backend_exists', $key]);
			if (isset($existing)) {
				return $existing;
			} else {
				$flag = file_exists('./../libraries/vendor/' . $key);
				array_key_set(self::$settings, ['backend_exists', $key], $flag);
				return $flag;
			}
		}
		// get data from settings
		$result = array_key_get(self::$settings, $key);
		// submodule exists
		if (!empty($options['submodule_exists'])) {
			$parts = explode('\\', trim($key, '\\'));
			array_pop($parts);
			$temp = \Application::get('dep.submodule.' . implode('.', $parts));
			return !empty($temp);
		}
		// if we need to fix class name
		if (!empty($options['class'])) {
			$result = str_replace('.', '\\', $result);
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
		$ini_folder = isset($options['ini_folder']) ? (rtrim($options['ini_folder'], '/') . '/') : $application_path . 'Config/';
		// working directory is location of the application
		chdir($application_path);
		$application_path_full = getcwd();
		// setting include_path
		$paths = [];
		$paths[] = $application_path_full;
		$paths[] = __DIR__;
		$paths[] = str_replace(['/Numbers/Framework', '/numbers/Framework'], '', __DIR__);
		set_include_path(implode(PATH_SEPARATOR, $paths));
		// autoloader
		spl_autoload_register(array('Application', 'autoloader'));
		// support functions
		require('Functions.php');
		// load ini settings
		self::$settings = System\Config::load($ini_folder);
		self::$settings['application']['system']['request_time'] = $application_request_time;
		// special handling of media files for development, so there's no need to redeploy application
		if (self::$settings['environment'] == 'development' && isset($_SERVER['REQUEST_URI'])) {
			\System\Media::serveMediaIfExists($_SERVER['REQUEST_URI'], $application_path);
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
		self::processMagicVariables();
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
		register_shutdown_function(array('Bootstrap', 'destroy'));
		// error handler first
		\Object\Error\Base::init();
		// debug after error handler
		\Debug::init(self::get('debug'));
		// Bootstrap Class
		$bootstrap = new Bootstrap();
		$bootstrap_methods = get_class_methods($bootstrap);
		foreach ($bootstrap_methods as $method) {
			if (strpos($method, 'init')===0) call_user_func([$bootstrap, $method], $options);
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
		\Object\Controller\Front::setMvc($options['request_uri'] ?? null);
		// check if controller exists
		if (!file_exists(self::$settings['mvc']['controller_file'])) {
			Throw new \Exception('Resource not found!', -1);
		}
		// initialize the controller
		$controller_class = self::$settings['mvc']['controller_class'];
		self::$controller = new $controller_class;
		// forcing people to do things
		if (!empty($_SESSION['numbers']['force'])) {
			$already = false;
			foreach ($_SESSION['numbers']['force'] as $k => $v) {
				if ($v['controller'] == \Application::get(['mvc', 'full'])) {
					$already = $v;
					break;
				}
			}
			$next = current($_SESSION['numbers']['force']);
			if (empty($already) && !\Application::get('flag.global.__ajax') && !\Helper\Cmd::isCli() && empty(self::$controller->skip_monitoring)) {
				\Request::redirect($next['controller']);
			} else if (!empty($already)) {
				\Layout::addMessage($already['message'], DANGER);
			}
		}
		// dispatch before, we need some settings from the controller
		if (!empty(self::$settings['application']['dispatch']['before_controller'])) {
			call_user_func(self::$settings['application']['dispatch']['before_controller']);
		}
		// start singleton
		if (!empty(self::$controller->singleton_flag)) {
			$message = self::$controller->singleton_message ?? 'This script is being run by another user!';
			$lock_id = "singleton_" . $controller_class;
			if (\Lock::process($lock_id)===false) {
				Throw new \Exception($message);
			}
		}
		// process parameters and provide output
		self::process();
		// release singleton lock
		if (self::$controller->singleton_flag) {
			\Lock::release($lock_id);
		}
		// dispatch after controller
		if (!empty(self::$settings['application']['dispatch']['after_controller'])) {
			call_user_func(self::$settings['application']['dispatch']['after_controller']);
		}
	}

	/**
	 * Load classes
	 *
	 * @param string $class
	 */
	public static function autoloader($class) {
		$class = ltrim($class, '\\');
		if (class_exists($class, false) || interface_exists($class, false)) {
			return;
		}
		$whitelisted = ['Memcached', 'PHPUnit', 'Symfony'];
		foreach ($whitelisted as $v) {
			if (strpos($class, $v) === 0) return;
		}
		// we need to check if we have customization for classes, we only allow 
		// customizaton for models and controllers
		$file = str_replace(['_', '\\'], DIRECTORY_SEPARATOR, $class) . '.php';
		// we need to store class path so we can load js, css and scss files
		self::$settings['application']['loaded_classes'][$class] = [
			'class' => $class,
			'file' => $file,
			'media' => []
		];
		// debuging
		if (class_exists('Debug', false) && \Debug::$debug) {
			\Debug::$data['classes'][] = ['class' => $class, 'file' => $file];
		}
		require_once($file);
	}

	/*
	 * Processing and generating layout
	 * 
	 * @return string
	 */
	public static function process($options = []) {
		// start buffering
		\Helper\Ob::start(true);
		$controller_class = self::$settings['mvc']['controller_class'];
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
		if ($controller_class != '\Controller\Errors') {
			if (!self::$controller->permitted(['redirect' => true])) {
				Throw new \Exception('Permission denied!', -1);
			}
		}
		// auto populating input property in controller
 		if (!empty(self::$settings['application']['controller']['input'])) {
			self::$controller->input = \Request::input(null, true, true);
 		}
		// init method
		if (method_exists(self::$controller, 'init')) {
			call_user_func(array(self::$controller, 'init'));
		}
		// check if action exists
		if (!method_exists(self::$controller, self::$controller->action_method)) {
			Throw new \Exception('Action does not exists!');
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
					$view_object = new \View(self::$controller, $file, $extension);
					$flag_view_found = true;
					break;
				}
			}
			// if views are mandatory
			if (!empty(self::$settings['application']['view']['mandatory']) && !$flag_view_found) {
				Throw new \Exception('View ' . $view . ' does not exists!');
			}
		}
		// autoloading media files
		\Layout::includeMedia($controller_dir, $controller_file, $view, $controller_class);
		// appending view after controllers output
		self::$controller->data->view = (self::$controller->data->view ?? '') . \Helper\Ob::clean();
		// if we have to render debug toolbar
		if (\Debug::$toolbar) {
			\Helper\Ob::start();
		}
		// call pre rendering method in bootstrap
		\Bootstrap::preRender();
		// rendering layout
		$__skip_layout = self::get('flag.global.__skip_layout');
		if (!empty(self::$settings['mvc']['controller_layout']) && empty($__skip_layout)) {
			\Helper\Ob::start();
			if (file_exists(self::$settings['mvc']['controller_layout_file'])) {
				$layout_object = new \Layout(self::$controller, self::$settings['mvc']['controller_layout_file'], self::$settings['mvc']['controller_layout_extension']);
			}
			// session expiry dialog before replaces
			\Session::expiryDialog();
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
				'<!-- [numbers: layout onhtml] -->',
				'<!-- [numbers: workflows] -->'
			];
			$to = [
				\Layout::renderMessages(),
				\Layout::renderTitle(),
				\Layout::renderDocumentTitle(),
				\Layout::renderActions(),
				\Layout::renderBreadcrumbs(),
				\Layout::renderJs(),
				\Layout::renderJsData(),
				\Layout::renderCss(),
				\Layout::renderOnload(),
				\Layout::$onhtml,
				\Object\Form\Workflow\Base::render()
			];
			echo str_replace($from, $to, \Helper\Ob::clean());
		} else {
			echo self::$controller->view;
		}
		// headers
		if (!empty(self::$settings['header']) && !headers_sent()) {
			foreach (self::$settings['header'] as $k => $v) {
				header($v);
			}
		}
		// ajax calls that has not been processed by application
		if (self::get('flag.global.__ajax')) {
			\Layout::renderAs(['success' => false, 'error' => [\I18n(null, 'Could not process ajax call!')]], 'application/json');
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
	 * Process magic variables
	 */
	public static function processMagicVariables() {
		$variables_object = new \Object\Magic\Variables();
		$variables = $variables_object->get();
		$input = \Request::input(null, true, true);
		foreach ($variables as $k => $v) {
			if (!array_key_exists($k, $input)) {
				self::$settings['flag']['global'][$k] = null;
				continue;
			}
			if ($k == '__content_type') {
				$object = new \Object\Content\Types();
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
	public static function isDeployed() {
		return (strpos(__FILE__, '/deployments/build.') !== false);
	}
}