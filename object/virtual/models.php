<?php

class object_virtual_models {

	/**
	 * List of available handlers
	 *
	 * @var array
	 */
	public static $data = [
		'attributes' => 'numbers_data_relations_model_attribute_virtual_attributes',
		'addresses' => 'numbers_data_widgets_addresses_basic_model_virtual_addresses',
		'audit' => 'numbers_data_widgets_audit_basic_model_virtual_audit'
	];

	/**
	 * Model
	 *
	 * @param string $class
	 */
	public static function model($class) {
		$temp = explode('__virtual__', $class);
		$last = array_pop($temp);
		$class = self::$data[$last];
		$object = new $class(implode('__virtual__', $temp));
		return $object;
	}
}