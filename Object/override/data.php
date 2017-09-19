<?php

namespace Object\Override;
class Data {

	/**
	 * We would keep override data cached here
	 *
	 * @var array
	 */
	public static $override_data = [];

	/**
	 * Override handler
	 *
	 * @param type $object
	 * @return boolean
	 */
	public function overrideHandle(& $object) {
		$class = get_class($object);
		$class = str_replace('\\', '_', trim($class, '\\'));
		if (isset(self::$override_data[$class]) && self::$override_data[$class] === false) {
			return false;
		}
		// need to fix file path based on PHPUnit
		if (!file_exists('./Overrides')) {
			$filename = './application/Overrides/Class/Override_' . $class . '.php';
		} else {
			$filename = './Overrides/Class/Override_' . $class . '.php';
		}
		if (!file_exists($filename)) {
			self::$override_data[$class] = false;
			return false;
		}
		unset($object_override_blank_object);
		require($filename); // must use require!!!
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