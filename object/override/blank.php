<?php

class object_override_blank {

	/**
	 * Create new object and set properties
	 *
	 * @param array $vars
	 * @return object
	 */
	public static function __set_state($vars) {
		$object = new object_override_blank();
		object_merge_values($object, $vars);
		return $object;
	}
}