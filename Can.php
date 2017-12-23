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
}