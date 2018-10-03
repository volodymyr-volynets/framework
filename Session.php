<?php

class Session {

	/**
	 * Submodule object
	 *
	 * @var object
	 */
	public static $object;

	/**
	 * Array of default options
	 * 
	 * @var array
	 */
	public static $default_options = [
		'save_path'                 => null,
		'name'                      => null,
		'save_handler'              => null,
		'gc_probability'            => 1,
		'gc_divisor'                => 100,
		'gc_maxlifetime'            => 7200,
		'serialize_handler'         => null,
		'cookie_lifetime'           => null,
		'cookie_path'               => null,
		'cookie_domain'             => null,
		'cookie_secure'             => null,
		'cookie_httponly'           => null,
		'use_cookies'               => null,
		'use_only_cookies'          => 'off',
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
	];

	/**
	 * Starting session
	 *
	 * @param array $options
	 */
	public static function start($options) {
		// setting default options
		foreach (self::$default_options as $k => $v) {
			if (isset($options[$k]) || array_key_exists($k, $options)) {
				ini_set("session.$k", $options[$k]);
				self::$default_options[$k] = $options[$k];
			} else if (isset(self::$default_options[$k])) {
				ini_set("session.$k", $v);
			}
		}
		// session # replacement
		$__session_id = \Application::get('flag.global.__session_id');
		if (!empty($__session_id)) {
			session_id($__session_id);
		}
		// starting session submodule if we have one
		$class = Application::get('flag.global.session.submodule', ['class' => 1]);
		// check if backend has been enabled
		if (!\Application::get($class, ['submodule_exists' => true])) {
			Throw new Exception('You must enable ' . $class . ' first!');
		}
		// initialize
		if (!empty($class)) {
			self::$object = new $class();
			self::$object->init();
		}
		// starting session
		session_start();
		// session fixation prevention
		if (empty($_SESSION['numbers']['flag_generated_by_system'])) {
			session_regenerate_id(true);
			$_SESSION = [];
			$_SESSION['numbers']['flag_generated_by_system'] = true;
		}
		// processing IP address
		$ip = \Request::ip();
		// we need to reset ip address details if we have different ip
		if (!empty($_SESSION['numbers']['ip']['ip']) && $_SESSION['numbers']['ip']['ip'] != $ip) {
			$_SESSION['numbers']['ip'] = [];
		}
		// we need to try to decode ip address
		$ip_decoder_submodule = \Application::get('flag.global.ip.submodule');
		if (!empty($ip_decoder_submodule) && !isset($_SESSION['numbers']['ip']['ip'])) {
			$ip_object = new \Object\Miscellaneous\IP();
			$ip_data = $ip_object->get($ip);
			if ($ip_data['success']) {
				$_SESSION['numbers']['ip'] = array_merge2(['ip' => $ip], $ip_data['data']);
			}
		}
		// we only store ip address if its not set
		if (!isset($_SESSION['numbers']['ip']['ip'])) {
			$_SESSION['numbers']['ip'] = [
				'ip' => $ip
			];
		}
		// add anonymous role
		if (!\User::authorized()) {
			\User::roleGrant(\Object\ACL\Resources::getStatic('user_roles', 'anonymous', 'data'));
		}
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
	 * Garbage collector
	 */
	public static function gc() {
		self::$object->gc(1);
	}

	/**
	 * Set session values as static
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param array $options
	 */
	public static function set($key, $value, $options = []) {
		array_key_set($_SESSION, $key, $value, $options);
	}

	/**
	 * Get session values as static
	 * 
	 * @param string $key
	 * @return type
	 */
	public static function get($key = null) {
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
		// todo: what if key is an array
		return isset($_SESSION[$key]);
	}

	/**
	 * Unset a key in session
	 * 
	 * @param type $key
	 */
	public function __unset($key) {
		// todo: what if key is an array
		unset($_SESSION[$key]);
	}

	/**
	 * Add expiry dialog
	 */
	public static function expiryDialog() {
		/**
		 * Important, we trigger dialog when:
		 *		1. Session exists and user is authorized
		 *		2. Controller requires login and not public
		 */
		$acl = \Application::$controller->acl;
		if (!empty(self::$object) && \Application::get('flag.global.session.expiry_dialog') && \User::authorized() && !empty($acl['authorized']) && empty($acl['public'])) {
			\Layout::onhtml(self::$object->expiryDialog());
		}
	}
}