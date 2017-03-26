<?php

namespace Object;
class Widgets {

	/**
	 * Attributes
	 */
	const ATTRIBUTES = '__widget_attributes';
	const ATTRIBUTES_DATA = ['order' => PHP_INT_MAX - 1000, 'label_name' => 'Attributes', 'widget' => 'attributes'];

	/**
	 * Details Attributes
	 */
	const DETAIL_ATTRIBUTES = '__widget_attribute_details';
	const DETAIL_ATTRIBUTES_DATA = [
		'widget' => 'detail_attributes',
		'label_name' => 'Attributes',
		'type' => 'subdetails',
		'details_rendering_type' => 'table',
		'details_new_rows' => 5,
		'details_parent_key' => null,
		'details_key' => null,
		'details_pk' => null,
		'order' => PHP_INT_MAX - 1000,
		'required' => false
	];

	/**
	 * Addresses
	 */
	const ADDRESSES = '__widget_addresses';
	const ADDRESSES_DATA = ['order' => PHP_INT_MAX - 2000, 'label_name' => 'Addresses', 'widget' => 'addresses'];

	/**
	 * All available widgets embedded into tabs
	 */
	const WIDGET_TABS = [self::ATTRIBUTES, self::ADDRESSES];

	/**
	 * All available widgets embedded into details
	 */
	const WIDGET_DETAILS = ['attributes'];

	/**
	 * Widgets that can be attached to the models
	 */
	const WIDGET_MODELS = ['attributes', 'addresses', 'audit', 'registrations'];

	/**
	 * Enabled
	 *
	 * @param string $widget
	 * @return boolean
	 */
	public static function enabled($widget) {
		// check if submodule is enabled
		$submodule = Application::get("flag.global.widgets.{$widget}.submodule");
		if (empty($submodule)) return false;
		// check if submodule is included
		$temp = explode('.', $submodule);
		array_pop($temp);
		array_unshift($temp, 'submodule');
		array_unshift($temp, 'dep');
		if (empty(Application::get($temp))) return false;
		return true;
	}
}