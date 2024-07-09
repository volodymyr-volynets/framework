<?php

namespace Object\Override;

use stdClass;

#[\AllowDynamicProperties]
class Data {

	/**
	 * We would keep override data cached here
	 *
	 * @var array
	 */
	public static array $override_data = [];

	/**
	 * Have overrides
	 *
	 * @var bool
	 */
	public bool $have_overrides = true;

	/**
	 * Override handler
	 *
	 * @param type $object
	 * @return boolean
	 */
	public function overrideHandle(& $object) {
		// this determines whether object needs to be ovverriden
		if (empty($this->have_overrides)) {
			return;
		}
		$class = get_class($object);
		$class = str_replace('\\', '_', trim($class, '\\'));
		if (isset(self::$override_data[$class]) && self::$override_data[$class] === false) {
			return false;
		}
		// need to fix file path based on PHPUnit
		$filename = \Application::get(['application', 'path_full']) . 'Overrides/Class/Override_' . $class . '.php';
		$object_override_blank_object = new stdClass(); // a must
		if (!file_exists($filename)) {
			self::$override_data[$class] = false;
			return false;
		}
		require($filename); // a must
		$vars = get_object_vars($object_override_blank_object);
		if (empty($vars)) {
			self::$override_data[$class] = false;
			return false;
		} else {
			self::$override_data[$class] = $vars;
		}
		// if we have data we merge it with an object
		if (!empty(self::$override_data[$class])) {
			object_merge_values($object, self::$override_data[$class]);
		}
	}
}