<?php

class object_acl_datasources extends object_override_data {

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