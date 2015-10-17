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
	public static function init() {
		$input = request::input(null, true, true);
		$mvc = application::mvc($_SERVER['REQUEST_URI']);

		// internationalization
		$i18n = application::get(array('i18n'));
		if (!empty($i18n)) {
			$current_lang = !empty($input[$i18n['current_variable']]) ? $input[$i18n['current_variable']] : $i18n['default'];
			$class_i18n = new i18n($i18n['default'], $current_lang, $i18n['path']);
		}

		// get flags
		$flags = application::get('flag');

		// initialize cryptography
		$crypt = application::get('crypt');
		if (!empty($crypt) && !empty($flags['global']['crypt']['autoconnect'])) {
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
		if (!empty($db) && !empty($flags['global']['db']['autoconnect'])) {
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

		// initialize cache
		$cache = application::get('cache');
		if (!empty($cache)) {
			foreach ($cache as $cache_id => $cache_options) {
				$cache_result = cache::create($cache_id, $cache_options);
			}
		}

		// initialize session
		$session = application::get('session');
		if (!empty($session)) {
			session::start($session);
		}

		// format: locale and timezone after database and cache
		$format = application::get(array('format'));
		if (!empty($format)) {
			format::init($format);
		}
	}

	/**
	 * Destroy everything
	 */
	public static function destroy() {
		// write sessions
		session_write_close();

		// close db connections
		$dbs = factory::get(['db']);
		if (!empty($dbs)) {
			foreach ($dbs as $k => $v) {
				$object = $v['object'];
				$object->close();
			}
		}
	}
}