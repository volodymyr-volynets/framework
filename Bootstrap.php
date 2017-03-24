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
			Format::init();
			return;
		}
		// get flags & dependencies
		$flags = Application::get('flag');
		$backend = Application::get('Numbers.Backend', ['backend_exists' => true]);
		// initialize cryptography
		$crypt = Application::get('crypt');
		if (!empty($crypt) && $backend) {
			foreach ($crypt as $crypt_link => $crypt_settings) {
				if (!empty($crypt_settings['submodule']) && !empty($crypt_settings['autoconnect'])) {
					$crypt_object = new crypt($crypt_link, $crypt_settings['submodule'], $crypt_settings);
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
					Factory::submodule('flag.global.library.' . $k . '.submodule')->add();
				}
			}
		}
		// check if we need to include system files from frontend
		if (Application::get('dep.submodule.numbers.frontend.system')) {
			numbers_frontend_system_model_base::start();
		}
		// application structure
		$application_structure_model = Application::get('application.structure.model');
		if (!empty($application_structure_model)) {
			Factory::model($application_structure_model, true)->settings();
		}
		$application_structure = Application::get('application.structure');
		// create database connections
		$db = Application::get('db');
		if (!empty($db) && $backend) {
			foreach ($db as $db_link => $db_settings) {
				if (empty($db_settings['autoconnect']) || empty($db_settings['servers']) || empty($db_settings['submodule'])) continue;
				$connected = false;
				$db_options = $db_settings;
				unset($db_options['servers']);
				foreach ($db_settings['servers'] as $server_key => $server_values) {
					$db_object = new db($db_link, $db_settings['submodule'], $db_options);
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
						request::redirect($application_structure['db_not_found_url']);
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
				$cache_result = cache::connect_to_servers($cache_link, $cache_settings);
				if (!$cache_result['success']) {
					Throw new Exception(implode(', ', $cache_result['error']));
				}
			}
		}
		// initialize session
		$session = Application::get('flag.global.session');
		if (!empty($session['start']) && $backend && !Application::get('flag.global.__skip_session')) {
			Session::start($session['options'] ?? []);
		}
		// load tenant
		if (!empty($application_structure_model)) {
			Factory::model($application_structure_model, true)->tenant();
		}
		// we need to get overrides from session and put them back to flag array
		$flags = array_merge_hard($flags, Session::get('numbers.flag'));
		Application::set('flag', $flags);
		// initialize i18n
		if ($backend) {
			$temp_result = I18n::init();
			if (!$temp_result['success']) {
				Throw new Exception('Could not initialize i18n.');
			}
		}
		// format
		Format::init();
		// default actions
		Layout::add_action('refresh', ['value' => 'Refresh', 'icon' => 'refresh', 'onclick' => 'location.reload();', 'order' => -32000]);
		Layout::add_action('print', ['value' => 'Print', 'icon' => 'print', 'onclick' => 'window.print();', 'order' => -31000]);
	}

	/**
	 * Pre render processing
	 */
	public static function preRender() {
		$crypt_class = new crypt();
		$token = urldecode($crypt_class->token_create('general'));
		Layout::js_data([
			'token' => $token, // generating token to receive data from frontend
			'controller_full' => Application::get(['mvc', 'full']), // full controller path
			// flags set in configuration files
			'flag' => [
				'global' => [
					'format' => Format::$options // format options
				]
			],
			// domains
			'\Object\Data\Domains' => [
				'data' => \Object\Data\Domains::get_static()
			]
		]);
	}

	/**
	 * Destroy everything
	 */
	public static function destroy() {
		$__run_only_bootstrap = Application::get(['flag', 'global', '__run_only_bootstrap']);
		// we need to set working directory again
		chdir(Application::get(['application', 'path_full']));
		// error processing
		if (empty(\Object\Error\Base::$flag_error_already)) {
			$last_error = error_get_last();
			$flag_render = false;
			if (in_array($last_error['type'], [E_COMPILE_ERROR, E_PARSE, E_ERROR])) {
				\Object\Error\Base::error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
				\Object\Error\Base::$flag_error_already = true;
				$flag_render = true;
			}
			if ($flag_render || \Object\Error\Base::$flag_exception) {
				\Object\Error\Base::$flag_error_already = true;
				if ($__run_only_bootstrap) {
					\Helper\Ob::cleanAll();
					print_r(\Object\Error\Base::$errors);
				} else {
					// set mvc + process
					Application::set_mvc('/error/_error/500');
					Application::$controller = new controller_error();
					Application::process();
				}
			}
		}
		// write sessions
		session_write_close();
		// final benchmark
		if (debug::$debug) {
			debug::benchmark('application end');
		}
		// debugging toolbar last
		if (debug::$toolbar && !$__run_only_bootstrap) {
			echo str_replace('<!-- [numbers: debug toolbar] -->', debug::render(), Helper_Ob::clean());
		}
		// flush data to client
		flush();
		// closing caches before db
		$cache = Factory::get(['cache']);
		if (!empty($cache)) {
			foreach ($cache as $k => $v) {
				if (!empty(cache::$reset_caches[$k])) {
					$v['object']->gc(3, cache::$reset_caches[$k]);
				}
				$v['object']->close();
			}
		}
		// destroy i18n
		if (I18n::$initialized) {
			I18n::destroy();
		}
		// close db connections
		$dbs = Factory::get(['db']);
		if (!empty($dbs)) {
			foreach ($dbs as $k => $v) {
				$v['object']->close();
			}
		}
		// emails with erros
		if (debug::$debug && !empty(debug::$email) && Application::get('Numbers.Backend', ['backend_exists' => true]) && Application::get('Numbers.Frontend', ['backend_exists' => true])) {
			debug::sendErrorsToAdmin();
		}
	}
}