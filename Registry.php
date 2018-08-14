<?php

class Registry {

	/**
	 * Settings
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Access to settings, we can get a set of keys
	 *
	 * @param mixed $key
	 *		if key starts with "application" we would pull from application settings
	 * @return mixed
	 */
	public static function get($key) {
		$key = array_key_convert_key($key);
		// if we need to fetch from application settings
		if ($key[0] == 'application') {
			array_shift($key);
			return \Application::get($key);
		}
		$result = array_key_get(self::$settings, $key);
		if (!isset($result)) {
			array_unshift($key, 'registry');
			array_unshift($key, 'numbers');
			$result = \Session::get($key);
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