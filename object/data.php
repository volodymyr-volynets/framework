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
	 * Mapping for optmultis() method
	 *
	 * @var array
	 */
	public $optmultis_map = [
		//'column' => ['alias' => '[alias name]', 'model' => '[model name]'],
		//'column' => ['alias' => '[alias name]', 'column' => '[column name]'],
	];

	/**
	 * Initiator class
	 *
	 * @var string
	 */
	public $initiator_class = 'object_data';

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
		// process domain in columns, we skip domain model
		$class = get_called_class();
		if ($class != 'object_data_domains') {
			if ($class == 'object_data_types') {
				$this->columns = object_data_common::process_domains_and_types($this->columns, $this->data);
			} else {
				$this->columns = object_data_common::process_domains_and_types($this->columns);
			}
		}
	}

	/**
	 * Get data
	 *
	 * @param array $options
	 *		where - array of conditions
	 *		pk - primary key to be used by query
	 *		orderby - array of columns to sort by
	 * @return array
	 */
	public function get($options = []) {
		// get available data types
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
					$result[$k][$k2] = $k;
				} else if (!array_key_exists($k2, $v)) {
					$result[$k][$k2] = $v2['default'] ?? $types[$v2['type']]['no_data_type_default'] ?? null;
				} else {
					$result[$k][$k2] = $v[$k2];
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
		// sorting, if none specified we sort by name if its in columns
		$orderby = null;
		if (isset($options['orderby'])) {
			$orderby = $options['orderby'];
		} else if (isset($this->orderby)) {
			$orderby = $this->orderby;
		} else if (isset($this->columns[$this->column_prefix . 'name'])) {
			$orderby = [$this->column_prefix . 'name' => SORT_ASC];
		}
		if (!empty($orderby)) {
			$method = [];
			foreach ($orderby as $k => $v) {
				$type = $types[$this->columns[$k]['type']]['php_type'];
				if ($type == 'integer' || $type == 'float') {
					$method[$k] = SORT_NUMERIC;
				}
			}
			array_key_sort($result, $orderby, $method);
		}
		// if we have primary key
		$pk = $options['pk'] ?? $this->pk;
		if (!empty($pk)) {
			pk($pk, $result);
		}
		return $result;
	}

	/**
	 * Get setting
	 *
	 * @param string $setting
	 * @param string $property
	 * @return mixed
	 */
	public static function get_setting($setting = null, $property = null) {
		$data = self::get_static();
		$keys = [];
		if (isset($setting)) {
			$keys[] = $setting;
			if (isset($property)) {
				$keys[] = $property;
			}
		}
		return array_key_get($data, $keys);
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
	 * @see $this->get()
	 * @return boolean
	 */
	public function exists($options = []) {
		$data = $this->get($options);
		return !empty($data);
	}

	/**
	 * @see $this->get()
	 * @return boolean
	 */
	public static function exists_static($options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->exists($options);
	}

	/**
	 * Options
	 *
	 * @see $this->get()
	 */
	public function options($options = []) {
		$data = $this->get($options);
		// process options_map
		if (isset($options['options_map'])) {
			$options_map = $options['options_map'];
		} else if (!empty($this->options_map)) {
			$options_map = $this->options_map;
		} else {
			$options_map = [$this->column_prefix . 'name' => 'name'];
		}
		// if we need to filter options_active
		if (!empty($options['__options_active'])) {
			$options_active = $this->options_active ? $this->options_active : [$this->column_prefix . 'inactive' => 0];
			$data = object_data_common::filter_active_options($data, $options_active, $options['existing_values'] ?? [], $options['skip_values'] ?? []);
		}
		// build options
		$options['column_prefix'] = $this->column_prefix;
		return object_data_common::build_options($data, $options_map, $this->orderby, $options);
	}

	/**
	 * Active Options
	 *
	 * @param array $options
	 * @return array
	 */
	public function options_active($options = []) {
		$options['__options_active'] = true;
		return $this->options($options);
	}

	/**
	 * Optgroups
	 *
	 * @see $this->get()
	 */
	public function optgroups($options = []) {
		$data = $this->get($options);
		$options_map = !empty($this->options_map) ? $this->options_map : [$this->column_prefix . 'name' => 'name'];
		if (!empty($this->optgroups_map)) {
			$optgroups_map = $this->optgroups_map;
			$optgroups_map['column'] = $optgroups_map['column'];
			return object_data_common::optgroups($data, $optgroups_map, $options_map);
		} else {
			return object_data_common::options($data, $options_map);
		}
	}

	/**
	 * Multi level options
	 *
	 * @see $this->get()
	 */
	public function optmultis($options = []) {
		if (empty($this->optmultis_map)) {
			return [];
		} else {
			$data = $this->get($options);
			$optmultis_map = $this->optmultis_map;
			return object_data_common::optmultis($data, $optmultis_map);
		}
	}
}