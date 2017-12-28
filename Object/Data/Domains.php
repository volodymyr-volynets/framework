<?php

namespace Object\Data;
class Domains extends \Object\Data {
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
		'php_type' => ['name' => 'PHP Type', 'type' => 'text', 'default' => 'string', 'options_model' => '\Object\Data_php_types'],
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
		'reference' => ['name' => 'Reference', 'type' => 'varchar', 'length' => 255, 'placeholder' => 'Reference'],
		'description' => ['name' => 'Description', 'type' => 'varchar', 'length' => 2000, 'placeholder' => 'Description'],
		'symlink' => ['name' => 'Symlink', 'type' => 'varchar', 'length' => 255, 'placeholder' => 'Symlink'],
		// codes
		'code' => ['name' => 'Code', 'type' => 'varchar', 'length' => 255],
		'short_code' => ['name' => 'Short Code', 'type' => 'varchar', 'length' => 6, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'type_code' => ['name' => 'Type Code', 'type' => 'varchar', 'length' => 15, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'group_code' => ['name' => 'Group Code', 'type' => 'varchar', 'length' => 30, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'token' => ['name' => 'Token', 'type' => 'varchar', 'length' => 255],
		'status_one' => ['name' => 'Status (1)', 'type' => 'char', 'length' => 1],
		'status_two' => ['name' => 'Status (2)', 'type' => 'char', 'length' => 2],
		'promocode' => ['name' => 'Promocode', 'type' => 'varchar', 'length' => 255],
		'barcode' => ['name' => 'Barcode', 'type' => 'varchar', 'length' => 255],
		// types & groups
		'type_id' => ['name' => 'Type #', 'type' => 'smallint', 'default' => null, 'format' => 'id'],
		'type_id_sequence' => ['name' => 'Type #', 'type' => 'smallserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'status_id' => ['name' => 'Status #', 'type' => 'smallint', 'default' => null, 'format' => 'id'],
		'group_id' => ['name' => 'Group #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'group_id_sequence' => ['name' => 'Group #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'big_id' => ['name' => 'Big #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'big_id_sequence' => ['name' => 'Big #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'order' => ['name' => 'Order', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'counter' => ['name' => 'Counter', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'bigcounter' => ['name' => 'Counter (Big)', 'type' => 'bigint', 'default' => 0, 'format' => 'id'],
		// date & time
		'optimistic_lock' => ['name' => 'Optimistic Lock', 'type' => 'timestamp', 'null' => false, 'default' => 'now()', 'format' => '', 'validator_method' => '', 'placeholder' => ''],
		'timestamp_now' => ['name' => 'Timestamp (Now)', 'type' => 'timestamp', 'default' => 'now()', 'null' => false, 'format' => '', 'validator_method' => '', 'placeholder' => ''],
		// personal
		'email' => ['name' => 'Email', 'type' => 'varchar', 'length' => 255, 'validator_method' => '\Object\Validator\Email::validate', 'null' => true],
		'phone' => ['name' => 'Phone', 'type' => 'varchar', 'length' => 50, 'validator_method' => '\Object\Validator\Phone::validate', 'null' => true],
		'personal_name' => ['name' => 'Name (Personal)', 'type' => 'varchar', 'length' => 50],
		'personal_title' => ['name' => 'Title (Personal)', 'type' => 'varchar', 'length' => 10],
		'icon' => ['name' => 'Icon', 'type' => 'varchar', 'length' => 50],
		// login
		'login' => ['name' => 'Login', 'type' => 'varchar', 'length' => 30, 'validator_method' => '\Object\Validator\LowerCase::validate'],
		'password' => ['name' => 'Password', 'type' => 'text', 'validator_method' => '\Object\Validator\Password::validate'],
		'ip' => ['name' => 'IP', 'type' => 'varchar', 'length' => 46],
		'domain_part' => ['name' => 'Domain Part', 'type' => 'varchar', 'length' => 30, 'validator_method' => 'object_validator_domain_part::validate'],
		// S/M System
		'ledger_id' => ['name' => 'Ledger #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'ledger_id_sequence' => ['name' => 'Ledger #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'language_code' => ['name' => 'Language Code', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\LowerCase::validate'],
		'locale_code' => ['name' => 'Locale Code', 'type' => 'varchar', 'length' => 30],
		'timezone_code' => ['name' => 'Timezone Code', 'type' => 'varchar', 'length' => 30],
		// C/M Country Management
		'country_code' => ['name' => 'Country Code', 'type' => 'char', 'length' => 2, 'validator_method' => '\Object\Validator\UpperCase::validate', 'searchable' => true],
		'country_code3' => ['name' => 'Country Code (3)', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'country_number' => ['name' => 'Country Numeric Code', 'type' => 'smallint', 'default' => null],
		'province_code' => ['name' => 'Province Code', 'type' => 'varchar', 'length' => 30, 'validator_method' => '\Object\Validator\UpperCase::validate', 'searchable' => true],
		'postal_code' => ['name' => 'Postal Code', 'type' => 'varchar', 'length' => 15, 'validator_method' => '\Object\Validator\PostalCode::validate'],
		'geo_coordinate' => ['name' => 'Geo Coordinate', 'type' => 'numeric', 'precision' => 10, 'scale' => 6, 'null' => true],
		// C/Y Currency Management
		'currency_type' => ['name' => 'Currency Type', 'type' => 'varchar', 'length' => 12, 'validator_method' => '\Object\Validator\UpperCase::validate', 'searchable' => true],
		'currency_code' => ['name' => 'Currency Code', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Currency', 'searchable' => true],
		'currency_rate_id' => ['name' => 'Currency Rate #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'currency_rate_id_sequence' => ['name' => 'Currency Rate #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'currency_rate' => ['name' => 'Currency Rate', 'type' => 'bcnumeric', 'precision' => 16, 'scale' => 8, 'default' => '1.00000000', 'format' => 'currencyRate', 'align' => 'right'],
		'fraction_digits' => ['name' => 'Fraction Digits', 'type' => 'smallint', 'default' => 2],
		// T/M Tenants
		'tenant_id' => ['name' => 'Tenant #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'tenant_id_sequence' => ['name' => 'Tenant #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'module_code' => ['name' => 'Module Code', 'type' => 'char', 'length' => 2, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'module_id' => ['name' => 'Module #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'module_id_sequence' => ['name' => 'Module #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'feature_code' => ['name' => 'Feature Code', 'type' => 'varchar', 'length' => 30, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'resource_id' => ['name' => 'Resource #', 'type' => 'integer', 'format' => 'id'],
		'resource_id_sequence' => ['name' => 'Resource #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'action_id' => ['name' => 'Action #', 'type' => 'smallint', 'format' => 'id'],
		// U/M User Management
		'user_id' => ['name' => 'User #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'user_id_sequence' => ['name' => 'User #', 'type' => 'serial', 'placeholder' => 'Sequence'],
		'role_id' => ['name' => 'Role #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Role'],
		'role_id_sequence' => ['name' => 'Role #', 'type' => 'serial', 'placeholder' => 'Sequence'],
		'message_id' => ['name' => 'Message #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'message_id_sequence' => ['name' => 'Message #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'weight' => ['name' => 'Weight', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		// O/N Organization Management
		'organization_id' => ['name' => 'Organization #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Organization', 'format' => 'id'],
		'organization_id_sequence' => ['name' => 'Organization #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'location_id' => ['name' => 'Location #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Location', 'format' => 'id'],
		'location_id_sequence' => ['name' => 'Location #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'jurisdiction_id' => ['name' => 'Jurisdiction #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'jurisdiction_id_sequence' => ['name' => 'Jurisdiction #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'authority_id' => ['name' => 'Authority #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'authority_id_sequence' => ['name' => 'Authority #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'sbu_id' => ['name' => 'SBU #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'sbu_id_sequence' => ['name' => 'SBU #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'department_id' => ['name' => 'Department #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'department_id_sequence' => ['name' => 'Department #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'brand_id' => ['name' => 'Brand #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Brand', 'format' => 'id'],
		'brand_id_sequence' => ['name' => 'Brand #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'district_id' => ['name' => 'District #', 'type' => 'integer', 'default' => null, 'placeholder' => 'District', 'format' => 'id'],
		'district_id_sequence' => ['name' => 'District #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'market_id' => ['name' => 'Market #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Market', 'format' => 'id'],
		'market_id_sequence' => ['name' => 'Market #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'region_id' => ['name' => 'Region #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Region', 'format' => 'id'],
		'region_id_sequence' => ['name' => 'Region #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'trademark_id' => ['name' => 'Trademark #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'trademark_id_sequence' => ['name' => 'Trademark #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'cost_center_id' => ['name' => 'Cost Center #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'cost_center_id_sequence' => ['name' => 'Cost Center #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'division_id' => ['name' => 'Division #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'division_id_sequence' => ['name' => 'Division #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// D/T Document Management
		'file_id' => ['name' => 'File #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'file_id_sequence' => ['name' => 'File #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'file_name' => ['name' => 'File Name', 'type' => 'varchar', 'length' => 255],
		'file_extension' => ['name' => 'File Extension', 'type' => 'varchar', 'length' => 30],
		'file_size' => ['name' => 'File Size', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'file_path' => ['name' => 'File Path', 'type' => 'varchar', 'length' => 500],
		// A/M Advertizing Management
		'adcode_id' => ['name' => 'Adcode #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'adcode_id_sequence' => ['name' => 'Adcode #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'promocode_id' => ['name' => 'Promocode #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'promocode_id_sequence' => ['name' => 'Promocode #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// P/M Project Management
		'product_id' => ['name' => 'Product #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'product_id_sequence' => ['name' => 'Product #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// W/W Workflow
		'workflow_id' => ['name' => 'Workflow #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Workflow', 'format' => 'id'],
		'workflow_id_sequence' => ['name' => 'Workflow #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'service_id' => ['name' => 'Service #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'service_id_sequence' => ['name' => 'Service #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'dimension' => ['name' => 'Dimansion', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'version_code' => ['name' => 'Version Code', 'type' => 'varchar', 'length' => 30],
		// relations & models
		'relation_id' => ['name' => 'Relation #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'relation_id_sequence' => ['name' => 'Relation #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// amounts
		'amount' => ['name' => 'Amount', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'align' => 'right'],
		'quantity' => ['name' => 'Quantity', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 4, 'default' => '0.0000', 'format' => 'quantity', 'align' => 'right'],
		'bigamount' => ['name' => 'Amount (Big)', 'type' => 'bcnumeric', 'precision' => 32, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'align' => 'right'],
		'bigquantity' => ['name' => 'Quantity (Big)', 'type' => 'bcnumeric', 'precision' => 32, 'scale' => 4, 'default' => '0.0000', 'format' => 'quantity', 'align' => 'right'],
		// accounting
		'year' => ['name' => 'Year', 'type' => 'smallint', 'validator_method' => '\Object\Validator\Year::validate', 'format' => 'id', 'searchable' => true],
		'period' => ['name' => 'Period', 'type' => 'smallint', 'validator_method' => '\Object\Validator\Period::validate', 'format' => 'id', 'searchable' => true],
		'uom' => ['name' => 'UOM', 'type' => 'varchar', 'length' => 12],
		'cs_segment_delimiter' => ['name' => 'Segment Delimiter', 'type' => 'char', 'length' => 1],
		'cs_account' => ['name' => 'C/S Account', 'type' => 'varchar', 'length' => 109, 'placeholder' => 'Account', 'searchable' => true, 'tree' => true, 'format' => 'id'],
		'gl_source_code' => ['name' => 'Source Code', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'tax_group_id' => ['name' => 'Tax Group #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'tax_group_id_sequence' => ['name' => 'Tax Group #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'document_id' => ['name' => 'Document #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'document_id_sequence' => ['name' => 'Document #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'document_number' => ['name' => 'Document Number', 'type' => 'varchar', 'length' => 30],
		'bank_id' => ['name' => 'Bank #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'bank_id_sequence' => ['name' => 'Bank #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'bank_deposit_number' => ['name' => 'Bank Deposit #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
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
	public function optionsNoSequences($options = []) {
		$data = $this->options($options);
		foreach ($data as $k => $v) {
			if (strpos($k, '_sequence') !== false) {
				unset($data[$k]);
			}
		}
		return $data;
	}
}