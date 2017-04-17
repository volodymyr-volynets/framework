<?php

class User {

	/**
	 * User #
	 *
	 * @return int
	 */
	public static function id() {
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
	 * Get
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public static function get($key) {
		if (isset($_SESSION['numbers']['user'])) {
			return array_key_get($_SESSION['numbers']['user'], $key);
		}
	}

	/**
	 * Authorize user
	 *
	 * @param array $data
	 */
	public static function userAuthorize(array $data) {
		$_SESSION['numbers']['user'] = $data;
		// flag as authorized
		$_SESSION['numbers']['flag_authorized'] = true;
		// add authorized role
		$roles = \Object\ACL\Resources::getStatic('user_roles', 'authorized', 'data');
		self::roleGrant($roles);
	}

	/**
	 * Sign out user
	 *
	 * @param array $data
	 */
	public static function userSignOut() {
		$_SESSION['numbers']['user'] = [];
		$_SESSION['numbers']['flag_authorized'] = false;
	}

	/**
	 * Roles
	 *
	 * @return array
	 */
	public static function roles() : array {
		return $_SESSION['numbers']['user']['roles'] ?? [];
	}

	/**
	 * Grant role(s)
	 *
	 * @param string|array $role
	 */
	public static function roleGrant($role) {
		// add roles
		if (!empty($role)) {
			// initialize roles array
			if (!isset($_SESSION['numbers']['user']['roles'])) {
				$_SESSION['numbers']['user']['roles'] = [];
			}
			if (!is_array($role)) $role = [$role];
			$_SESSION['numbers']['user']['roles'] = array_unique(array_merge($_SESSION['numbers']['user']['roles'], $role));
		}
	}

	/**
	 * Revoke role(s)
	 *
	 * @param string|array $role
	 */
	public static function roleRevoke($role) {
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
	public static function roleExists($role) : bool {
		if (empty($_SESSION['numbers']['user']['roles'])) return false;
		if (is_array($role)) {
			$temp = array_intersect($role, $_SESSION['numbers']['user']['roles']);
			return !empty($temp);
		} else {
			return in_array($role, $_SESSION['numbers']['user']['roles']);
		}
	}
}