<?php

namespace Object\ACL;
class Resources extends \Object\Override\Data {

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
		parent::overrideHandle($this);
	}

	/**
	 * Get
	 *
	 * @param string $type
	 * @param string $module
	 * @param string $key
	 * @return array|string
	 */
	public function get(string $type = '', string $module = '', $key = null) {
		$result = [];
		foreach ($this->data as $k => $v) {
			if (!empty($type) && $type != $k) continue;
			foreach ($v as $k2 => $v2) {
				if (!empty($module) && $module != $k2) continue;
				// if we have datasource
				if (!empty($v2['datasource'])) {
					// acl is skipped intentionally
					$temp = \Factory::model($v2['datasource'], true)->get(['skip_acl' => true]);
					$result = array_merge_hard($result, $temp);
				} else if (array_key_exists($key, $v2)) {
					return $v2[$key];
				} else {
					return $v2;
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
	public static function getStatic(string $type = '', string $module = '', $key = null) {
		return \Factory::model('\\' . get_called_class())->get($type, $module, $key);
	}
}