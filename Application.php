<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Cmd;
use Helper\Ob;
use Object\Content\Messages;
use Object\Content\Types;
use Object\Controller;
use Object\Controller\Front;
use Object\Error\Base;
use Object\Error\PermissionException;
use Object\Error\ResourseNotFoundException;
use Object\Magic\Variables;
use System\Config;
use System\Media;
use Helper\Constant\HTTPConstants;
use Object\Reflection;
use Controller\Errors;
use NF\Error;

class Application
{
    /**
     * Application settings
     *
     * @var array
     */
    protected static $settings = [];

    /**
     * Controller
     *
     * @var Controller
     */
    public static $controller;

    /**
     * @var Request
     */
    public static ?Request $request = null;

    /**
     * @var Response
     */
    public static ?Response $response = null;

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
    public static function get($key = null, $options = [])
    {
        // if we need to determine if backend exists
        if (!empty($options['backend_exists'])) {
            $composer = self::get("dep.composer.{$key}");
            if (empty($composer)) {
                return false;
            }
            $key = str_replace('.', '/', $key);
            // we must set current working directory
            chdir(Application::get('application.path_full'));
            return file_exists('./../libraries/vendor/' . strtolower($key));
        }
        // get data from settings
        $result = array_key_get(self::$settings, $key);
        // submodule exists
        if (!empty($options['submodule_exists'])) {
            $parts = explode('\\', trim($key, '\\'));
            array_pop($parts);
            $temp = Application::get('dep.submodule.' . implode('.', $parts));
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
    public static function set($key, $value, $options = [])
    {
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
    public static function run($options = [])
    {
        // setting variables
        if (!isset(self::$settings['application']) || !is_array(self::$settings['application'])) {
            self::$settings['application'] = [];
        }
        // recort application start time
        $application_request_time = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        // fixing location paths
        $application_path = isset($options['application_path']) ? (rtrim($options['application_path'], '/') . '/') : '../application/';
        $root_path = $application_path . '../';
        $application_name = isset($options['application_name']) ? $options['application_name'] : 'default';
        $ini_folder = isset($options['ini_folder']) ? (rtrim($options['ini_folder'], '/') . '/') : $application_path . 'Config/';
        // working directory is location of the application
        chdir($application_path);
        $application_path_full = getcwd();
        // setting include_path
        $paths = [];
        $paths[] = $application_path_full;
        $application_path_temp = preg_replace('/\/application$/', '', $application_path_full);
        $paths[] = $application_path_temp . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'numbers' . DIRECTORY_SEPARATOR . 'framework';
        $paths[] = $application_path_temp . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'private';
        $paths[] = $application_path_temp . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'vendor';
        set_include_path(implode(PATH_SEPARATOR, $paths));
        // autoloader
        spl_autoload_register(array('Application', 'autoloader'));
        // support functions
        require_once('Functions.php');
        // load ini settings
        self::$settings['application']['libraries_folder'] = $application_path_temp . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
        self::$settings['application']['application_folder'] = $application_path;
        self::$settings['application']['root_folder'] = $root_path;
        self::$settings = Config::load($ini_folder, [
            'libraries_folder' => self::$settings['application']['libraries_folder'],
            'application_folder' => self::$settings['application']['application_folder'],
            'root_folder' => self::$settings['application']['root_folder'],
        ]);
        // additional ini settings
        if (!empty($options['__ini_additional_settings'])) {
            foreach ($options['__ini_additional_settings'] as $k => $v) {
                self::set($k, $v);
            }
        }
        // request uri
        $request_uri = $options['request_uri'] ?? $_SERVER['REQUEST_URI'] ?? '';
        self::$settings['application']['request_uri'] = $request_uri;
        if (strpos($request_uri, '?') !== false) {
            $parsed = parse_url($request_uri);
            $request_uri = $parsed['path'] ?? '/';
        }
        // template
        $template = Application::get('application.template');
        if (!empty($request_uri) && !empty($template['name'])) {
            $matches = [];
            if (!empty($template['url_path_name'])) {
                preg_match('/\/' . $template['url_path_name'] . '-([A-Za-z0-9]+)\//i', $request_uri, $matches);
                if (!empty($matches[1])) {
                    $template['name'] = strtolower(trim($matches[1]));
                    $available = Application::get('application.template.available');
                    if (!empty($available)) {
                        if (in_array($template['name'], $available)) {
                            Application::set('application.template.name', $template['name']);
                        }
                    } else {
                        Application::set('application.template.name', $template['name']);
                    }
                    $request_uri = str_replace($matches[0], '/', $request_uri);
                }
            } else {
                $available = Application::get('application.template.available');
                $first_fragment = explode('/', $request_uri);
                foreach ($available as $v) {
                    if (ucfirst($v) === $first_fragment[1]) {
                        $template['name'] = strtolower(trim($first_fragment[1]));
                        $request_uri = str_replace('/' . $first_fragment[1] . '/', '/', $request_uri);
                        Application::set('application.template.name', $template['name']);
                        break;
                    }
                }
            }
            // template.ini only if activated in previous files
            if (!empty($template['name'])) {
                $file = $ini_folder . 'template.ini';
                if (file_exists($file)) {
                    $ini_data = Config::ini($file, $template['name'] . '-' . Application::get('environment'));
                    self::$settings = array_merge2(self::$settings, $ini_data);
                }
            }
        }
        // registry last
        if (file_exists($ini_folder . 'registry.ini')) {
            Registry::load($ini_folder . 'registry.ini');
        }
        self::$settings['application']['system']['request_time'] = $application_request_time;
        // server
        if (!empty($request_uri) && !empty(self::$settings['server']['extension']['skip'])) {
            $temp = explode('.', $request_uri);
            $extension = array_pop($temp);
            if (in_array($extension, self::$settings['server']['extension']['skip'])) {
                Request::error(404);
            }
        }
        // special handling of media files for development, so there's no need to redeploy application
        if (self::$settings['environment'] == 'development' && isset($_SERVER['REQUEST_URI'])) {
            Media::serveMediaIfExists($_SERVER['REQUEST_URI'], $application_path);
        }
        // we need to solve chicken and egg problem so we load cache first and then run application
        //cache::create('php', array('type'=>'php', 'dir'=>'../application/cache'));
        self::$settings['application']['name'] = $application_name;
        self::$settings['application']['path'] = $application_path;
        self::$settings['application']['path_full'] = $application_path_full . '/';
        self::$settings['application']['paths'] = $paths;
        self::$settings['application']['loaded_classes'] = []; // class paths
        self::$settings['layout'] = []; // layout settings
        // flags
        self::$settings['flag'] = (isset(self::$settings['flag']) && is_array(self::$settings['flag'])) ? self::$settings['flag'] : [];
        self::$settings['flag']['global']['__run_only_bootstrap'] = !empty($options['__run_only_bootstrap']);
        // magic variables processed here
        self::$settings['flag']['global']['__content_type'] = 'text/html';
        self::processMagicVariables([
            'request_uri' => $request_uri,
        ]);
        // alive for HTML pages
        if (self::$settings['flag']['global']['__content_type'] == 'text/html') {
            Alive::start();
        }
        // processing php settings
        if (isset(self::$settings['php'])) {
            foreach (self::$settings['php'] as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
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
        Base::init();
        // debug after error handler
        Debug::init(self::get('debug'));
        // Bootstrap Class
        $bootstrap = new Bootstrap();
        $bootstrap_methods = get_class_methods($bootstrap);
        foreach ($bootstrap_methods as $method) {
            if (strpos($method, 'init') === 0) {
                call_user_func([$bootstrap, $method], $options);
            }
        }
        // if we are calling application from the command line
        if (!empty($options['__run_only_bootstrap']) && $options['__run_only_bootstrap'] != 2) {
            // dispatch before, in case if we open database connections in there
            if (!empty(self::$settings['application']['dispatch']['before_controller'])) {
                call_user_func(self::$settings['application']['dispatch']['before_controller']);
            }
            return;
        }
        // create request and response objects
        self::$request = new Request();
        self::$response = new Response();
        // processing routes from misc routes
        $request_route = null;
        if (require_if_exists($application_path_full . '/Miscellaneous/Routes/AllRoutes.php')) {
            // all API routes exists in additional file
            require_if_exists($application_path_full . '/Miscellaneous/Routes/APIRoutes.php');
            // math route
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $route = Route::match($request_uri, $method);
            if ($route['success']) {
                if ($route['wrong_method']) {
                    Messages::message('ROUTE_INVALID_METHOD', ['[method]' => $method], true, true);
                }
                $request_uri = $route['request_uri'];
                if ($route['route']->acl['as_controller']) {
                    goto route_load_controller;
                } else {
                    // midleware before ACL
                    if (!Route::checkMiddleware($route['name'], 'Before')) {
                        throw new Exception(loc(Error::ROUTE_MIDDLEWARE_DENIED));
                    }
                    // if we do not have permission we throw exception
                    if (!Route::checkAcl($route['name'])) {
                        Messages::message('ROUTE_PERMISSION_DENIED', null, true, true, -1);
                    }
                    /** @var Route $request_route */
                    $request_route = $route['route'];
                    // we need to put parameters into $_GET
                    if (!empty($request_route->parameters['from_request'])) {
                        self::$request->merge($request_route->parameters['from_request']);
                    }
                    // if its API route we execute
                    if ($route['type'] == 'API') {
                        // initialize route controller to run permissions
                        self::$controller = new Controller\Route();
                        // create new API object
                        $api_controller = Factory::model($request_route->resource[0], true, [['skip_constructor_loading' => false]]);
                        $api_method = $request_route->resource[1];
                        do {
                            $api_result = RESULT_BLANK;
                            // if we have columns we validate by deefault
                            $api_columns = $api_controller->{$request_route->resource[1] . '_columns'} ?? null;
                            if (!empty($api_columns)) {
                                $validator = Validator::validateInputStatic($api_controller->input, $api_columns);
                                if ($validator->hasErrors()) {
                                    $api_result = $validator->errors('result');
                                    break;
                                }
                                $api_controller->columns = $api_columns;
                                $api_controller->values = $validator->values();
                            }
                            // prepare dependency injection
                            $dependency = Reflection::dependencyInjectionParameters($api_controller, $api_method, $api_controller->values);
                            if (!$dependency['success']) {
                                $api_result = $dependency;
                                break;
                            }
                            // execute method
                            try {
                                $api_result = call_user_func_array([$api_controller, $api_method], $dependency['data']);
                            } catch (Exception $e) {
                                $api_result['error'][] = $e->getMessage();
                                $api_result['http_status_code'] = HTTPConstants::Status500InternalServerError;
                            }
                        } while (0);
                        // handle output
                        $api_controller->handleOutput($api_result);
                    }
                }
            }
        }
        // if we run special bootstrap we exist here
        if (!empty($options['__run_only_bootstrap']) && $options['__run_only_bootstrap'] == 2) {
            return;
        }
        // processing mvc settings
        route_load_controller:
                Front::setMvc($request_uri);
        // check if controller exists
        if (!file_exists(self::$settings['mvc']['controller_file']) && (!$request_route || $request_route->resource != 'callable')) {
            trigger_error('Resource not found [' . self::$settings['mvc']['controller_file'] . ']!');
            throw new ResourseNotFoundException('Resource not found!', -1);
        }
        // initialize the controller
        $controller_class = self::$settings['mvc']['controller_class'];
        self::$controller = new $controller_class();
        self::$controller->route = $request_route;
        // put action into controller
        self::$controller->action_code = self::$settings['mvc']['controller_action_code'];
        self::$controller->action_method = self::$settings['mvc']['controller_action'];
        // process controller middleware
        if (!self::$controller->checkMiddleware('Before')) {
            throw new Exception(loc(Error::ROUTE_MIDDLEWARE_DENIED));
        }
        // forcing people to do things
        if (!empty($_SESSION['numbers']['force'])) {
            $already = false;
            foreach ($_SESSION['numbers']['force'] as $k => $v) {
                if ($v['controller'] == Application::get(['mvc', 'full'])) {
                    $already = $v;
                    break;
                }
            }
            $next = current($_SESSION['numbers']['force']);
            if (empty($already) && !Application::get('flag.global.__ajax') && !Cmd::isCli() && empty(self::$controller->skip_monitoring)) {
                Request::redirect($next['controller']);
            } elseif (!empty($already)) {
                Layout::addMessage($already['message'], DANGER);
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
            if (Lock::process($lock_id) === false) {
                throw new Exception($message);
            }
        }
        // process parameters and provide output
        self::process();
        // release singleton lock
        if (self::$controller->singleton_flag) {
            Lock::release($lock_id);
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
    public static function autoloader($class)
    {
        $class = ltrim($class, '\\');
        if (class_exists($class, false) || interface_exists($class, false) || strpos($class, 'SkipMeNow') === 0) {
            return;
        }
        $whitelisted = ['Memcached', 'PHPUnit', 'Symfony'];
        foreach ($whitelisted as $v) {
            if (strpos($class, $v) === 0) {
                return;
            }
        }
        // some classes cannot have namespaces
        if (strpos($class, 'Object') === 0 && strpos($class, '_') !== false) {
            $temp = explode('_', $class);
            array_pop($temp);
            $file = implode(DIRECTORY_SEPARATOR, $temp) . DIRECTORY_SEPARATOR . $class . '.php';
            goto load;
        }
        // we need to check if we have customization for classes, we only allow
        // customizaton for models and controllers
        $file = str_replace(['_', '\\'], DIRECTORY_SEPARATOR, $class) . '.php';
        if (strpos($file, 'Numbers') === 0) {
            $temp = explode(DIRECTORY_SEPARATOR, $file);
            $temp[0] = strtolower($temp[0]);
            $temp[1] = strtolower($temp[1]);
            if (in_array($temp[1], ['backend', 'communication', 'countries', 'documentation', 'framework', 'frontend', 'internalization', 'services', 'tenants', 'users'])) {
                $file = implode(DIRECTORY_SEPARATOR, $temp);
            }
        }
        load:
                // we need to store class path so we can load js, css and scss files
                self::$settings['application']['loaded_classes'][$class] = [
                    'class' => $class,
                    'file' => $file,
                    'media' => []
                ];
        // debuging
        if (class_exists('Debug', false) && Debug::$debug) {
            Debug::$data['classes'][] = ['class' => $class, 'file' => $file];
        }
        require_once($file);
    }

    /*
     * Processing and generating layout
     *
     * @return string
     */
    public static function process($options = [])
    {
        // start buffering
        Ob::start(true);
        $controller_class = self::$settings['mvc']['controller_class'];
        // put action into controller
        self::$controller->action_code = self::$settings['mvc']['controller_action_code'];
        self::$controller->action_method = self::$settings['mvc']['controller_action'];
        // processing options
        if (!empty($options)) {
            foreach ($options as $k => $v) {
                self::$controller->{$k} = $v;
            }
        }
        // check ACL
        if ($controller_class != Errors::class) {
            if (!self::$controller->permitted(['redirect' => true])) {
                throw new PermissionException('Permission denied!', -1);
            }
        }
        // auto populating input property in controller
        if (!empty(self::$settings['application']['controller']['input'])) {
            self::$controller->input = Request::input(null, true, true);
        }
        // init method
        if (method_exists(self::$controller, 'init')) {
            call_user_func(array(self::$controller, 'init'));
        }
        // check if action exists
        if (!method_exists(self::$controller, self::$controller->action_method)) {
            throw new ResourseNotFoundException('Action does not exists: ' . self::$controller->action_method . ', Controller: ' . get_class(self::$controller) . '!');
        }
        // calling action
        Log::add([
            'type' => 'System',
            'only_chanel' => 'default',
            'message' => 'Calling controller!',
            'other' => 'Controller title: ' . self::$controller->title,
        ]);
        // process dependency for controller actions
        $dependency = Reflection::dependencyInjectionParameters(self::$controller, self::$controller->action_method, []);
        echo call_user_func_array(array(self::$controller, self::$controller->action_method), $dependency['data']);
        // auto rendering view only if view exists, processing extension order as specified in .ini file
        $controller_dir = Application::get('old.controller.dir');
        if (!$controller_dir) {
            $temp_reflection_obj = new ReflectionClass(Application::$controller);
            $controller_dir = pathinfo($temp_reflection_obj->getFileName(), PATHINFO_DIRNAME) . '/';
        }
        $controller_file = end(self::$settings['mvc']['controllers']);
        $view = self::$settings['mvc']['controller_view'];
        $flag_view_found = false;
        if (!empty($view)) {
            $extensions = explode(',', self::$settings['application']['view']['extension'] ?? 'html');
            foreach ($extensions as $extension) {
                $file = $controller_dir  . $controller_file . '.' . $view . '.' . $extension;
                if (file_exists($file)) {
                    $flag_view_found = true;
                    break;
                }
            }
            // if views are mandatory
            if (!empty(self::$settings['application']['view']['mandatory']) && !$flag_view_found) {
                throw new Exception('View ' . $view . ' does not exists!');
            }
        }
        $view_html = null;
        if ($flag_view_found) {
            Ob::start();
            $view_object = new View(self::$controller, $file, $extension);
            $view_html = Ob::clean();
        }
        // autoloading media files
        Layout::includeMedia($controller_dir, $controller_file, $view, $controller_class);
        // appending view after controllers output
        self::$controller->data = self::$controller->data ?? new stdClass();
        self::$controller->data->view = (self::$controller->data->view ?? '') . Ob::clean();
        if (isset($view_html) && str_contains($view_html, '<!-- [numbers: controller content] -->')) {
            self::$controller->data->view = str_replace('<!-- [numbers: controller content] -->', self::$controller->data->view, $view_html);
        }
        // if we have to render debug toolbar
        if (Debug::$toolbar) {
            Ob::start();
        }
        // call pre rendering method in bootstrap
        Bootstrap::preRender();
        // rendering layout
        $__skip_layout = self::get('flag.global.__skip_layout');
        // if
        if ($__skip_layout == 'no_menu') {
            self::$settings['mvc']['controller_layout_file'] = Application::get(['application', 'path_full']) . 'Layout/' . self::get('application.layout.layout') . '_' . $__skip_layout . '.' . self::get('application.layout.extension');
            $__skip_layout = false;
        }
        if (!empty(self::$settings['mvc']['controller_layout']) && empty($__skip_layout)) {
            Ob::start();
            if (file_exists(self::$settings['mvc']['controller_layout_file'])) {
                $layout_object = new Layout(self::$controller, self::$settings['mvc']['controller_layout_file'], self::$settings['mvc']['controller_layout_extension']);
            }
            // session expiry dialog before replaces
            Session::expiryDialog();
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
                '<!-- [numbers: workflows] -->',
                '<!-- [numbers: layout footer] -->'
            ];
            $to = [
                Layout::renderMessages(),
                Layout::renderTitle(),
                Layout::renderDocumentTitle(),
                Layout::renderActions(),
                Layout::renderBreadcrumbs(),
                Layout::renderJs(),
                Layout::renderJsData(),
                Layout::renderCss(),
                Layout::renderOnload(),
                Layout::$onhtml,
                Object\Form\Workflow\Base::render(),
                Controller::renderFooter(),
            ];
            echo str_replace($from, $to, Ob::clean());
        } else {
            echo self::$controller->data->view;
        }
        // headers
        if (!empty(self::$settings['header']) && !headers_sent()) {
            foreach (self::$settings['header'] as $k => $v) {
                header($v);
            }
        }
        // ajax calls that has not been processed by application
        if (self::get('flag.global.__ajax')) {
            Layout::renderAs(['success' => false, 'error' => [\I18n(null, 'Could not process ajax call!')]], 'application/json');
        }
    }

    /**
     * Changing view or layout
     *
     * @param string $what [layout,view]
     * @param string $how
     */
    public static function change($what, $how)
    {
        switch ($what) {
            case 'layout':
                self::$settings['mvc']['controller_layout'] = $how;
                self::$settings['mvc']['controller_layout_file'] = Application::get(['application', 'path_full']) . 'Layout/' . $how . '.html';
                break;
            case 'view':
                self::$settings['mvc']['controller_view'] = $how;
                break;
        }
    }

    /**
     * Process magic variables
     *
     * @param array $options
     * 		string request_uri
     */
    public static function processMagicVariables(array $options = [])
    {
        $variables_object = new Variables();
        $variables = $variables_object->get();
        $input = Request::input(null, true, true);
        foreach ($variables as $k => $v) {
            if (!array_key_exists($k, $input)) {
                self::$settings['flag']['global'][$k] = null;
                continue;
            }
            if ($k == '__content_type') {
                $object = new Types();
                $data = $object->get();
                if (isset($data[$input[$k]])) {
                    self::$settings['flag']['global'][$k] = $input[$k];
                }
            } else {
                self::$settings['flag']['global'][$k] = $input[$k];
            }
        }
        // other
        $headers = getallheaders();
        self::$settings['flag']['global']['__accept'] = $headers['Content-Type'] ?? null;
        if (!isset(self::$settings['flag']['global']['__accept'])) {
            $accept = $headers['Accept'] ?? null;
            if (isset($accept)) {
                self::$settings['flag']['global']['__accept'] = explode(',', $accept)[0];
            }
        }
        // is api
        self::$settings['flag']['global']['__is_api'] = false;
        if (str_starts_with($options['request_uri'] ?? '', '/API/')) {
            self::$settings['flag']['global']['__is_api'] = true;
        }
        // authorization and bearer token
        self::$settings['flag']['global']['__bearer_token'] = null;
        self::$settings['flag']['global']['__authorization'] = null;
        if (!empty($headers['Authorization'])) {
            self::$settings['flag']['global']['__authorization'] = trim($headers['Authorization']);
            if (str_starts_with($headers['Authorization'], 'Bearer ')) {
                self::$settings['flag']['global']['__bearer_token'] = trim(str_replace('Bearer ', '', $headers['Authorization']));
            }
        }
        // iframe
        if (($headers['Sec-Fetch-Dest'] ?? '') == 'iframe') {
            self::$settings['flag']['global']['__skip_layout'] = 'no_menu';
        }
    }

    /**
     * Check if application has been deployed
     *
     * @return boolean
     */
    public static function isDeployed()
    {
        return (strpos(__FILE__, '/deployments/build.') !== false);
    }
}
