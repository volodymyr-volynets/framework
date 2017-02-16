<?php

/**
 * Bootstrap Class have all methods called in order they are defined. Methods that are definined
 * starting with init are called at the beginning of execution, methods that start with destroy
 * are called at the end.
 *  
 */
class bootstrap {

	/**
	 * Initialize db connections, cache and session
	 */
	public static function init($options = []) {
		// initialize mbstring
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');
		// get flags & dependencies
		$flags = application::get('flag');
		$backend = application::get('numbers.backend', ['backend_exists' => true]);
		// initialize cryptography
		$crypt = application::get('crypt');
		if (!empty($crypt) && $backend) {
			foreach ($crypt as $crypt_link => $crypt_settings) {
				if (!empty($crypt_settings['submodule']) && !empty($crypt_settings['autoconnect'])) {
					$crypt_object = new crypt($crypt_link, $crypt_settings['submodule'], $crypt_settings);
				}
			}
		}
		// if we are from command line we exit here
		if (!empty($options['__run_only_bootstrap'])) {
			// format
			format::init();
			return;
		}
		// including libraries that we need to auto include
		if (!empty($flags['global']['library'])) {
			foreach ($flags['global']['library'] as $k => $v) {
				// we need to skip certain keys
				if ($k == 'submodule' || $k == 'options') continue;
				// we only include if autoconnect is on
				if (!empty($v['autoconnect'])) {
					factory::submodule('flag.global.library.' . $k . '.submodule')->add();
				}
			}
		}
		// check if we need to include system files from frontend
		if (application::get('dep.submodule.numbers.frontend.system')) {
			numbers_frontend_system_model_base::start();
		}
		// application structure
		$application_structure_model = application::get('application.structure.model');
		if (!empty($application_structure_model)) {
			factory::model($application_structure_model, true)->settings();
		}
		$application_structure = application::get('application.structure');
		// create database connections
		$db = application::get('db');
		if (!empty($db) && $backend) {
			foreach ($db as $db_link => $db_settings) {
				if (empty($db_settings['autoconnect']) || empty($db_settings['servers']) || empty($db_settings['submodule'])) {
					continue;
				}
				$connected = false;
				foreach ($db_settings['servers'] as $server_key => $server_values) {
					$db_object = new db($db_link, $db_settings['submodule']);
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
		// initialize cache
		$cache = application::get('cache');
		if (!empty($cache) && $backend) {
			foreach ($cache as $cache_link => $cache_settings) {
				if (empty($cache_settings['submodule']) || empty($cache_settings['autoconnect'])) {
					continue;
				}
				$connected = false;
				foreach ($cache_settings['servers'] as $cache_server) {
					$cache_object = new cache($cache_link, $cache_settings['submodule'], $cache_settings);
					$cache_status = $cache_object->connect($cache_server);
					if ($cache_status['success']) {
						$connected = true;
						break;
					}
				}
				// checking if not connected
				if (!$connected) {
					Throw new Exception('Unable to open cache connection!');
				}
			}
		}
		// initialize session
		$session = application::get('flag.global.session');
		if (!empty($session['start']) && $backend && !application::get('flag.global.__skip_session')) {
			session::start(isset($session['options']) ? $session['options'] : []);
		}
		// we need to get overrides from session and put them back to flag array
		$flags = array_merge_hard($flags, session::get('numbers.flag'));
		application::set('flag', $flags);
		// initialize i18n
		if ($backend) {
			$temp_result = i18n::init();
			if (!$temp_result['success']) {
				Throw new Exception('Could not initialize i18n.');
			}
		}
		// format
		format::init();
	}

	/**
	 * Pre render processing
	 */
	public static function pre_render() {
		$crypt_class = new crypt();
		$token = urldecode($crypt_class->token_create('general'));
		layout::js_data([
			'token' => $token, // generating token to receive data from frontend
			'controller_full' => application::get(['mvc', 'full']), // full controller path
			// flags set in configuration files
			'flag' => [
				'global' => [
					'format' => format::$options // format options
				]
			],
			// domains
			'object_data_domains' => [
				'data' => object_data_domains::get_static()
			]
		]);
	}

	/**
	 * Destroy everything
	 */
	public static function destroy() {
		$__run_only_bootstrap = application::get(['flag', 'global', '__run_only_bootstrap']);
		// we need to set working directory again
		chdir(application::get(['application', 'path_full']));
		// error processing
		if (empty(error_base::$flag_error_already)) {
			$last_error = error_get_last();
			$flag_render = false;
			if (in_array($last_error['type'], [E_COMPILE_ERROR, E_PARSE, E_ERROR])) {
				error_base::error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
				error_base::$flag_error_already = true;
				$flag_render = true;
			}
			if ($flag_render || error_base::$flag_exception) {
				error_base::$flag_error_already = true;
				if ($__run_only_bootstrap) {
					helper_ob::clean_all();
					print_r(error_base::$errors);
				} else {
					// set mvc + process
					application::set_mvc('/error/_error/500');
					application::process();
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
			echo str_replace('<!-- [numbers: debug toolbar] -->', debug::render(), helper_ob::clean());
		}
		// flush data to client
		flush();
		// closing caches before db
		$cache = factory::get(['cache']);
		if (!empty($cache)) {
			foreach ($cache as $k => $v) {
				$object = $v['object'];
				if (!empty(cache::$reset_caches[$k])) {
					$object->gc(1, cache::$reset_caches[$k]);
				}
				$object->close();
			}
		}
		// destroy i18n
		if (i18n::$initialized) {
			i18n::destroy();
		}
		// close db connections
		$dbs = factory::get(['db']);
		if (!empty($dbs)) {
			foreach ($dbs as $k => $v) {
				$object = $v['object'];
				$object->close();
			}
		}
		// emails with erros
		if (debug::$debug && !empty(debug::$email)) {
			debug::send_errors_to_admin();
		}
	}
}