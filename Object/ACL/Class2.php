<?php

namespace Object\ACL;
class Class2 extends \Object\Override\Data {

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [
		/*
		'[acl_key]' => [ // affected model
			'[acl_type]' => [ // get, options, datasource
				'[name]' => [
					'method' => '[model]::[method]',
					'options' => [],
					'order' => -32000				]
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
	 * Acl handle
	 *
	 * @param string $acl_key
	 * @param string $acl_type
	 * @param array $data
	 * @param array $options
	 * @return boolean
	 */
	public function acl_handle($acl_key, $acl_type, & $data, & $options) {
		// if we have no acls we must return null
		if (empty($this->data[$acl_key][$acl_type])) return null;
		// sort
		array_key_sort($this->data[$acl_key][$acl_type], ['order' => SORT_ASC], ['order' => SORT_NUMERIC]);
		// process one by one
		foreach ($this->data[$acl_key][$acl_type] as $k => $v) {
			$method = Factory::method($v['method'], null, true);
			$result = $method[0]->{$method[1]}($acl_key, $acl_type, $data, $options);
			if (!$result) {
				\Debug::$data['acls'][$acl_key][$acl_type] = 'Failed';
				return false;
			} else {
				\Debug::$data['acls'][$acl_key][$acl_type] = 'Success';
			}
		}
		return true;
	}

	/**
	 * Acl initialize
	 *
	 * @param string $acl_key
	 * @param array $options
	 * @return boolean
	 */
	public function acl_init($acl_key, & $data, & $options) {
		return $this->acl_handle($acl_key, 'init', $data, $options);
	}

	/**
	 * Acl finish
	 *
	 * @param string $acl_key
	 * @param array $data
	 * @param array $options
	 * @return boolean
	 */
	public function acl_finish($acl_key, & $data, & $options) {
		return $this->acl_handle($acl_key, 'finish', $data, $options);
	}
}