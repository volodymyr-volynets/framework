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
	 * Flag IP address changed
	 *
	 * @var boolean
	 */
	public static $flag_ip_changed = false;

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
			// regenerate id on IP change
			self::$flag_ip_changed = true;
			session_regenerate_id(true);
			// reset variable
			$_SESSION['numbers']['ip'] = [
				'pages_count' => 0,
				'request_count' => 0,
			];
		}
		// we need to try to decode ip address
		$ip_decoder_submodule = \Application::get('flag.global.ip.submodule');
		if (!empty($ip_decoder_submodule) && empty($_SESSION['numbers']['ip']['decoded'])) {
			$ip_object = new \Object\Miscellaneous\IP();
			$ip_data = $ip_object->get($ip);
			if ($ip_data['success']) {
				$_SESSION['numbers']['ip'] = array_merge2(['ip' => $ip], $ip_data['data']);
				$_SESSION['numbers']['ip']['decoded'] = true;
			} else {
				$_SESSION['numbers']['ip']['decoded'] = 'Not found in database!';
			}
		}
		// we only store ip address if its not set
		if (!isset($_SESSION['numbers']['ip']['ip'])) {
			$_SESSION['numbers']['ip'] = [
				'ip' => $ip,
				'pages_count' => 0,
				'request_count' => 0,
			];
		}
		// increment counters
		$__ajax = \Request::input('__ajax');
		if (!$__ajax && \Object\Content\Types::existsStatic(['where' => ['no_virtual_controller_code' => \Application::get('flag.global.__content_type'), 'no_content_type_presentation' => 1]])) {
			$_SESSION['numbers']['ip']['pages_count'] = ($_SESSION['numbers']['ip']['pages_count'] ?? 0) + 1;
		}
		$_SESSION['numbers']['ip']['request_count'] = ($_SESSION['numbers']['ip']['request_count'] ?? 0) + 1;
		// add anonymous role
		if (!\User::authorized()) {
			\User::roleGrant(\Object\ACL\Resources::getStatic('user_roles', 'anonymous', 'data'));
		}
		// Protection for over usage.
		$allowed_ips = \Application::get('firewalls.primary.allow.ips') ?? [];
		if (empty($allowed_ips)) {
			$allowed_ips[] = '127.0.0.1';
		}
		if (!in_array($ip, $allowed_ips)) {
			self::$object->checkOverUsage($ip, \Application::get('firewalls.primary.rules') ?? []);
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