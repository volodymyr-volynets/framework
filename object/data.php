<?php

class object_data extends object_override_data {

	/**
	 * Primary key columns, used to convert data
	 * Note: we must use full column names with column prefix
	 *
	 * @var array
	 */
	public $pk = [];

	/**
	 * Key in data is this column
	 *
	 * @var string
	 */
	public $column_key;

	/**
	 * Column prefix or table alias
	 *
	 * @var string 
	 */
	public $column_prefix;

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
	 * List of columns to sort by
	 * Format:
	 *		column 1 => asc or SORT_ASC
	 *		column 2 => desc or SORT_DESC
	 * @var array 
	 */
	public $orderby = [];

	/**
	 * Mapping for options() method
	 * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
	 *
	 * @var array
	 */
	public $options_map = [
		//'[data column]' => '[key in array]',
	];

	/**
	 * Condition for options_active() method
	 *
	 * @var type
	 */
	public $options_active = [
		//'[data column]' => [value],
	];

	/**
	 * Mapping for optgroups() method
	 *
	 * @var array
	 */
	public $optgroups_map = [
		//'column' => '[data column]',
		//'model' => '[model name]',
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		// we need to handle overrrides
		parent::override_handle($this);
		// we must have columns
		if (empty($this->columns)) {
			Throw new Exception('object_data ' . get_called_class() . ' children must have columns!');
		}
	}

	/**
	 * Get raw data
	 *
	 * @param array $options
	 *		where - array of conditions
	 *		orderby - array of columns to sort by
	 * @return array
	 */
	public function get($options = []) {
		if (get_called_class() == 'object_data_types') {
			$types = $this->data;
		} else {
			$types = object_data_types::get_static();
		}
		// transform data
		$result = [];
		foreach ($this->data as $k => $v) {
			foreach ($this->columns as $k2 => $v2) {
				if ($this->column_key == $k2) {
					$result[$k][$this->column_prefix . $k2] = $k;
				} else if (!array_key_exists($k2, $v)) {
					$result[$k][$this->column_prefix . $k2] = $v2['default'] ?? $types[$v2['type']]['no_data_type_default'] ?? null;
				} else {
					$result[$k][$this->column_prefix . $k2] = $v[$k2];
				}
			}
		}
		// filtering
		if (!empty($options['where'])) {
			foreach ($result as $k => $v) {
				$found = true;
				foreach ($options['where'] as $k2 => $v2) {
					// todo: add options ad in query
					if (array_key_exists($k2, $v) && $v[$k2] != $v2) {
						$found = false;
						break;
					}
				}
				if (!$found) {
					unset($result[$k]);
				}
			}
		}
		// sorting, if none specified we sort by name
		$orderby = $options['orderby'] ?? (!empty($this->orderby) ? $this->orderby : [$this->column_prefix . 'name' => SORT_ASC]);
		if (!empty($orderby)) {
			$method = [];
			foreach ($orderby as $k => $v) {
				$type = $types[$this->columns[str_replace($this->column_prefix, '', $k)]['type']]['php_type'];
				if ($type == 'integer' || $type == 'float') {
					$method[$k] = SORT_NUMERIC;
				}
			}
			array_key_sort($result, $orderby, $method);
		}
		// if we have primary key
		if (!empty($pk)) {
			pk($pk, $result);
		}
		return $result;
	}

	/**
	 * @see $this->get()
	 */
	public static function get_static($options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->get($options);
	}

	/**
	 * Generate options
	 *
	 * @see $this->get()
	 */
	public function options($options = []) {
		$data = $this->get($options);
		return remap($data, !empty($this->options_map) ? $this->options_map : [$this->column_prefix . 'name' => 'name']);
	}

	/**
	 * Generate optgroups
	 *
	 * @see $this->get()
	 */
	public function optgroups($options = []) {
		$data = $this->get($options);
		$column = !empty($this->optgroups_map['column']) ? $this->optgroups_map['column'] : ($this->column_prefix . 'name');
		$model = !empty($this->optgroups_map['model']) ? $this->optgroups_map['model'] : null;
		if ($model) {
			$object = new $model();
			$model_names = $object->options();
		} else {
			$model_names = [];
		}
		$result = [];
		foreach ($data as $k => $v) {
			if (!isset($result[$v[$column]])) {
				$result[$v[$column]] = [
					'name' => $model_names[$v[$column]]['name'] ?? $v[$column],
					'options' => []
				];
			}
			$result[$v[$column]]['options'][$k] = $v;
		}
		// sorting and remapping
		$options_map = !empty($this->options_map) ? $this->options_map : [$this->column_prefix . 'name' => 'name'];
		foreach ($result as $k => $v) {
			$result[$k]['options'] = remap($result[$k]['options'], $options_map);
		}
		array_key_sort($result, ['name' => SORT_ASC]);
		return $result;
	}

	// todo: add options_active()
}