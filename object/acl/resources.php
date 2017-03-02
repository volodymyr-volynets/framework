<?php

class object_acl_resources extends object_override_data {

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [
		//'[module]' => '[datasource]'
		/*
		'[type]' => [ // controllers, menu
			'[module]' => [
				'datasource' => '[datasource]',
				'data' => '[data]',
			]
		]
		*/
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		// we need to handle overrrides
		parent::override_handle($this);
	}

	/**
	 * Get
	 *
	 * @param string $type
	 * @param string $module
	 * @return array|string
	 */
	public function get(string $type = '', string $module = '') {
		$result = [];
		foreach ($this->data as $k => $v) {
			if (!empty($type) && $type != $k) continue;
			foreach ($v as $k2 => $v2) {
				if (!empty($module) && $module != $k2) continue;
				// if we have datasource
				if (!empty($v2['datasource'])) {
					$temp = factory::model($v2['datasource'], true)->get();
					$result = array_merge_hard($result, $temp);
				} else if ($v2['data']) { // if we are requresting url
					return $v2['data'];
				}
			}
		}
		return $result;
	}

	/**
	 * Get (static)
	 *
	 * @see $this::get()
	 */
	public static function get_static(string $type = '', string $module = '') {
		return factory::model(get_called_class())->get($type, $module);
	}
}