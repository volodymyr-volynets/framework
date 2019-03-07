<?php

class Can {

	/**
	 * Submodule exists
	 *
	 * @param string $submodule
	 * @return bool
	 */
	public static function submoduleExists(string $submodule) : bool {
		return \Application::get(str_replace(['/', '\\', '.'], '\\', $submodule . '\\' . 'Base'), ['submodule_exists' => true]);
	}

	/**
	 * System module exists
	 *
	 * @param string $submodule
	 * @return bool
	 */
	public static function systemModuleExists(string $module_code) : bool {
		$result = \Object\Controller::getSystemModuleByModuleCode($module_code);
		return !empty($result);
	}

	/**
	 * System feature exists
	 *
	 * @param string $feature_code
	 * @param int|null $module_id
	 * @return bool
	 */
	public static function systemFeatureExists(string $feature_code, $module_id = null) : bool {
		if (!isset($module_id)) {
			$module_id = \Application::$controller->module_id;
		}
		$temp = explode('::', $feature_code);
		$result = \Object\Controller::getSystemModuleByModuleCode($temp[0]);
		if (isset($result['module_ids'][$module_id]['features'])) {
			return in_array($feature_code, $result['module_ids'][$module_id]['features']);
		}
		if (empty($result['module_multiple'])) {
			return in_array($feature_code, $result['all_features']);
		}
		return false;
	}

	/**
	 * System features exist
	 *
	 * @param array $feature_codes
	 * @param int|null $module_id
	 * @return bool
	 */
	public static function systemFeaturesExist(array $feature_codes, $module_id = null) : bool {
		$not_found = false;
		foreach ($feature_codes as $v) {
			if (!self::systemFeatureExists($v, $module_id)) {
				$not_found = true;
				break;
			}
		}
		return !$not_found;
	}

	/**
	 * User feature exists
	 *
	 * @param string $feature_code
	 * @param int|null $module_id
	 * @return bool
	 */
	public static function userFeatureExists(string $feature_code, $module_id = null) : bool {
		if (!isset($module_id)) {
			$module_id = \Application::$controller->module_id;
		}
		// user first
		$features = \User::get('features');
		if (!empty($features)) {
			$result = self::userFeatureExistsOne($features, $feature_code, $module_id);
			if ($result == 1) {
				return true;
			} else if ($result == 2) { // disabled feature
				return false;
			}
		}
		// roles second
		if (is_null(\Object\Controller::$cached_roles) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			\Object\Controller::$cached_roles = \Object\ACL\Resources::getStatic('roles', 'primary');
		}
		foreach (\User::roles() as $v) {
			$result = self::userFeatureExistsRole($v, $feature_code, $module_id);
			if ($result == 1) {
				return true;
			} else if ($result == 2) { // disabled feature
				return false;
			}
		}
		// teams last
		if (is_null(\Object\Controller::$cached_teams) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			\Object\Controller::$cached_teams = \Object\ACL\Resources::getStatic('roles', 'teams');
		}
		foreach (\User::teams() as $v) {
			$features = \Object\Controller::$cached_teams[$v]['features'] ?? null;
			if (!empty($features)) {
				$result = self::userFeatureExistsOne($features, $feature_code, $module_id);
				if ($result == 1) {
					return true;
				} else if ($result == 2) { // disabled feature
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * User feature exists one
	 *
	 * @param array $features
	 * @param string $feature_code
	 * @param int $module_id
	 * @return int
	 */
	private static function userFeatureExistsOne(array $features, string $feature_code, $module_id = null) : int {
		$result = $features[$feature_code][$module_id] ?? null;
		if ($result === 0) {
			return 1;
		} else if ($result === 1) {
			return 2;
		}
		return 0;
	}

	/**
	 * User feature exists one role
	 *
	 * @param string $role
	 * @param string $feature_code
	 * @param int $module_id
	 * @return int
	 */
	private static function userFeatureExistsRole(string $role, string $feature_code, $module_id = null) : int {
		if (!empty(\Object\Controller::$cached_roles[$role]['features'])) {
			$result = self::userFeatureExistsOne(\Object\Controller::$cached_roles[$role]['features'], $feature_code, $module_id);
			if ($result == 1) {
				return true;
			} else if ($result == 2) { // disabled feature
				return false;
			}
		}
		// super admin
		if (!empty(\Object\Controller::$cached_roles[$role]['super_admin'])) return 1;
		// if permission is not found we need to check parents
		if (empty(\Object\Controller::$cached_roles[$role]['parents'])) return 0;
		// go though parents
		foreach (\Object\Controller::$cached_roles[$role]['parents'] as $k => $v) {
			if (!empty($v)) continue;
			$result = self::userFeatureExistsRole($k, $feature_code, $module_id);
			if ($result === 1) {
				return 1;
			} else if ($result === 2) {
				return 2;
			}
		}
		return 0;
	}

	/**
	 * File exist in path
	 *
	 * @param string $filename
	 * @return mixed
	 */
	public static function fileExistsInPath(string $filename) {
		$paths = explode(';', str_replace(':', ';', get_include_path()));
		foreach($paths as $v) {
			if (file_exists($v . DIRECTORY_SEPARATOR . $filename)) {
				return $v . DIRECTORY_SEPARATOR . $filename;
			}
		}
		return false;
	}

	/**
	 * User feature exists
	 *
	 * @param string|int $flag
	 * @param string|int $action
	 * @param int|null $module_id
	 * @return bool
	 */
	public static function userFlagExists($flag, $action, $module_id = null) : bool {
		if (\Helper\Cmd::isCli()) return false;
		if (!isset($module_id)) {
			$module_id = \Application::$controller->module_id;
		}
		// load all actions from datasource
		if (is_null(\Object\Controller::$cached_actions) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			\Object\Controller::$cached_actions = \Object\ACL\Resources::getStatic('actions', 'primary');
		}
		// load all flags
		if (is_null(\Object\Controller::$cached_flags) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			\Object\Controller::$cached_flags = \Object\ACL\Resources::getStatic('flags', 'primary');
		}
		if (is_string($flag)) {
			$flag = \Object\Controller::$cached_flags[$flag]['id'];
		}
		if (is_string($action)) {
			$action = \Object\Controller::$cached_actions[$action]['id'];
		}
		// user first
		$flags = \User::get('flags');
		if (!empty($flags)) {
			$result = self::userFlagExistsOne($flags, $flag, $action, $module_id);
			if ($result == 1) {
				return true;
			} else if ($result == 2) { // disabled feature
				return false;
			}
		}
		// roles second
		if (is_null(\Object\Controller::$cached_roles) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			\Object\Controller::$cached_roles = \Object\ACL\Resources::getStatic('roles', 'primary');
		}
		foreach (\User::roles() as $v) {
			$result = self::userFlagExistsRole($v, $flag, $action, $module_id);
			if ($result == 1) {
				return true;
			} else if ($result == 2) { // disabled feature
				return false;
			}
		}
		// teams last
		if (is_null(\Object\Controller::$cached_teams) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			\Object\Controller::$cached_teams = \Object\ACL\Resources::getStatic('roles', 'teams');
		}
		foreach (\User::teams() as $v) {
			$flags = \Object\Controller::$cached_teams[$v]['flags'] ?? null;
			if (!empty($features)) {
				$result = self::userFeatureExistsOne($flags, $flag, $action, $module_id);
				if ($result == 1) {
					return true;
				} else if ($result == 2) { // disabled feature
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * User flag exists one
	 *
	 * @param array $flags
	 * @param int $flag_id
	 * @param int $action_id
	 * @param int $module_id
	 * @return int
	 */
	private static function userFlagExistsOne(array $flags, int $flag_id, int $action_id, $module_id = null) : int {
		$result = $flags[$flag_id][$action_id][$module_id] ?? null;
		if ($result === 0) {
			return 1;
		} else if ($result === 1) {
			return 2;
		}
		return 0;
	}

	/**
	 * User flag exists one role
	 *
	 * @param string $role
	 * @param string $feature_code
	 * @param int $module_id
	 * @return int
	 */
	private static function userFlagExistsRole(string $role, int $flag_id, int $action_id, $module_id = null) : int {
		if (!empty(\Object\Controller::$cached_roles[$role]['flags'])) {
			$result = self::userFlagExistsOne(\Object\Controller::$cached_roles[$role]['flags'], $flag_id, $action_id, $module_id);
			if ($result == 1) {
				return true;
			} else if ($result == 2) { // disabled feature
				return false;
			}
		}
		// if permission is not found we need to check parents
		if (empty(\Object\Controller::$cached_roles[$role]['parents'])) return 0;
		// go though parents
		foreach (\Object\Controller::$cached_roles[$role]['parents'] as $k => $v) {
			if (!empty($v)) continue;
			$result = self::userFlagExistsRole($k, $flag_id, $action_id, $module_id);
			if ($result === 1) {
				return 1;
			} else if ($result === 2) {
				return 2;
			}
		}
		return 0;
	}
}