<?php

class object_table_columns extends object_data {
	public $column_key = 'code';
	public $column_prefix = null; // must not change it
	public $orderby = [];
	public $columns = [
		'code' => ['name' => 'Attribute Code', 'domain' => 'code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'description' => ['name' => 'Description', 'type' => 'text']
	];
	public $data = [
		'name' => ['name' => 'Name', 'description' => 'Name of a column'],
		// ddl related attributes
		'domain' => ['name' => 'Domain', 'description' => 'Domain from object_data_domains'],
		'type' => ['name' => 'Data Type', 'description' => 'Data Type from object_data_types'],
		'null' => ['name' => 'Null', 'description' => 'Whether column is null'],
		'default' => ['name' => 'Default', 'description' => 'Default value'],
		'length' => ['name' => 'Length', 'description' => 'String length'],
		'precision' => ['name' => 'Precision', 'description' => 'Numeric/Datetime precision'],
		'scale' => ['name' => 'Scale', 'description' => 'Numeric scale'],
		'sequence' => ['name' => 'Sequence', 'description' => 'Indicates that its a sequence'],
		// php attributes
		'php_type' => ['name' => 'PHP Type'],
		// misc attributes
		'format' => ['name' => 'Format'],
		'format_options' => ['name' => 'Format Options'],
		'align' => ['name' => 'Align'],
		'validator_method' => ['name' => 'Validator Method'],
		'validator_params' => ['name' => 'Validator Params'],
		'placeholder' => ['name' => 'Placeholder'],
		'searchable' => ['name' => 'Searchable'],
		'tree' => ['name' => 'Tree']
	];

	/**
	 * Process single column
	 *
	 * @param string $column_name
	 * @param array $column_options
	 * @param array $data
	 * @param array $options
	 * @return array
	 */
	public static function process_single_column($column_name, $column_options, $data, $options = []) {
		$result = [];
		// process domain
		if (!empty($options['process_domains'])) {
			$temp = [$column_name => $column_options];
			$temp = object_data_common::process_domains_and_types($temp);
			$column_options = $temp[$column_name];
		}
		// if we ignoring not set fields
		if (!empty($options['ignore_not_set_fields']) && !array_key_exists($column_name, $data)) {
			return $result;
		}
		// processing
		$value = $data[$column_name] ?? null;
		if (is_array($value)) {
			$result2 = [];
			foreach ($value as $k => $v) {
				$temp = self::process_single_column_type($column_name, $column_options, $v, ['ignore_defaults' => $options['ignore_defaults'] ?? false]);
				if (array_key_exists($column_name, $temp) && $temp[$column_name] !== null) {
					$result2[] = $temp[$column_name];
				}
			}
			$result[$column_name] = $result2;
		} else {
			$result = self::process_single_column_type($column_name, $column_options, $value, ['ignore_defaults' => $options['ignore_defaults'] ?? false]);
		}
		return $result;
	}

	/**
	 * Process single type for column
	 *
	 * @param string $column_name
	 * @param array $column_options
	 * @param mixed $value
	 * @return array
	 */
	public static function process_single_column_type($column_name, $column_options, $value, $options = []) {
		// we need to fix default
		if (isset($column_options['default'])) {
			foreach (['dependent::', 'parent::', 'master_object::', 'static::'] as $v) {
				if (strpos($column_options['default'] . '', $v) !== false) {
					unset($column_options['default']);
				}
			}
		}
		$result = [];
		// processing as per different data types
		if ($column_options['type'] == 'boolean') { // booleans
			$result[$column_name] = !empty($value) ? 1 : 0;
		} else if (in_array($column_options['type'], ['smallserial', 'serial', 'bigserial'])) {
			if (format::read_intval($value, ['valid_check' => 1])) {
				$temp = format::read_intval($value);
				if ($temp !== 0) {
					$result[$column_name] = $temp;
				}
			} else {
				$result[$column_name . '_is_serial_error'] = true;
			}
			$result[$column_name . '_is_serial'] = true;
		} else if (in_array($column_options['type'], ['smallint', 'integer', 'bigint'])) { // integers
			// if we got empty string we say its null
			if (is_string($value) &&  $value === '') {
				$value = null;
			}
			if (is_null($value)) {
				if (!empty($column_options['null']) || !empty($options['ignore_defaults'])) {
					$result[$column_name] = null;
				} else {
					$result[$column_name] = $column_options['default'] ?? 0;
				}
			} else {
				$result[$column_name] = format::read_intval($value);
			}
		} else if (in_array($column_options['type'], ['numeric', 'bcnumeric'])) { // numerics as floats or strings
			// if we got empty string we say its null
			if (is_string($value) &&  $value === '') {
				$value = null;
			}
			if (is_null($value)) {
				if (!empty($column_options['null']) || !empty($options['ignore_defaults'])) {
					$result[$column_name] = null;
				} else {
					$result[$column_name] = $column_options['default'] ?? ($column_options['type'] == 'bcnumeric' ? '0' : 0);
				}
			} else {
				$result[$column_name] = format::read_floatval($value, ['bcnumeric' => $column_options['type'] == 'bcnumeric']);
			}
		} else if (in_array($column_options['type'], ['date', 'time', 'datetime', 'timestamp'])) {
			$result[$column_name] = format::read_date($value, $column_options['type']);
			// for datetime we do additional processing
			if (!empty($options['process_datetime'])) {
				$result[$column_name . '_strtotime_value'] = 0;
				if (!empty($value)) {
					$result[$column_name . '_strtotime_value'] = strtotime($result[$column_name]);
				}
			}
		} else if ($column_options['type'] == 'json') {
			if (!is_json($value)) {
				$result[$column_name] = json_encode($value);
			} else {
				$result[$column_name] = $value;
			}
		} else {
			if (is_null($value)) {
				$result[$column_name] = null;
			} else {
				// we need to convert numeric strings
				if (($column_options['format'] ?? '') == 'id') {
					$result[$column_name] = format::number_to_from_native_language($value, [], true);
				} else {
					$result[$column_name] = (string) $value;
				}
			}
		}
		return $result;
	}
}