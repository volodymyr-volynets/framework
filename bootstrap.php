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

		// create database connections
		$db = application::get('db');
		if (!empty($db)) {
			foreach ($db as $db_link=>$db_settings) {
				$connected = false;
				foreach ($db_settings as $server_key=>$server_values) {
					$db_status = db::connect($server_values, $db_link);
					if ($db_status['success'] && $db_status['status']) {
						$connected = true;
						break;
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

		// kill db connections
		if (!empty(db::$links)) {
			foreach (db::$links as $db_link=>$db_settings) {
				db::close($db_link);
			}
		}
	}
}