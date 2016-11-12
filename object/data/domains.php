<?php

class object_data_domains extends object_data {
	public $column_key = 'code';
	public $column_prefix = null; // you must not change it !!!
	public $orderby = ['name' => SORT_ASC];
	public $columns = [
		'code' => ['name' => 'Code', 'domain' => 'group_code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'type' => ['name' => 'Type', 'type' => 'text'],
		'default' => ['name' => 'Default', 'type' => 'mixed'],
		'length' => ['name' => 'Length', 'type' => 'smallint'],
		'null' => ['name' => 'Null', 'type' => 'boolean', 'default' => 0],
		'precision' => ['name' => 'Precision', 'type' => 'smallint'],
		'scale' => ['name' => 'Scale', 'type' => 'smallint'],
		// misc settings
		'format' => ['name' => 'Format', 'type' => 'text'],
		'format_options' => ['name' => 'Format Params', 'type' => 'mixed'],
		'validator_method' => ['name' => 'Validator Method', 'type' => 'text'],
		'validator_params' => ['name' => 'Validator Params', 'type' => 'mixed'],
		'align' => ['name' => 'Align', 'type' => 'text'],
		'placeholder' => ['name' => 'Placeholder', 'type' => 'text'],
		'searchable' => ['name' => 'Searchable', 'type' => 'boolean'],
		'tree' => ['name' => 'Tree', 'type' => 'boolean']
	];
	// todo: refactor
	public $data = [
		// general
		'name' => ['name' => 'Name', 'type' => 'varchar', 'length' => 120],
		'code' => ['name' => 'Code', 'type' => 'varchar', 'length' => 255],
		'type_id' => ['name' => 'Type #', 'type' => 'smallint', 'format' => 'id'],
		'type_id_sequence' => ['name' => 'Type #', 'type' => 'smallserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'type_code' => ['name' => 'Type Code', 'type' => 'varchar', 'length' => 15],
		'group_id' => ['name' => 'Group #', 'type' => 'integer', 'format' => 'id'],
		'group_id_sequence' => ['name' => 'Group #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'group_code' => ['name' => 'Group Code', 'type' => 'varchar', 'length' => 30],
		'big_id' => ['name' => 'Big #', 'type' => 'bigint', 'format' => 'id'],
		'big_id_sequence' => ['name' => 'Big #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'order' => ['name' => 'Order', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'counter' => ['name' => 'Counter', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'email' => ['name' => 'Email', 'type' => 'varchar', 'length' => 255, 'validator_method' => 'object_validator_email::validate', 'null' => true],
		'phone' => ['name' => 'Phone', 'type' => 'varchar', 'length' => 50, 'validator_method' => 'object_validator_phone::validate', 'null' => true],
		'personal_name' => ['name' => 'Name (Personal)', 'type' => 'varchar', 'length' => 50],
		'personal_title' => ['name' => 'Title (Personal)', 'type' => 'varchar', 'length' => 10],
		'icon' => ['name' => 'Icon', 'type' => 'varchar', 'length' => 50],
		// login related
		'login' => ['name' => 'Login', 'type' => 'varchar', 'length' => 30],
		'password' => ['name' => 'Password', 'type' => 'text', 'validator_method' => 'object_validator_password::validate'],
		// system
		'controller_id' => ['name' => 'Controller #', 'type' => 'integer', 'format' => 'id'],
		'controller_id_sequence' => ['name' => 'Controller #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'action_id' => ['name' => 'Action #', 'type' => 'smallint', 'format' => 'id'],
		'optimistic_lock' => ['name' => 'Optimistic Lock', 'type' => 'timestamp', 'null' => false, 'default' => 'now()', 'format' => '', 'validator_method' => '', 'placeholder' => ''],
		'language_code' => ['name' => 'Language Code', 'type' => 'char', 'length' => 3, 'validator_method' => 'object_validator_lowercase::validate'],
		'country_code' => ['name' => 'Country Code', 'type' => 'char', 'length' => 2, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'country_code3' => ['name' => 'Country Code (3)', 'type' => 'char', 'length' => 3, 'validator_method' => 'object_validator_uppercase::validate'],
		'province_code' => ['name' => 'Province Code', 'type' => 'varchar', 'length' => 30, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'postal_code' => ['name' => 'Postal Code', 'type' => 'varchar', 'length' => 15, 'validator_method' => 'object_validator_postalcode::validate'],
		'geo_coordinate' => ['name' => 'Geo Coordinate', 'type' => 'numeric', 'precision' => 10, 'scale' => 6, 'null' => true],
		// entities
		'entity_id' => ['name' => 'Entity #', 'type' => 'integer', 'format' => 'id'],
		'entity_id_sequence' => ['name' => 'Entity #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id', 'format' => 'id'],
		// relations & models
		'relation_id' => ['name' => 'Relation #', 'type' => 'bigint', 'format' => 'id'],
		'relation_id_sequence' => ['name' => 'Relation #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'format' => 'id'],
		// amounts
		'amount' => ['name' => 'Amount', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'format_options' => ['decimals' => 2], 'align' => 'right'],
		'quantity' => ['name' => 'Quantity', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 4, 'default' => '0.0000', 'format' => 'quantity', 'align' => 'right'],
		'currency_rate' => ['name' => 'Currency Rate', 'type' => 'bcnumeric', 'precision' => 16, 'scale' => 8, 'default' => '1.00000000', 'format' => 'currency_rate', 'format_options' => ['decimals' => 8], 'align' => 'right'],
		'bigamount' => ['name' => 'Amount (Big)', 'type' => 'bcnumeric', 'precision' => 32, 'scale' => 8, 'default' => '0.00000000', 'format' => 'amount', 'format_options' => ['decimals' => 8], 'align' => 'right'],
		// accounting
		'currency_code' => ['name' => 'Currency Code', 'type' => 'char', 'length' => 3, 'validator_method' => 'numbers_data_accounting_cs_model_currency_validator_codes::validate', 'searchable' => true],
		'currency_type' => ['name' => 'Currency Type', 'type' => 'varchar', 'length' => 12, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'fraction_digits' => ['name' => 'Fraction Digits', 'type' => 'smallint', 'default' => 2],
		'term_code' => ['name' => 'Term Code', 'type' => 'varchar', 'length' => 12, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'payment_code' => ['name' => 'Payment Code', 'type' => 'varchar', 'length' => 12, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'ledger_code' => ['name' => 'Ledger Code', 'type' => 'char', 'length' => 2, 'validator_method' => 'object_validator_uppercase::validate'],
		'source_code' => ['name' => 'Source Code', 'type' => 'char', 'length' => 3, 'validator_method' => 'object_validator_uppercase::validate'],
		'year' => ['name' => 'Year', 'type' => 'smallint', 'validator_method' => 'object_validator_year::validate', 'format' => 'id'],
		'period' => ['name' => 'Period', 'type' => 'smallint', 'validator_method' => 'object_validator_period::validate', 'format' => 'id'],
		'segment_delimiter' => ['name' => 'Segment Delimiter', 'type' => 'varchar', 'length' => 1],
		'gl_account' => ['name' => 'G/L Account', 'type' => 'varchar', 'length' => 109, 'placeholder' => 'G/L Account', 'searchable' => true, 'tree' => true, 'format' => 'id'],
		'uom' => ['name' => 'UOM', 'type' => 'varchar', 'length' => 12],
		'status' => ['name' => 'Status', 'type' => 'char', 'length' => 1],
		'reference' => ['name' => 'Reference', 'type' => 'varchar', 'length' => 255],
		// html
		'html_color_code' => ['name' => 'HTML Color Code', 'type' => 'char', 'length' => 6, 'null' => true],
		'html_color_group' => ['name' => 'HTML Color Group', 'type' => 'varchar', 'length' => 30, 'null' => true]
	];

	/**
	 * Options without sequences
	 *
	 * @param array $options
	 * @return array
	 */
	public function options_no_sequences($options = []) {
		$data = $this->options($options);
		foreach ($data as $k => $v) {
			if (strpos($k, '_sequence') !== false) {
				unset($data[$k]);
			}
		}
		return $data;
	}
}