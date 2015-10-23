<?php

class object_data {

	/**
	 * Primary key columns, used to convert data
	 *
	 * @var array
	 */
	public $pk = [];

	/**
	 * A list of available columns
	 *
	 * @var array
	 */
	public $columns = [];

	/**
	 * Data would be here
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Mapping for options() method
	 * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
	 *
	 * @var array
	 */
	public $data_options_map = [
		//'[data column]' => '[key in array]',
	];

	/**
	 * Condition for options_active() method
	 *
	 * @var type
	 */
	public $data_options_active = [
		//'[data column]' => [value],
	];

	/**
	 * Get data for datasource
	 *
	 * @param array $options
	 * @return array
	 */
	public function get_datasource($options = array()) {
		return [
			'success' => true,
			'error' => [],
			'columns' => $this->columns,
			'data' => $this->data
		];
	}
	
	// todo: add options() and options_active() methods
}