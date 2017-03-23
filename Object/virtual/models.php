<?php

class object_virtual_models {

	/**
	 * Model
	 *
	 * @param string $class
	 */
	public static function model($class) {
		$temp = explode('__virtual__', $class);
		$last = array_pop($temp);
		// fetch submodule
		$submodule = application::get("flag.global.widgets.{$last}.submodule");
		$class = str_replace('.base__123', '', $submodule . '__123');
		$class = str_replace('.', '_', $class) . '_model_virtual_' . $last;
		// create an object
		$object = new $class(implode('__virtual__', $temp));
		return $object;
	}
}