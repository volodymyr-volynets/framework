<?php

namespace Object_Override;
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
		if (isset(self::$override_data[$class]) && self::$override_data[$class] === false) {
			return false;
		}
		$filename = './overrides/class/override_' . $class . '.php';
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