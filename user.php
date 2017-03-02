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
		// todo
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
	 * Grant role
	 *
	 * @param string $role
	 */
	public static function role_grant($role) {
		// initialize roles array
		if (!isset($_SESSION['numbers']['user']['roles'])) {
			$_SESSION['numbers']['user']['roles'] = [];
		}
		if (!empty($role) && !in_array($role, $_SESSION['numbers']['user']['roles'])) {
			$_SESSION['numbers']['user']['roles'][] = $role;
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