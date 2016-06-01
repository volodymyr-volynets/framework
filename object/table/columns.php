<?php

class object_table_columns extends object_data {
	public $column_key = 'no_table_column_code';
	public $column_prefix = 'no_table_column_';
	public $orderby = ['no_table_column_name' => SORT_ASC];
	public $columns = [
		'no_table_column_code' => ['name' => 'Attribute', 'type' => 'varchar', 'length' => 30],
		'no_table_column_name' => ['name' => 'Name', 'type' => 'text'],
		'no_table_column_description' => ['name' => 'Description', 'type' => 'text']
	];
	public $data = [
		'name' => ['no_table_column_name' => 'Name', 'no_table_column_description' => 'Name of a column'],
		// ddl related attributes
		'domain' => ['no_table_column_name' => 'Domain', 'no_table_column_description' => 'Domain from object_type_table_domain'],
		'type' => ['no_table_column_name' => 'Data Type', 'no_table_column_description' => 'Datatype from object_type_table_column'],
		'length' => ['no_table_column_name' => 'Length', 'no_table_column_description' => 'String length'],
		'precision' => ['no_table_column_name' => 'Precision', 'no_table_column_description' => 'Numeric precision'],
		'scale' => ['no_table_column_name' => 'Scale', 'no_table_column_description' => 'Numeric scale'],
		'null' => ['no_table_column_name' => 'Null', 'no_table_column_description' => 'Whether column is null'],
		'default' => ['no_table_column_name' => 'Default', 'no_table_column_description' => 'Default value']
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
			$temp = object_data_common::process_domains($temp);
			$column_options = $temp[$column_name];
		}
		//print_r2($column_options);
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
		$result = [];
		// processing as per different data types
		if ($column_options['type'] == 'boolean') {
			$result[$column_name] = !empty($value) ? 1 : 0;
		} else if (in_array($column_options['type'], ['smallserial', 'serial', 'bigserial'])) {
			if (!empty($value)) {
				$result[$column_name] = format::read_intval($value);
			}
		} else if (in_array($column_options['type'], ['smallint', 'integer', 'bigint'])) {
			// if we got empty string we say its null
			if (is_string($value) &&  $value == '') {
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
		} else if ($column_options['type'] == 'numeric') {
			// if we got empty string we say its null
			if (is_string($value) &&  $value == '') {
				$value = null;
			}
			if (is_null($value)) {
				if (!empty($column_options['null']) || !empty($options['ignore_defaults'])) {
					$result[$column_name] = null;
				} else {
					$result[$column_name] = $column_options['default'] ?? 0;
				}
			} else {
				$result[$column_name] = format::read_floatval($value);
			}
		} else if (in_array($column_options['type'], ['date', 'time', 'datetime', 'timestamp'])) {
			$result[$column_name] = format::read_date($value, $column_options['type']);
		} else if ($column_options['type'] == 'json') {
			if (is_null($value)) {
				$result[$column_name] = null;
			} else if (is_array($value)) {
				$result[$column_name] = json_encode($value);
			} else {
				$result[$column_name] = (string) $value;
			}
		} else {
			if (is_null($value)) {
				$result[$column_name] = null;
			} else {
				$result[$column_name] = (string) $value;
			}
		}
		return $result;
	}
}