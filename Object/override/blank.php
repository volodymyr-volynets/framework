<?php

namespace Object\Override;
class Blank {

	/**
	 * Create new object and set properties
	 *
	 * @param array $vars
	 * @return object
	 */
	public static function __set_state($vars) {
		$object = new \Object\Override\Blank();
		object_merge_values($object, $vars);
		return $object;
	}
}