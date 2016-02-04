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
	 * Get raw data
	 *
	 * @param array $options
	 * @return array
	 */
	public function get($where = [], $options = []) {
		if (empty($this->columns) || empty($this->pk)) {
			$data = $this->data;
		} else {
			// we require additional processing
			Throw new Exception('Not implemented!');
		}
		if (!empty($where)) {
			$temp = [];
			foreach ($data as $k => $v) {
				$found = true;
				foreach ($where as $k2 => $v2) {
					if (!isset($v[$k2]) || $v[$k2] != $v2) {
						$found = false;
					}
				}
				if ($found) {
					$temp[$k] = $v;
				}
			}
			$data = $temp;
		}
		return $data;
	}

	/**
	 * Generate options
	 *
	 * @param array $where
	 * @param array $options
	 * @return array
	 */
	public function options($where = [], $options = []) {
		$data = $this->get($where, $options);
		// remapping
		if ($this->data_options_map) {
			return remap($data, $this->data_options_map);
		} else {
			return $data;
		}
	}

	// todo: add options_active()

	/**
	 * Get data for datasource
	 *
	 * @param array $options
	 * @return array
	 */
	public function get_datasource($options = []) {
		return [
			'success' => true,
			'error' => [],
			'columns' => $this->columns,
			'data' => $this->data
		];
	}
}