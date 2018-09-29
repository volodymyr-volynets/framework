<?php

class Registry {

	/**
	 * Settings
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Cached registries
	 *
	 * @var array
	 */
	private static $cached_registries;

	/**
	 * Access to settings, we can get a set of keys
	 *
	 * @param mixed $key
	 *		if key starts with "application" we would pull from application settings
	 * @return mixed
	 */
	public static function get($registry_key) {
		$key = array_key_convert_key($registry_key);
		$key2 = implode('.', $key);
		// if we need to fetch from application settings
		if ($key[0] == 'application') {
			array_shift($key);
			$result = \Application::get($key);
		} else {
			$result = array_key_get(self::$settings, $key);
			if (!isset($result)) {
				array_unshift($key, 'registry');
				array_unshift($key, 'numbers');
				$result = \Session::get($key);
			}
		}
		// load overrides from db
		if (\Db::$flag_db_loaded) {
			// see if we have an override
			if (!isset(self::$cached_registries)) {
				self::$cached_registries = \Object\ACL\Resources::getStatic('registries', 'primary') ?? [];
			}
			if (isset(self::$cached_registries[$key2])) {
				$result = self::$cached_registries[$key2]['value'];
			}
		}
		return $result;
	}

	/**
	 * Set value in settings
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param array $options
	 *		boolean session - whether to store value in session
	 */
	public static function set($key, $value, $options = []) {
		// store value in session
		if (!empty($options['session'])) {
			$key = array_key_convert_key($key);
			array_unshift($key, 'registry');
			array_unshift($key, 'numbers');
			\Session::set($key, $value);
		}
		array_key_set(self::$settings, $key, $value);
	}
}