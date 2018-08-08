<?php

namespace Object\Virtual;
class Models {

	/**
	 * Model
	 *
	 * @param string $class
	 */
	public static function model($class, $options = []) {
		$temp = explode('\0Virtual0\\', $class);
		$last = array_pop($temp);
		$temp2 = explode('\\', $last);
		$model = \Object\ACL\Resources::getStatic(strtolower($temp2[0]), strtolower($temp2[1]), 'model');
		// create an object
		$parent_class = implode('\0Virtual0\\', $temp);
		$object = new $model($parent_class, $class, $options);
		return $object;
	}
}