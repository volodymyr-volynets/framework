<?php

class object_widgets {

	/**
	 * Attributes
	 */
	const attributes = '__widget_attributes';
	const attributes_data = ['order' => PHP_INT_MAX - 1000, 'label_name' => 'Attributes', 'widget' => 'attributes'];

	/**
	 * Details Attributes
	 */
	const detail_attributes = '__widget_attribute_details';
	const detail_attributes_data = [
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
	const addresses = '__widget_addresses';
	const addresses_data = ['order' => PHP_INT_MAX - 2000, 'label_name' => 'Addresses', 'widget' => 'addresses'];

	/**
	 * All available widgets embedded into tabs
	 */
	const widget_tabs = [self::attributes, self::addresses];

	/**
	 * All available widgets embedded into details
	 */
	const widget_details = ['attributes'];

	/**
	 * Widgets that can be attached to the models
	 */
	const widget_models = ['attributes', 'addresses', 'audit', 'registrations'];

	/**
	 * Enabled
	 *
	 * @param string $widget
	 * @return boolean
	 */
	public static function enabled($widget) {
		// check if submodule is enabled
		$submodule = application::get("flag.global.widgets.{$widget}.submodule");
		if (empty($submodule)) return false;
		// check if submodule is included
		$temp = explode('.', $submodule);
		array_pop($temp);
		array_unshift($temp, 'submodule');
		array_unshift($temp, 'dep');
		if (empty(application::get($temp))) return false;
		return true;
	}
}