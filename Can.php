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
	 * @param string $submodule
	 * @return bool
	 */
	public static function systemFeatureExists(string $feature_code) : bool {
		$temp = explode('::', $feature_code);
		$result = \Object\Controller::getSystemModuleByModuleCode($temp[0]);
		if (isset($result['module_ids'][\Application::$controller->module_id]['features'])) {
			return in_array($feature_code, $result['module_ids'][\Application::$controller->module_id]['features']);
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
	 * @return bool
	 */
	public static function systemFeaturesExist(array $feature_codes) : bool {
		$not_found = false;
		foreach ($feature_codes as $v) {
			if (!self::systemFeatureExists($v)) {
				$not_found = true;
				break;
			}
		}
		return !$not_found;
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
}