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

		// get flags & dependencies
		$flags = application::get('flag');
		$backend = application::get('numbers.backend', ['backend_exists' => true]);

		// processing wildcard first
		$wildcard = application::get('wildcard');
		$wildcard_keys = null;
		if (!empty($wildcard['enabled']) && !empty($wildcard['model'])) {
			$wildcard_keys = call_user_func($wildcard['model']);
			application::set(['wildcard', 'keys'], $wildcard_keys);
		}

		// initialize cryptography
		$crypt = application::get('crypt');
		if (!empty($crypt) && $backend && !empty($flags['global']['crypt']['autoconnect'])) {
			// converting flags to array
			if (!is_array($flags['global']['crypt']['autoconnect'])) {
				$flags['global']['crypt']['autoconnect'] = [$flags['global']['crypt']['autoconnect']];
			}
			// going though all available links
			foreach ($crypt as $crypt_link => $crypt_settings) {
				if (!in_array($crypt_link, $flags['global']['crypt']['autoconnect']) && !in_array('*', $flags['global']['crypt']['autoconnect'])) {
					continue;
				}
				if (!empty($crypt_settings['submodule'])) {
					$crypt_object = new crypt($crypt_link, $crypt_settings['submodule'], $crypt_settings);
				}
			}
		}

		// create database connections
		$db = application::get('db');
		if (!empty($db) && $backend && !empty($flags['global']['db']['autoconnect'])) {
			// converting flags to array
			if (!is_array($flags['global']['db']['autoconnect'])) {
				$flags['global']['db']['autoconnect'] = [$flags['global']['db']['autoconnect']];
			}
			// going though all available links
			foreach ($db as $db_link => $db_settings) {
				if (!in_array($db_link, $flags['global']['db']['autoconnect']) && !in_array('*', $flags['global']['db']['autoconnect'])) {
					continue;
				}
				$connected = false;
				foreach ($db_settings as $server_key => $server_values) {
					if (!empty($server_values['submodule'])) {
						$db_object = new db($db_link, $server_values['submodule']);

						// wildcards replaces
						if ($wildcard_keys !== null) {
							$server_values['dbname'] = $wildcard_keys['dbname'];
						}

						// connecting
						$db_status = $db_object->connect($server_values);
						if ($db_status['success'] && $db_status['status']) {
							$connected = true;
							break;
						}
					}
				}

				// checking if not connected
				if (!$connected) {
					Throw new Exception('Unable to open database connection.');
				}
			}
		}

		// if we are from command line we exit here
		if (!empty($options['__run_only_bootstrap'])) {
			return;
		}

		// initialize cache
		$cache = application::get('cache');
		if (!empty($cache) && $backend && !empty($flags['global']['cache']['autoconnect'])) {
			// converting flags to array
			if (!is_array($flags['global']['cache']['autoconnect'])) {
				$flags['global']['cache']['autoconnect'] = [$flags['global']['cache']['autoconnect']];
			}
			// going though all available links
			foreach ($cache as $cache_link => $cache_clusters) {
				$connected = false;
				foreach ($cache_clusters as $cache_settings) {
					if (!in_array($cache_link, $flags['global']['cache']['autoconnect']) && !in_array('*', $flags['global']['cache']['autoconnect'])) {
						continue;
					}
					if (!empty($cache_settings['submodule'])) {
						$cache_object = new cache($cache_link, $cache_settings['submodule']);
						$cache_status = $cache_object->connect($cache_settings);
						if ($cache_status['success']) {
							$connected = true;
							break;
						}
					}
				}
				// checking if not connected
				if (!$connected) {
					Throw new Exception('Unable to open cache connection.');
				}
			}
		}

		// initialize session
		$session = application::get('flag.global.session');
		if (!empty($session['start']) && $backend) {
			session::start(isset($session['options']) ? $session['options'] : []);
		}

		// format: locale and timezone after database and cache
		/* todo: fix here
		$format = application::get(array('format'));
		if (!empty($format)) {
			format::init($format);
		}
		*/

		// including media files
		layout::add_js('/numbers/media_submodules/numbers_framework_functions.js', -32001);
		layout::add_js('/numbers/media_submodules/numbers_framework_base.js', -32000);
		// generating token to receive data from frontend
		if ($backend) {
			$crypt_class = new crypt();
			$token = urldecode($crypt_class->token_create('general'));
			layout::js_data(['token' => $token]);
		}
	}

	/**
	 * Destroy everything
	 */
	public static function destroy() {
		$__run_only_bootstrap = application::get(['flag', 'global', '__run_only_bootstrap']);
		// error processing
		if (empty(error::$flag_error_already)) {
			$last_error = error_get_last();
			$flag_render = false;
			if (in_array($last_error['type'], [E_COMPILE_ERROR, E_PARSE, E_ERROR])) {
				error::error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
				error::$flag_error_already = true;
				$flag_render = true;
			}
			if ($flag_render || error::$flag_exception) {
				if ($__run_only_bootstrap) {
					$temp = @ob_get_clean();
					print_r(error::$errors);
				} else {
					application::set_mvc('/error/~error/500');
					application::process();
				}
			}
		}

		// write sessions
		session_write_close();

		// closing caches before db
		$cache = factory::get(['cache']);
		if (!empty($cache)) {
			foreach ($cache as $k => $v) {
				$object = $v['object'];
				$object->close();
			}
		}

		// close db connections
		$dbs = factory::get(['db']);
		if (!empty($dbs)) {
			foreach ($dbs as $k => $v) {
				$object = $v['object'];
				$object->close();
			}
		}

		// final benchmark
		if (debug::$debug) {
			debug::benchmark('application end');
		}

		// debugging toolbar last
		if (debug::$toolbar && !$__run_only_bootstrap) {
			echo str_replace('<!-- [numbers: debug toolbar] -->', debug::render(), ob_get_clean());
			flush();
		}

		// emails with erros
		if (debug::$debug && !empty(debug::$email)) {
			debug::send_errors_to_admin();
		}
	}
}