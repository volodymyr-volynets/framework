<?php

class object_data_domains extends object_data {
	public $column_key = 'code';
	public $column_prefix = null; // you must not change it !!!
	public $orderby = ['name' => SORT_ASC];
	public $columns = [
		'code' => ['name' => 'Code', 'domain' => 'group_code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		// data attributes
		'type' => ['name' => 'Type', 'type' => 'text'],
		'default' => ['name' => 'Default', 'type' => 'mixed'],
		'length' => ['name' => 'Length', 'type' => 'smallint'],
		'null' => ['name' => 'Null', 'type' => 'boolean', 'default' => 0],
		'precision' => ['name' => 'Precision', 'type' => 'smallint'],
		'scale' => ['name' => 'Scale', 'type' => 'smallint'],
		'php_type' => ['name' => 'PHP Type', 'type' => 'text', 'default' => 'string', 'options_model' => 'object_data_php_types'],
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
	public $data = [
		// general
		'name' => ['name' => 'Name', 'type' => 'varchar', 'length' => 120],
		'reference' => ['name' => 'Reference', 'type' => 'varchar', 'length' => 255],
		'description' => ['name' => 'Description', 'type' => 'varchar', 'length' => 2000],
		// codes
		'code' => ['name' => 'Code', 'type' => 'varchar', 'length' => 255],
		'short_code' => ['name' => 'Short Code', 'type' => 'varchar', 'length' => 6],
		'type_code' => ['name' => 'Type Code', 'type' => 'varchar', 'length' => 15],
		'group_code' => ['name' => 'Group Code', 'type' => 'varchar', 'length' => 30],
		// types & groups
		'type_id' => ['name' => 'Type #', 'type' => 'smallint', 'default' => null, 'format' => 'id'],
		'type_id_sequence' => ['name' => 'Type #', 'type' => 'smallserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'group_id' => ['name' => 'Group #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'group_id_sequence' => ['name' => 'Group #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'big_id' => ['name' => 'Big #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'big_id_sequence' => ['name' => 'Big #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'order' => ['name' => 'Order', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'counter' => ['name' => 'Counter', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'bigcounter' => ['name' => 'Counter (Big)', 'type' => 'bigint', 'default' => 0, 'format' => 'id'],
		// personal
		'email' => ['name' => 'Email', 'type' => 'varchar', 'length' => 255, 'validator_method' => 'object_validator_email::validate', 'null' => true],
		'phone' => ['name' => 'Phone', 'type' => 'varchar', 'length' => 50, 'validator_method' => 'object_validator_phone::validate', 'null' => true],
		'personal_name' => ['name' => 'Name (Personal)', 'type' => 'varchar', 'length' => 50],
		'personal_title' => ['name' => 'Title (Personal)', 'type' => 'varchar', 'length' => 10],
		'icon' => ['name' => 'Icon', 'type' => 'varchar', 'length' => 50],
		// login
		'login' => ['name' => 'Login', 'type' => 'varchar', 'length' => 30],
		'password' => ['name' => 'Password', 'type' => 'text', 'validator_method' => 'object_validator_password::validate'],
		// S/M System
		'ledger_id' => ['name' => 'Ledger #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'ledger_id_sequence' => ['name' => 'Ledger #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'optimistic_lock' => ['name' => 'Optimistic Lock', 'type' => 'timestamp', 'null' => false, 'default' => 'now()', 'format' => '', 'validator_method' => '', 'placeholder' => ''],
		'language_code' => ['name' => 'Language Code', 'type' => 'char', 'length' => 3, 'validator_method' => 'object_validator_lowercase::validate'],
		'currency_code' => ['name' => 'Currency Code', 'type' => 'char', 'length' => 3, 'validator_method' => 'numbers_data_system_internalization_model_currency_validator_codes::validate', 'searchable' => true],
		// C/M Country Management
		'country_code' => ['name' => 'Country Code', 'type' => 'char', 'length' => 2, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'country_code3' => ['name' => 'Country Code (3)', 'type' => 'char', 'length' => 3, 'validator_method' => 'object_validator_uppercase::validate'],
		'country_number' => ['name' => 'Country Numeric Code', 'type' => 'smallint'],
		'province_code' => ['name' => 'Province Code', 'type' => 'varchar', 'length' => 30, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'postal_code' => ['name' => 'Postal Code', 'type' => 'varchar', 'length' => 15, 'validator_method' => 'object_validator_postalcode::validate'],
		'geo_coordinate' => ['name' => 'Geo Coordinate', 'type' => 'numeric', 'precision' => 10, 'scale' => 6, 'null' => true],
		// T/M Tenants
		'tenant_id' => ['name' => 'Tenant #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'tenant_id_sequence' => ['name' => 'Tenant #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'module_code' => ['name' => 'Module Code', 'type' => 'char', 'length' => 2, 'validator_method' => 'object_validator_uppercase::validate'],
		'ledger_id' => ['name' => 'Ledger #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'ledger_id_sequence' => ['name' => 'Ledger #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'feature_code' => ['name' => 'Feature Code', 'type' => 'varchar', 'length' => 15, 'validator_method' => 'object_validator_uppercase::validate'],
		'resource_id' => ['name' => 'Resource #', 'type' => 'integer', 'format' => 'id'],
		'resource_id_sequence' => ['name' => 'Resource #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'action_id' => ['name' => 'Action #', 'type' => 'smallint', 'format' => 'id'],
		// U/M User & Entity Management
		'user_id' => ['name' => 'User #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'user_id_sequence' => ['name' => 'User #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'entity_id' => ['name' => 'Entity #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'entity_id_sequence' => ['name' => 'Entity #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		//'component_id' => ['name' => 'Component #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		//'component_id_sequence' => ['name' => 'Component #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// relations & models
		'relation_id' => ['name' => 'Relation #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'relation_id_sequence' => ['name' => 'Relation #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// amounts
		'amount' => ['name' => 'Amount', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'align' => 'right'],
		'quantity' => ['name' => 'Quantity', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 4, 'default' => '0.0000', 'format' => 'quantity', 'align' => 'right'],
		'currency_rate' => ['name' => 'Currency Rate', 'type' => 'bcnumeric', 'precision' => 16, 'scale' => 8, 'default' => '1.00000000', 'format' => 'currency_rate', 'align' => 'right'],
		'bigamount' => ['name' => 'Amount (Big)', 'type' => 'bcnumeric', 'precision' => 32, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'align' => 'right'],
		'bigquantity' => ['name' => 'Quantity (Big)', 'type' => 'bcnumeric', 'precision' => 32, 'scale' => 4, 'default' => '0.0000', 'format' => 'quantity', 'align' => 'right'],
		// accounting
		'currency_type' => ['name' => 'Currency Type', 'type' => 'varchar', 'length' => 12, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'fraction_digits' => ['name' => 'Fraction Digits', 'type' => 'smallint', 'default' => 2],
		'term_code' => ['name' => 'Term Code', 'type' => 'varchar', 'length' => 12, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'payment_code' => ['name' => 'Payment Code', 'type' => 'varchar', 'length' => 12, 'validator_method' => 'object_validator_uppercase::validate', 'searchable' => true],
		'source_code' => ['name' => 'Source Code', 'type' => 'char', 'length' => 3, 'validator_method' => 'object_validator_uppercase::validate'],
		'year' => ['name' => 'Year', 'type' => 'smallint', 'validator_method' => 'object_validator_year::validate', 'format' => 'id', 'searchable' => true],
		'period' => ['name' => 'Period', 'type' => 'smallint', 'validator_method' => 'object_validator_period::validate', 'format' => 'id'],
		'segment_delimiter' => ['name' => 'Segment Delimiter', 'type' => 'varchar', 'length' => 1],
		'gl_account' => ['name' => 'G/L Account', 'type' => 'varchar', 'length' => 109, 'placeholder' => 'G/L Account', 'searchable' => true, 'tree' => true, 'format' => 'id'],
		'uom' => ['name' => 'UOM', 'type' => 'varchar', 'length' => 12],
		'status' => ['name' => 'Status', 'type' => 'char', 'length' => 1],
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