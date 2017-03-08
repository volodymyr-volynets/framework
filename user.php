<?php

/**
 * User
 */
class user {

	/**
	 * User #
	 *
	 * @return int
	 */
	public static function user_id() {
		return ($_SESSION['numbers']['user']['id'] ?? null);
	}

	/**
	 * Authorized
	 *
	 * @return boolean
	 */
	public static function authorized() {
		return (!empty($_SESSION['numbers']['flag_authorized']) ? true : false);
	}

	/**
	 * Authorize user
	 *
	 * @param array $data
	 */
	public static function user_authorize(array $data) {
		$_SESSION['numbers']['user'] = $data;
		// flag as authorized
		$_SESSION['numbers']['flag_authorized'] = true;
		// add authorized role
		self::role_grant(object_acl_resources::get_static('user_roles', 'authorized', 'data'));
	}

	/**
	 * Sign out user
	 *
	 * @param array $data
	 */
	public static function user_sign_out() {
		$_SESSION['numbers']['user'] = [];
		$_SESSION['numbers']['flag_authorized'] = false;
	}

	/**
	 * Grant role(s)
	 *
	 * @param string|array $role
	 */
	public static function role_grant($role) {
		// initialize roles array
		if (!isset($_SESSION['numbers']['user']['roles'])) {
			$_SESSION['numbers']['user']['roles'] = [];
		}
		// add roles
		if (!empty($role)) {
			if (!is_array($role)) $role = [$role];
			$_SESSION['numbers']['user']['roles'] = array_unique(array_merge($_SESSION['numbers']['user']['roles'], $role));
		}
	}

	/**
	 * Revoke role(s)
	 *
	 * @param string|array $role
	 */
	public static function role_revoke($role) {
		if (!empty($role) && !empty($_SESSION['numbers']['user']['roles'])) {
			if (!is_array($role)) $role = [$role];
			foreach ($role as $v) {
				$key = array_search($v, $_SESSION['numbers']['user']['roles']);
				if ($key !== false) {
					unset($_SESSION['numbers']['user']['roles'][$key]);
				}
			}
		}
	}

	/**
	 * Check if role(s) exists
	 *
	 * @param string|array $role
	 * @return boolean
	 */
	public static function role_exists($role) : bool {
		if (empty($_SESSION['numbers']['user']['roles'])) return false;
		if (is_array($role)) {
			$temp = array_intersect($role, $_SESSION['numbers']['user']['roles']);
			return !empty($temp);
		} else {
			return in_array($role, $_SESSION['numbers']['user']['roles']);
		}
	}
}