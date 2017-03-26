<?php

class object_acl_datasources extends \Object\Override\Data {

	/**
	 * A list of module/datasource
	 *
	 * @var array 
	 */
	public $data = [
		//'[module]' => '[datasource]'
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		// we need to handle overrrides
		parent::override_handle($this);
	}

	/**
	 * Process
	 */
	public function get() {
		$result = [];
		foreach ($this->data as $k => $v) {
			$model = new $v();
			$result = array_merge_hard($result, [$k => $model->get()]);
		}
		return $result;
	}
}