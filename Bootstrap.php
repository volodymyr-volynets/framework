<?php

class Bootstrap {

	/**
	 * Initialize db connections, cache and session
	 */
	public static function init($options = []) {
		// initialize mbstring
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');
		// if we are from command line we exit here
		if (!empty($options['__run_only_bootstrap']) && $options['__run_only_bootstrap'] == 1) {
			// format
			\Format::init();
			return;
		}
		// enforcing https
		$enforce_https = \Application::get('application.https.enforce');
		if (!empty($enforce_https)) {
			if (!\Request::isSSL()) {
				$url = \Request::host(['protocol' => 'https', 'request' => true]);
				\Request::redirect($url);
			}
		}
		// get flags & backend
		$flags = \Application::get('flag');
		$backend = \Application::get('numbers.backend', ['backend_exists' => true]);
		// alive
		if (!empty($flags['alive']['autoconnect'])) {
			\Alive::start();
		}
		// initialize cryptography
		$crypt = \Application::get('crypt');
		if (!empty($crypt) && $backend) {
			foreach ($crypt as $crypt_link => $crypt_settings) {
				if (!empty($crypt_settings['submodule']) && !empty($crypt_settings['autoconnect'])) {
					$crypt_object = new \Crypt($crypt_link, $crypt_settings['submodule'], $crypt_settings);
				}
			}
		}
		// including libraries that we need to auto include
		if (!empty($flags['global']['library'])) {
			foreach ($flags['global']['library'] as $k => $v) {
				// we need to skip certain keys
				if ($k == 'submodule' || $k == 'options') continue;
				// we only include if autoconnect is on
				if (!empty($v['autoconnect'])) {
					\Factory::submodule('flag.global.library.' . $k . '.submodule')->add();
				}
			}
		}
		// check if we need to include system files from frontend
		if (\Application::get('dep.submodule.Numbers.Frontend.System')) {
			\Numbers\Frontend\System\Model\Base::start();
		}
		// application structure
		$application_structure_model = \Application::get('application.structure.model');
		if (!empty($application_structure_model)) {
			\Factory::model($application_structure_model, true)->settings();
		}
		$application_structure = \Application::get('application.structure');
		// create database connections
		$db = \Application::get('db');
		if (!empty($db) && $backend) {
			$db_links = [];
			foreach ($db as $db_link => $db_settings) {
				if (empty($db_settings['autoconnect']) || empty($db_settings['servers']) || empty($db_settings['submodule'])) continue;
				// establish connection
				$db_result = \Db::connectToServers($db_link, $db_settings);
				// checking if not connected
				if (!$db_result['success']) {
					// if wrong database name is provided we redirect to special url
					if (!empty($application_structure['db_not_found_url']) && isset($application_structure['settings']['db'][$db_link])) {
						\Request::redirect($application_structure['db_not_found_url']);
					} else {
						Throw new \Exception('Unable to open database connection!');
					}
				} else if ($db_link == 'default') {
					\Object\Error\Base::$flag_database_default_initiated = true;
				}
				$db_links[] = $db_link;
			}
			\Log::add([
				'type' => 'System',
				'only_chanel' => 'default',
				'message' => 'Initialized database(s): ' . implode(', ', $db_links),
			]);
		}
		// initialize logs
		$log = \Application::get('log');
		unset($log['settings']); // special key for slow query and request
		if (!empty($log) && $backend) {
			$log_links = [];
			foreach ($log as $log_link => $log_settings) {
				if (empty($log_settings['autoconnect'])) {
					continue;
				}
				$log_model = new \Log($log_link, $log_settings['submodule'], $log_settings);
				$log_links[]= $log_link;
			}
			\Log::add([
				'type' => 'System',
				'only_chanel' => 'default',
				'message' => 'Initialized log(s): ' . implode(', ', $log_links),
			]);
		}
		// initialize caches
		$cache = \Application::get('cache');
		if (!empty($cache) && $backend) {
			$cache_links = [];
			foreach ($cache as $cache_link => $cache_settings) {
				if (empty($cache_settings['submodule']) || empty($cache_settings['autoconnect'])) continue;
				$cache_result = \Cache::connectToServers($cache_link, $cache_settings);
				if (!$cache_result['success']) {
					Throw new \Exception(implode(', ', $cache_result['error']));
				}
				$cache_links[] = $cache_link;
			}
			\Log::add([
				'type' => 'System',
				'only_chanel' => 'default',
				'message' => 'Initialized caches(s): ' . implode(', ', $cache_links),
			]);
		}
		// initialize session
		$session = \Application::get('flag.global.session');
		$skip_session = \Application::get('flag.global.__skip_session') ?? false;
		if (\Application::get('flag.global.__is_api')) {
			$skip_session = true;
			$bearer_token = \Application::get('flag.global.__bearer_token');
			$crypt = new \Crypt();
			if ($bearer_token && $crypt->bearerAuthorizationTokenValidate($bearer_token)) {
				\Application::set('flag.global.__session_id', $crypt->bearerAuthorizationTokenDecode($bearer_token)['session_id']);
				$skip_session = false;
			}
		}
		if (!empty($session['start']) && $backend && !$skip_session) {
			\Session::start($session['options'] ?? []);
			\Log::add([
				'type' => 'System',
				'only_chanel' => 'default',
				'message' => 'Initialized session: ' . session_id(),
			]);
		}
		// load tenant
		if (!empty($application_structure_model) && !empty($application_structure['tenant_multiple'])) {
			\Factory::model($application_structure_model, true)->tenant();
			\Log::add([
				'type' => 'System',
				'only_chanel' => 'default',
				'message' => 'Initialized tenant: ' . \Tenant::id(),
			]);
		}
		// we need to get overrides from session and put them back to flag array
		$flags = array_merge_hard($flags, \Session::get('numbers.flag'));
		\Application::set('flag', $flags);
		// custom destroy methods
		$temp = \Object\ACL\Resources::getStatic('initialize');
		if (!empty($temp)) {
			foreach ($temp as $v) {
				$method = \Factory::method($v['method'], null, true);
				call_user_func_array($method, []);
			}
		}
		// initialize i18n
		if ($backend) {
			$temp_result = \I18n::init();
			if (!$temp_result['success']) {
				Throw new \Exception('Could not initialize i18n.');
			}
			\Log::add([
				'type' => 'System',
				'only_chanel' => 'default',
				'message' => 'Initialized I18n!',
			]);
		}
		// format & html
		\Format::init();
		\HTML::init();
		// default actions
		\Layout::addAction('refresh', ['value' => 'Refresh', 'icon' => 'fas fa-sync', 'onclick' => 'location.reload();', 'order' => -32000]);
		\Layout::addAction('print', ['value' => 'Print', 'icon' => 'fas fa-print', 'onclick' => 'window.print();', 'order' => -31000]);
		// include constants
		require('Constants.php');
		// And we need to check firewall.
		$firewalls = \Object\ACL\Resources::getStatic('firewalls', 'primary');
		if (!empty($firewalls)) {
			$ips = call_user_func_array($firewalls['list'], []);
			if (in_array(\Request::ip(), $ips)) {
				\Debug::$firewall = true;
				header('HTTP/1.1 403');
				echo 'Forbidden';
				exit;
			}
			// domain rules
			$domains = \Application::get('firewalls.primary.rules.domains');
			if (!empty($domains)) {
				if (!\Request::urlWhitelisted(\Request::host(), $domains)) {
					\Debug::$firewall = true;
					header('HTTP/1.1 403');
					echo 'Forbidden';
					exit;
				}
			}
		}
	}

	/**
	 * Pre render processing
	 */
	public static function preRender() {
		$crypt_class = new Crypt();
		$token = urldecode($crypt_class->tokenCreate(\User::getUser() ?? \User::id(), 'general'));
		\Layout::jsData([
			'token' => $token, // generating token to receive data from frontend
			'controller_full' => \Application::get(['mvc', 'full']), // full controller path
			'host' => \Request::host(),
			'ws_host' => \Request::host(['protocol' => 'ws', 'port' => \Application::get('websocket.port') ?? 9000, 'mvc' => '/ws']),
			'user_id' => \User::getUser() ?? \User::id(),
			// flags set in configuration files
			'flag' => [
				'global' => [
					'format' => \Format::$options // format options
				]
			],
		]);
	}

	/**
	 * Destroy everything
	 */
	public static function destroy() {
		// we need to stop alive
		\Alive::stop();
		// if we are in bootsrap mode
		$__run_only_bootstrap = \Application::get('flag.global.__run_only_bootstrap');
		// we need to set working directory again
		chdir(\Application::get('application.path_full'));
		// error processing
		if (empty(\Object\Error\Base::$flag_error_already)) {
			$last_error = error_get_last();
			$flag_render = false;
			if (isset($last_error['type']) && in_array($last_error['type'], [E_COMPILE_ERROR, E_PARSE, E_ERROR])) {
				\Object\Error\Base::errorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
				\Object\Error\Base::$flag_error_already = true;
				$flag_render = true;
			}
			if ($flag_render || \Object\Error\Base::$flag_exception) {
				\Object\Error\Base::$flag_error_already = true;
				if ($__run_only_bootstrap) {
					\Helper\Ob::cleanAll();
					print_r(\Object\Error\Base::$errors);
				} else {
					\Helper\Ob::cleanAll();
					// set mvc + process
					\Object\Error\Base::$flag_database_tenant_not_found = true;
					\Object\Controller\Front::setMvc('/Errors/_Error/500');
					\Application::$controller = new \Controller\Errors();
					\Application::process();
				}
			}
		}
		// final benchmark
		if (\Debug::$debug) {
			\Debug::benchmark('application end');
		}
		// debugging toolbar last
		if (\Debug::$toolbar && !$__run_only_bootstrap) {
			echo str_replace('<!-- [numbers: debug toolbar] -->', \Debug::render() . '', \Helper\Ob::clean() . '');
		}
		// flush data to client
		flush();
		// postponed execution
		if (!empty(\Factory::$postponed_execution)) {
			foreach (\Factory::$postponed_execution as $v) {
				call_user_func_array($v[0], $v[1]);
			}
		}
		// closing caches before db
		$cache = \Factory::get(['cache']);
		if (!empty($cache)) {
			foreach ($cache as $k => $v) {
				if (!empty(\Cache::$reset_caches[$k])) {
					$v['object']->gc(3, cache::$reset_caches[$k]);
				}
				$v['object']->close();
			}
		}
		// destroy i18n
		if (\I18n::$initialized) {
			\I18n::destroy();
		}
		// custom destroy methods
		$temp = \Object\ACL\Resources::getStatic('destroy');
		if (!empty($temp)) {
			foreach ($temp as $v) {
				$method = \Factory::method($v['method'], null, true);
				call_user_func_array($method, []);
			}
		}
		// write sessions
		session_write_close();
		// logs just before db closing
		\Log::add([
			'type' => 'Request',
			'only_chanel' => 'default',
			'message' => 'Request ends.',
			'duration' => microtime(true) - \Application::get('application.system.request_time'),
		]);
		\Log::deliver();
		// close db connections
		$dbs = \Factory::get(['db']);
		if (!empty($dbs)) {
			foreach ($dbs as $k => $v) {
				$v['object']->close();
			}
		}
		// logs
		//\Log::deliver();
		if (\Application::get('debug.log.sql')) {
			\Debug::dumpDbLogs();
		}
		// emails with errors
		if (!empty(\Debug::$email) && \Application::get('numbers.backend', ['backend_exists' => true]) && \Application::get('numbers.frontend', ['backend_exists' => true])) {
			\Debug::sendErrorsToAdmin();
		}
	}
}