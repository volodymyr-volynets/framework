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
		$result = $this->data;
		if (!empty($type)) {
			$result = $result[$type] ?? null;
			if (!isset($result)) return $result;
			if (!empty($module)) {
				$result = $result[$module] ?? null;
				if (!isset($result)) return $result;
				if (!empty($key)) {
					$result = $result[$key] ?? null;
					if (!isset($result)) return $result;
				} else if (!empty($result['datasource'])) {
					// acl is skipped intentionally
					return \Factory::model($result['datasource'], true)->get(['skip_acl' => true]);
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