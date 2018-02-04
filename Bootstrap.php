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
		if (!empty($options['__run_only_bootstrap'])) {
			// format
			\Format::init();
			return;
		}
		// get flags & dependencies
		$flags = \Application::get('flag');
		$backend = \Application::get('Numbers.Backend', ['backend_exists' => true]);
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
		$db = Application::get('db');
		if (!empty($db) && $backend) {
			foreach ($db as $db_link => $db_settings) {
				if (empty($db_settings['autoconnect']) || empty($db_settings['servers']) || empty($db_settings['submodule'])) continue;
				$connected = false;
				$db_options = $db_settings;
				unset($db_options['servers']);
				foreach ($db_settings['servers'] as $server_key => $server_values) {
					$db_object = new \Db($db_link, $db_settings['submodule'], $db_options);
					// application structure
					if (isset($application_structure['settings']['db'][$db_link])) {
						$server_values = array_merge_hard($server_values, $application_structure['settings']['db'][$db_link]);
					}
					// connecting
					$server_values = array_merge2($server_values, $db_settings);
					$db_status = $db_object->connect($server_values);
					if ($db_status['success'] && $db_status['status']) {
						$connected = true;
						break;
					}
				}
				// checking if not connected
				if (!$connected) {
					// if wrong database name is provided we redirect to special url
					if (!empty($application_structure['db_not_found_url']) && isset($application_structure['settings']['db'][$db_link])) {
						\Request::redirect($application_structure['db_not_found_url']);
					} else {
						Throw new Exception('Unable to open database connection!');
					}
				}
			}
		}
		// initialize caches
		$cache = Application::get('cache');
		if (!empty($cache) && $backend) {
			foreach ($cache as $cache_link => $cache_settings) {
				if (empty($cache_settings['submodule']) || empty($cache_settings['autoconnect'])) continue;
				$cache_result = \Cache::connectToServers($cache_link, $cache_settings);
				if (!$cache_result['success']) {
					Throw new Exception(implode(', ', $cache_result['error']));
				}
			}
		}
		// initialize session
		$session = \Application::get('flag.global.session');
		if (!empty($session['start']) && $backend && !\Application::get('flag.global.__skip_session')) {
			\Session::start($session['options'] ?? []);
		}
		// load tenant
		if (!empty($application_structure_model) && !empty($application_structure['tenant_multiple'])) {
			\Factory::model($application_structure_model, true)->tenant();
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
		}
		// format
		\Format::init();
		// default actions
		\Layout::addAction('refresh', ['value' => 'Refresh', 'icon' => 'fas fa-sync', 'onclick' => 'location.reload();', 'order' => -32000]);
		\Layout::addAction('print', ['value' => 'Print', 'icon' => 'fas fa-print', 'onclick' => 'window.print();', 'order' => -31000]);
	}

	/**
	 * Pre render processing
	 */
	public static function preRender() {
		$crypt_class = new Crypt();
		$token = urldecode($crypt_class->tokenCreate(\User::id(), 'general'));
		\Layout::jsData([
			'token' => $token, // generating token to receive data from frontend
			'controller_full' => \Application::get(['mvc', 'full']), // full controller path
			'host' => \Request::host(),
			'ws_host' => \Request::host(['protocol' => 'ws', 'port' => \Application::get('websocket.port') ?? 9000, 'mvc' => '/ws']),
			'user_id' => \User::id(),
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
		$__run_only_bootstrap = \Application::get('flag.global.__run_only_bootstrap');
		// we need to set working directory again
		chdir(\Application::get('application.path_full'));
		// error processing
		if (empty(\Object\Error\Base::$flag_error_already)) {
			$last_error = error_get_last();
			$flag_render = false;
			if (in_array($last_error['type'], [E_COMPILE_ERROR, E_PARSE, E_ERROR])) {
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
		// write sessions
		session_write_close();
		// final benchmark
		if (\Debug::$debug) {
			\Debug::benchmark('application end');
		}
		// debugging toolbar last
		if (\Debug::$toolbar && !$__run_only_bootstrap) {
			echo str_replace('<!-- [numbers: debug toolbar] -->', \Debug::render(), \Helper\Ob::clean());
		}
		// flush data to client
		flush();
		// closing caches before db
		$cache = \Factory::get(['cache']);
		if (!empty($cache)) {
			foreach ($cache as $k => $v) {
				if (!empty(cache::$reset_caches[$k])) {
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
		// close db connections
		$dbs = \Factory::get(['db']);
		if (!empty($dbs)) {
			foreach ($dbs as $k => $v) {
				$v['object']->close();
			}
		}
		// emails with erros
		if (\Debug::$debug && !empty(\Debug::$email) && \Application::get('Numbers.Backend', ['backend_exists' => true]) && \Application::get('Numbers.Frontend', ['backend_exists' => true])) {
			\Debug::sendErrorsToAdmin();
		}
	}
}