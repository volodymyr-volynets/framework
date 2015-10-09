<?php

class session {

	/**
	 * Available adapters
	 * 
	 * @var array
	 */
	private static $adapter_types = array(
		'file' => array('class'=>'session_file'),
		'db' => array('class'=>'session_db')
	);

	/**
	 * Array of default options
	 * 
	 * @var array
	 */
	private static $_default_options = array(
		'save_path'                 => null,
		'name'                      => null,
		'save_handler'              => null,
		'gc_probability'            => 1,
		'gc_divisor'                => 1000,
		'gc_maxlifetime'            => 7200,
		'serialize_handler'         => null,
		'cookie_lifetime'           => null,
		'cookie_path'               => null,
		'cookie_domain'             => null,
		'cookie_secure'             => null,
		'cookie_httponly'           => null,
		'use_cookies'               => null,
		'use_only_cookies'          => 'on',
		'referer_check'             => null,
		'entropy_file'              => null,
		'entropy_length'            => null,
		'cache_limiter'             => null,
		'cache_expire'              => null,
		'use_trans_sid'             => null,
		'bug_compat_42'             => null,
		'bug_compat_warn'           => null,
		'hash_function'             => null,
		'hash_bits_per_character'   => null
	);

	/**
	 * Starting session
	 * 
	 * @param array $options
	 */
	public static function start($options) {

		// setting default options
		foreach (self::$_default_options as $k=>$v) {
			if (isset($options[$k])) {
				ini_set("session.$k", $options[$k]);
			} else if (isset(self::$_default_options[$k])) {
				ini_set("session.$k", $v);
			}
		}

		// starting session from adapter
		if (!isset($options['gc_maxlifetime'])) $options['gc_maxlifetime'] = self::$_default_options['gc_maxlifetime'];
		$type = isset($options['type']) && isset(self::$adapter_types[$options['type']]) ? $options['type'] : 'file';
		call_user_func_array(array(self::$adapter_types[$type]['class'], 'start'), array($options));

		// starting session
		session_start();
	}

	/**
	 * Destroy the session
	 */
	public static function destroy() {
		// remove session variable from cookies
		setcookie(session_name(), '', time() - 3600, '/');
		// destroy the session.
		session_destroy();
		session_write_close();
	}

	/**
	 * Set session values as static
	 * 
	 * @param type $key
	 * @param type $value
	 */
	public static function set($key, $value) {
		array_key_set($_SESSION, $key, $value);
	}

	/**
	 * Get session values as static
	 * 
	 * @param string $key
	 * @return type
	 */
	public static function get($key = '') {
		return array_key_get($_SESSION, $key);
	}

	/**
	 * Set session variable
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value) {
		array_key_set($_SESSION, $key, $value);
	}

	/**
	 * Get session values
	 * 
	 * @param type $key
	 * @return type
	 */
	public function __get($key) {
		return array_key_get($_SESSION, $key);
	}

	/**
	 * Isset check in sessions
	 * 
	 * @param type $key
	 * @return type
	 */
	public function __isset($key) {
		return isset($_SESSION[$key]);
	}

	/**
	 * Unsetting a key in sessions
	 * 
	 * @param type $key
	 */
	public function __unset($key) {
		unset($_SESSION[$key]);
	}
}