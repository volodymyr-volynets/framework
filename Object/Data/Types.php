<?php

namespace Object\Data;
class Types extends \Object\Data {
	public $column_key = 'code';
	public $column_prefix = null; // you must not change it !!!
	public $columns = [
		'code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text'],
		// data attributes
		'default' => ['name' => 'Default', 'type' => 'mixed'],
		'length' => ['name' => 'Length', 'type' => 'smallint'],
		'null' => ['name' => 'Null', 'type' => 'boolean', 'default' => 0],
		'precision' => ['name' => 'Precision', 'type' => 'smallint'],
		'scale' => ['name' => 'Scale', 'type' => 'smallint'],
		'sequence' => ['name' => 'Sequence', 'type' => 'boolean', 'default' => 0],
		'php_type' => ['name' => 'PHP Type', 'type' => 'text', 'default' => 'string', 'options_model' => '\Object\Data_php_types'],
		// misc settings
		'format' => ['name' => 'Format', 'type' => 'text'],
		'format_options' => ['name' => 'Format Params', 'type' => 'mixed'],
		'validator_method' => ['name' => 'Validator Method', 'type' => 'text'],
		'validator_params' => ['name' => 'Validator Params', 'type' => 'mixed'],
		'align' => ['name' => 'Align', 'type' => 'text'],
		'placeholder' => ['name' => 'Placeholder', 'type' => 'text'],
		'searchable' => ['name' => 'Searchable', 'type' => 'boolean'],
		'tree' => ['name' => 'Tree', 'type' => 'boolean'],
		// other
		'is_numeric_key' => ['name' => 'Is Numeric Key', 'type' => 'boolean', 'default' => 0],
	];
	public $data = [
		'boolean' => ['name' => 'Boolean', 'default' => 0, 'null' => 0, 'php_type' => 'integer', 'placeholder' => 'Yes / No'],
		// numeric types
		'smallint' => ['name' => 'Small Integer', 'default' => 0, 'php_type' => 'integer', 'is_numeric_key' => 1],
		'integer' => ['name' => 'Integer', 'default' => 0, 'php_type' => 'integer', 'is_numeric_key' => 1],
		'bigint' => ['name' => 'Big Integer', 'default' => 0, 'php_type' => 'integer', 'is_numeric_key' => 1],
		'numeric' => ['name' => 'Numeric', 'default' => 0, 'php_type' => 'float'],
		'bcnumeric' => ['name' => 'BC Numeric', 'default' => '0', 'php_type' => 'bcnumeric'],
		// todo: add float/double
		// numbers with sequences
		'smallserial' => ['name' => 'Serial Smallint', 'php_type' => 'integer', 'sequence' => 1, 'is_numeric_key' => 1],
		'serial' => ['name' => 'Serial Integer', 'php_type' => 'integer', 'sequence' => 1, 'is_numeric_key' => 1],
		'bigserial' => ['name' => 'Big Serial', 'php_type' => 'integer', 'sequence' => 1, 'is_numeric_key' => 1],
		// text data types
		'char' => ['name' => 'Character', 'php_type' => 'string'],
		'varchar' => ['name' => 'Character Varying', 'php_type' => 'string'],
		'text' => ['name' => 'Text', 'php_type' => 'string'],
		// json types
		'json' => ['name' => 'JSON', 'php_type' => 'mixed'],
		// geometry
		'geometry' => ['name' => 'Geometry', 'php_type' => 'string'],
		// date types
		'date' => ['name' => 'Date', 'php_type' => 'string', 'format' => 'date', 'placeholder' => 'Format::getDatePlaceholder'],
		'time' => ['name' => 'Time', 'php_type' => 'string', 'format' => 'time', 'placeholder' => 'Format::getDatePlaceholder'],
		'datetime' => ['name' => 'Date & Time', 'php_type' => 'string', 'format' => 'datetime', 'placeholder' => 'Format::getDatePlaceholder'],
		'timestamp' => ['name' => 'Timestamp', 'php_type' => 'string', 'format' => 'timestamp', 'placeholder' => 'Format::getDatePlaceholder'],
		// other
		'mixed' => ['name' => 'Mixed', 'php_type' => 'mixed'],
		'array' => ['name' => 'Array', 'php_type' => 'array']
	];

	/**
	 * Options without sequences
	 *
	 * @param array $options
	 * @return array
	 */
	public function optionsNoSequences($options = []) {
		$data = $this->options($options);
		foreach ($data as $k => $v) {
			if (strpos($k, 'serial') !== false) {
				unset($data[$k]);
			}
		}
		return $data;
	}

	/**
	 * Get non sequence type
	 *
	 * @param string $type
	 * @return string
	 */
	public static function getNonSequenceType(string $type) : string {
		if ($type == 'smallserial') return 'smallint';
		if ($type == 'serial') return 'integer';
		if ($type == 'bigserial') return 'bigint';
		return $type;
	}
}