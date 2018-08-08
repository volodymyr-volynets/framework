<?php

namespace Object\Table;
class Options extends \Object\Override\Data {

	/**
	 * Column prefix
	 *
	 * @var string
	 */
	public $column_prefix;

	/**
	 * Table default order as array
	 * Format:
	 *		column 1 => asc or SORT_ASC
	 *		column 2 => desc or SORT_DESC
	 *
	 * @var array
	 */
	public $orderby = [];

	/**
	 * Mapping for options(),
	 * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
	 *
	 * @var array
	 */
	public $options_map = [
		//'[table field]' => '[key in array]',
	];

	/**
	 * Condition for options_active()
	 *
	 * @var array
	 */
	public $options_active = [
		//'[table field]' => [value],
	];

	/**
	 * Skip translations in options
	 *
	 * @var boolean
	 */
	public $options_skip_i18n = false;

	/**
	 * Acl get options
	 *
	 * @var array
	 */
	public $acl_get_options;

	/**
	 * Options
	 *
	 * @see $this->get()
	 */
	public function options($options = []) {
		$options['__options'] = true;
		if (!empty($this->options_skip_i18n)) {
			$options['i18n'] = false;
		}
		$data = $this->optionsQueryData($options);
		// process options_map
		if (isset($options['options_map'])) {
			$options_map = $options['options_map'];
		} else if (!empty($this->options_map)) {
			$options_map = $this->options_map;
		} else {
			$options_map = [$this->column_prefix . 'name' => 'name'];
		}
		if (isset($options['options_map_addition'])) {
			$options_map = array_merge_hard($options_map, $options['options_map_addition']);
		}
		// if we need to filter options_active
		if (!empty($options['__options_active'])) {
			$options_active = $this->options_active ? $this->options_active : [$this->column_prefix . 'inactive' => 0];
			$data = \Object\Data\Common::filterActiveOptions($data, $options_active, $options['existing_values'] ?? [], $options['skip_values'] ?? []);
		} else {
			$data = \Object\Data\Common::filterActiveOptions($data, false, $options['existing_values'] ?? [], $options['skip_values'] ?? []);
		}
		// if we need to prepend values based on pk
		if (!empty($options['__prepend_if_key'])) {
			foreach ($options['__prepend_if_key'] as $k => $v) {
				if (!empty($data[$k])) {
					$data[$k]['__prepend_if_key'] = !empty($options['i18n']) ? i18n(null, $v) : $v;
					$options_map['__prepend_if_key'] = 'name';
				}
			}
		}
		// build options
		$options['column_prefix'] = $this->column_prefix;
		return \Object\Data\Common::buildOptions($data, $options_map, $this->orderby, $options);
	}

	/**
	 * Options active
	 *
	 * @see $this->get()
	 */
	public function optionsActive($options = []) {
		$options['__options_active'] = true;
		return $this->options($options);
	}

	/**
	 * Optgroups
	 *
	 * @param array $options
	 * @return array
	 */
	public function optgroups($options = []) {
		if (empty($this->optgroups_map)) {
			return $this->options($options);
		} else {
			$options['options_map_addition'][$this->optgroups_map['column']] = $this->optgroups_map['column'];
			return \Object\Data\Common::optgroups($this->options($options), $this->optgroups_map, ['name' => 'name']);
		}
	}

	/**
	 * Presets
	 *
	 * @see $this->get()
	 */
	public function presets($options = []) {
		$options['__preset'] = true;
		if (empty($options['columns'])) {
			$options['columns'] = [$this->column_prefix . 'name'];
		} else if (!is_array($options['columns'])) {
			$options['columns'] = [$options['columns']];
		}
		$options['options_map'] = [
			'preset_value' => 'name'
		];
		$options['orderby'] = [
			'preset_value' => SORT_ASC
		];
		$options['pk'] = [];
		if (!empty($options['where'])) {
			$options['pk'] = array_keys($options['where']);
		}
		$options['pk'][] = 'preset_value';
		$values_found = $this->options($options);
		foreach ($values_found as $k => $v) {
			$values_found[$k]['__parent'] = '__values_found_all__';
		}
		$values_found['__values_found_all__'] = ['name' => i18n_if('Previously Set Values:', $options['i18n'] ?? false), '__parent' => null, 'disabled' => true];
		// eixsting values
		if (!empty($options['existing_values'])) {
			$existing_values = is_array($options['existing_values']) ? $options['existing_values'] : [$options['existing_values']];
			$found = false;
			foreach ($existing_values as $v) {
				if (empty($values_found[$v])) {
					$found = true;
					$values_found[$v] = ['name' => i18n_if($v, $options['i18n'] ?? false), '__parent' => '__values_existing__'];
				}
			}
			if ($found) {
				$values_found['__values_existing__'] = ['name' => i18n_if('Existing Value(s)', $options['i18n'] ?? false), '__parent' => null];
			}
		}
		// convert to tree
		$values_found = \Helper\Tree::convertByParent($values_found, '__parent');
		$result = [];
		\Helper\Tree::convertTreeToOptionsMulti($values_found, 0, ['name_field' => 'name'], $result);
		return $result;
	}

	/**
	 * Presets active
	 *
	 * @see $this->get()
	 */
	public function presetsActive($options = []) {
		$options['__options_active'] = true;
		return $this->presets($options);
	}

	/**
	 * Query data for options
	 *
	 * @param array $options
	 * @return array
	 */
	public function optionsQueryData(& $options) {
		// column prefix
		if (empty($options['column_prefix'])) {
			$options['column_prefix'] = $this->column_prefix;
		}
		// handle pk
		if (!array_key_exists('pk', $options)) {
			$options['pk'] = $this->pk;
		}
		$pk = $options['pk'];
		// inject tenant
		if ($this->tenant && !isset($options['where'][$this->tenant_column])) {
			$options['where'][$this->tenant_column] = \Tenant::id();
		}
		// if compound key
		if (count($pk) > 1) {
			$temp = $pk;
			$last = array_pop($temp);
			foreach ($temp as $v) {
				if (empty($options['where'][$v])) {
					return [];
				}
			}
		}
		$data = $this->get($options);
		// merge acl returned from get
		if (!empty($this->acl_get_options)) {
			$options = $this->acl_get_options;
		}
		// if compound key
		if (!empty($temp)) {
			foreach ($temp as $v) {
				// if pk is array
				if (!empty($options['where'][$v]) && is_array($options['where'][$v])) {
					$result = [];
					foreach ($options['where'][$v] as $v2) {
						if (isset($data[$v2])) {
							$result = array_merge_hard($result, $data[$v2]);
						}
					}
					$data = $result;
				} else { // regular pk
					if (!isset($data[$options['where'][$v]])) {
						return [];
					}
					$data = $data[$options['where'][$v]];
				}
			}
		}
		return $data;
	}

	/**
	 * Process options existing values and skip values
	 *
	 * @param mixed $value
	 * @param array $existing_values
	 * @param mixed $skip_values
	 * @return bool
	 */
	public static function processOptionsExistingValuesAndSkipValues($value, $existing_values, $skip_values) : bool {
		if (!empty($existing_values)) {
			if (!is_array($existing_values)) $existing_values = [$existing_values];
			if (in_array($value, $existing_values)) return true;
		}
		if (!empty($skip_values) && in_array($value, $skip_values)) return false;
		return true;
	}

	/**
	 * Format key for JSON options
	 *
	 * @param array $key
	 * @return string
	 */
	public static function optionJsonFormatKey(array $key) : string {
		ksort($key);
		return json_encode($key);
	}

	/**
	 * Extract key for JSON options
	 *
	 * @param string $key
	 * @param array $columns
	 * @param string $delimiter
	 * @return string
	 */
	public static function optionJsonExtractKey(string $key, array $columns, string $delimiter = '::') : string {
		$decoded = json_decode($key, true);
		$temp = [];
		foreach ($columns as $v) {
			$temp[] = $decoded[$v];
		}
		return implode($delimiter, $temp);
	}

	/**
	 * Multi level options
	 *
	 * @see $this->get()
	 */
	/*
	 * todo retire
	public function optmultis($options = []) {
		// todo - retire in favour of tree
		if (empty($this->optmultis_map)) {
			return [];
		} else {
			$data = $this->get($options);
			$optmultis_map = $this->optmultis_map;
			return \Object\Data\Common::optmultis($data, $optmultis_map, $options);
		}
	}
	*/
}