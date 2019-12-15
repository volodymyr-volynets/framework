<?php

namespace Object\Data;
class Domains extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Data Domains';
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
		'php_type' => ['name' => 'PHP Type', 'type' => 'text', 'default' => 'string', 'options_model' => '\Object\Data\PHP\Types'],
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
		'name' => ['name' => 'Name', 'type' => 'varchar', 'length' => 120, 'placeholder' => 'Name'],
		'reference' => ['name' => 'Reference', 'type' => 'varchar', 'length' => 255, 'placeholder' => 'Reference'],
		'description' => ['name' => 'Description', 'type' => 'varchar', 'length' => 2000, 'placeholder' => 'Description'],
		'comment' => ['name' => 'Comment', 'type' => 'text', 'placeholder' => 'Comment'],
		'symlink' => ['name' => 'Symlink', 'type' => 'varchar', 'length' => 255, 'placeholder' => 'Symlink'],
		'address' => ['name' => 'Address', 'type' => 'varchar', 'length' => 255, 'placeholder' => 'Address'],
		'city' => ['name' => 'City', 'type' => 'varchar', 'length' => 255, 'placeholder' => 'City'],
		// codes
		'code' => ['name' => 'Code', 'type' => 'varchar', 'length' => 255],
		'short_code' => ['name' => 'Short Code', 'type' => 'varchar', 'length' => 6, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'type_code' => ['name' => 'Type Code', 'type' => 'varchar', 'length' => 15, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Type'],
		'group_code' => ['name' => 'Group Code', 'type' => 'varchar', 'length' => 30, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'token' => ['name' => 'Token', 'type' => 'varchar', 'length' => 255],
		'hash' => ['name' => 'Hash', 'type' => 'varchar', 'length' => 255],
		'status_one' => ['name' => 'Status (1)', 'type' => 'char', 'length' => 1],
		'status_two' => ['name' => 'Status (2)', 'type' => 'char', 'length' => 2],
		'promocode' => ['name' => 'Promocode', 'type' => 'varchar', 'length' => 255],
		'barcode' => ['name' => 'Barcode', 'type' => 'varchar', 'length' => 255],
		'title_number' => ['name' => 'Title Number', 'type' => 'varchar', 'length' => 225, 'placeholder' => 'xxx.xxx.xxx'],
		// types & groups
		'type_id' => ['name' => 'Type #', 'type' => 'smallint', 'default' => null, 'format' => 'id', 'placeholder' => 'Type'],
		'type_id_sequence' => ['name' => 'Type #', 'type' => 'smallserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'status_id' => ['name' => 'Status #', 'type' => 'smallint', 'default' => null, 'format' => 'id'],
		'group_id' => ['name' => 'Group #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Group'],
		'group_id_sequence' => ['name' => 'Group #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'category_id' => ['name' => 'Category #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Category', 'searchable' => true],
		'category_id_sequence' => ['name' => 'Category #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'big_id' => ['name' => 'Big #', 'type' => 'bigint', 'default' => null, 'format' => 'id', 'placeholder' => 'Big #'],
		'big_id_sequence' => ['name' => 'Big #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'order' => ['name' => 'Order', 'type' => 'integer', 'default' => 0, 'format' => 'id', 'placeholder' => 'Order'],
		'big_order' => ['name' => 'Order (Big)', 'type' => 'bigint', 'default' => 0, 'format' => 'id', 'placeholder' => 'Order'],
		'counter' => ['name' => 'Counter', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'bigcounter' => ['name' => 'Counter (Big)', 'type' => 'bigint', 'default' => 0, 'format' => 'id'],
		// date & time
		'optimistic_lock' => ['name' => 'Optimistic Lock', 'type' => 'timestamp', 'null' => false, 'default' => 'now()', 'format' => '', 'validator_method' => '', 'placeholder' => ''],
		'timestamp_now' => ['name' => 'Timestamp (Now)', 'type' => 'timestamp', 'default' => 'now()', 'null' => false, 'format' => '', 'validator_method' => '', 'placeholder' => ''],
		// personal
		'email' => ['name' => 'Email', 'type' => 'varchar', 'length' => 255, 'validator_method' => '\Object\Validator\Email::validate', 'null' => true],
		'subject' => ['name' => 'Subject', 'type' => 'varchar', 'length' => 255, 'null' => true],
		'phone' => ['name' => 'Phone', 'type' => 'varchar', 'length' => 50, 'validator_method' => '\Object\Validator\Phone::validate', 'null' => true],
		'numeric_phone' => ['name' => 'Numeric Phone', 'type' => 'bigint', 'default' => null, 'format' => 'id', 'placeholder' => 'Phone'],
		'personal_name' => ['name' => 'Name (Personal)', 'type' => 'varchar', 'length' => 50, 'placeholder' => 'Name'],
		'personal_title' => ['name' => 'Title (Personal)', 'type' => 'varchar', 'length' => 10, 'placeholder' => 'Title'],
		'icon' => ['name' => 'Icon', 'type' => 'varchar', 'length' => 50, 'placeholder' => 'Icon', 'searchable' => true],
		'signature' => ['name' => 'Signature', 'type' => 'text', 'null' => true],
		// login
		'login' => ['name' => 'Login', 'type' => 'varchar', 'length' => 30, 'validator_method' => '\Object\Validator\LowerCase::validate'],
		'password' => ['name' => 'Password', 'type' => 'text', 'validator_method' => '\Object\Validator\Password::validate'],
		'encrypted_password' => ['name' => 'Password (Encrypted)', 'type' => 'bytea'],
		'ip' => ['name' => 'IP', 'type' => 'varchar', 'length' => 46, 'placeholder' => 'IP'],
		'domain_part' => ['name' => 'Domain Part', 'type' => 'varchar', 'length' => 30, 'validator_method' => 'object_validator_domain_part::validate'],
		// system
		'ledger_id' => ['name' => 'Ledger #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'ledger_id_sequence' => ['name' => 'Ledger #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'language_code' => ['name' => 'Language Code', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\LowerCase::validate', 'placeholder' => 'Language'],
		'locale_code' => ['name' => 'Locale Code', 'type' => 'varchar', 'length' => 30, 'placeholder' => 'Locale'],
		'timezone_code' => ['name' => 'Timezone Code', 'type' => 'varchar', 'length' => 30, 'placeholder' => 'Timezone'],
		'module_code' => ['name' => 'Module Code', 'type' => 'char', 'length' => 2, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Module'],
		'module_id' => ['name' => 'Module #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Module', 'format' => 'id'],
		'module_id_sequence' => ['name' => 'Module #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'feature_code' => ['name' => 'Feature Code', 'type' => 'varchar', 'length' => 63, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Feature'],
		'resource_id' => ['name' => 'Resource #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Resource'],
		'resource_id_sequence' => ['name' => 'Resource #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'action_id' => ['name' => 'Action #', 'type' => 'smallint', 'format' => 'id', 'placeholder' => 'Action'],
		'field_code' => ['name' => 'Field Code', 'type' => 'varchar', 'length' => 63],
		'field_id' => ['name' => 'Field #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Field', 'searchable' => true],
		'field_id_sequence' => ['name' => 'Field #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'attribute_id' => ['name' => 'Attribute #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Attribute'],
		'attribute_id_sequence' => ['name' => 'Attribute #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'service_id' => ['name' => 'Service #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Service', 'format' => 'id', 'searchable' => true],
		'service_id_sequence' => ['name' => 'Service #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'disposition_id' => ['name' => 'Disposition #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Disposition', 'format' => 'id', 'searchable' => true],
		'disposition_id_sequence' => ['name' => 'Disposition #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'model_id' => ['name' => 'Model #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Model', 'format' => 'id', 'searchable' => true],
		'model_id_sequence' => ['name' => 'Model #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'assignment_id' => ['name' => 'Assignment #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Assignment'],
		'assignment_id_sequence' => ['name' => 'Assignment #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'version_id' => ['name' => 'Version #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Version'],
		'version_id_sequence' => ['name' => 'Version #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'page_id' => ['name' => 'Page #', 'type' => 'bigint', 'default' => null, 'format' => 'id', 'placeholder' => 'Page'],
		'page_id_sequence' => ['name' => 'Page #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'fragment_id' => ['name' => 'Fragment #', 'type' => 'bigint', 'default' => null, 'format' => 'id', 'placeholder' => 'Fragment'],
		'fragment_id_sequence' => ['name' => 'Fragment #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'system_id' => ['name' => 'System #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'System', 'format' => 'id', 'searchable' => true],
		'system_id_sequence' => ['name' => 'System #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true],
		'server_id' => ['name' => 'Server #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Server', 'format' => 'id', 'searchable' => true],
		'server_id_sequence' => ['name' => 'Server #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true],
		'change_request_id' => ['name' => 'Change Request #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Change Request', 'format' => 'id', 'searchable' => true],
		'change_request_id_sequence' => ['name' => 'Change Request #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true],
		'board_id' => ['name' => 'Board #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Board', 'format' => 'id', 'searchable' => true],
		'board_id_sequence' => ['name' => 'Board #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true],
		'lane_id' => ['name' => 'Lane #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Lane', 'format' => 'id', 'searchable' => true],
		'lane_id_sequence' => ['name' => 'Lane #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true],
		'position' => ['name' => 'Position', 'type' => 'integer', 'default' => 0, 'format' => 'id', 'placeholder' => 'Position'],
		'cols' => ['name' => 'Columns', 'type' => 'integer', 'default' => 0, 'format' => 'id', 'placeholder' => 'Column'],
		'rows' => ['name' => 'Rows', 'type' => 'integer', 'default' => 0, 'format' => 'id', 'placeholder' => 'Row'],
		// BI
		'datasource_id' => ['name' => 'Data Source #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Data Source', 'format' => 'id', 'searchable' => true],
		'datasource_id_sequence' => ['name' => 'Data Source #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true],
		'sql_query' => ['name' => 'SQL Query', 'type' => 'varchar', 'length' => 5000, 'placeholder' => 'SQL Query'],
		// country management
		'country_code' => ['name' => 'Country Code', 'type' => 'char', 'length' => 2, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Country', 'searchable' => true],
		'country_code3' => ['name' => 'Country Code (3)', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'country_number' => ['name' => 'Country Numeric Code', 'type' => 'smallint', 'default' => null],
		'province_code' => ['name' => 'Province Code', 'type' => 'varchar', 'length' => 30, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Province', 'searchable' => true],
		'postal_code' => ['name' => 'Postal Code', 'type' => 'varchar', 'length' => 15, 'validator_method' => '\Object\Validator\PostalCode::validate'],
		'postal_codes' => ['name' => 'Postal Codes', 'type' => 'varchar', 'length' => 2000, 'placeholder' => 'Postal Code(s)'],
		'geo_coordinate' => ['name' => 'Geo Coordinate', 'type' => 'numeric', 'precision' => 14, 'scale' => 10, 'placeholder' => 'Coordinate', 'null' => true, 'default' => null],
		// currency management
		'currency_type' => ['name' => 'Currency Type', 'type' => 'varchar', 'length' => 12, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Currency Type', 'searchable' => true],
		'currency_code' => ['name' => 'Currency Code', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\UpperCase::validate', 'placeholder' => 'Currency', 'searchable' => true],
		'currency_rate_id' => ['name' => 'Currency Rate #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'currency_rate_id_sequence' => ['name' => 'Currency Rate #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'currency_rate' => ['name' => 'Currency Rate', 'type' => 'bcnumeric', 'precision' => 16, 'scale' => 8, 'default' => '1.00000000', 'format' => 'currencyRate', 'align' => 'right'],
		'fraction_digits' => ['name' => 'Fraction Digits', 'type' => 'smallint', 'default' => 2],
		// tenants
		'tenant_id' => ['name' => 'Tenant #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'tenant_id_sequence' => ['name' => 'Tenant #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'folder_id' => ['name' => 'Folder #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Folder', 'tree' => true],
		'folder_id_sequence' => ['name' => 'Folder #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'policy_id' => ['name' => 'Policy #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Policy'],
		'policy_id_sequence' => ['name' => 'Policy #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// user management
		'user_id' => ['name' => 'User #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'User', 'format' => 'id', 'searchable' => true],
		'user_id_sequence' => ['name' => 'User #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true],
		'role_id' => ['name' => 'Role #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Role'],
		'role_id_sequence' => ['name' => 'Role #', 'type' => 'serial', 'placeholder' => 'Sequence'],
		'message_id' => ['name' => 'Message #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'message_id_sequence' => ['name' => 'Message #', 'type' => 'bigserial', 'placeholder' => 'Sequence'],
		'weight' => ['name' => 'Weight', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'team_id' => ['name' => 'Team #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Team', 'format' => 'id'],
		'team_id_sequence' => ['name' => 'Team #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'shift_id' => ['name' => 'Shift #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Shift', 'format' => 'id'],
		'shift_id_sequence' => ['name' => 'Shift #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'holiday_id' => ['name' => 'Holiday #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Holiday', 'format' => 'id'],
		'holiday_id_sequence' => ['name' => 'Holiday #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'interval_id' => ['name' => 'Interval #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Interval', 'format' => 'id'],
		'interval_id_sequence' => ['name' => 'Interval #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// organization management
		'organization_id' => ['name' => 'Organization #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Organization', 'format' => 'id'],
		'organization_id_sequence' => ['name' => 'Organization #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'location_id' => ['name' => 'Location #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Location', 'format' => 'id'],
		'location_id_sequence' => ['name' => 'Location #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'zone_id' => ['name' => 'Zone #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Zone', 'format' => 'id'],
		'zone_id_sequence' => ['name' => 'Zone #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'location_number' => ['name' => 'Location Number', 'type' => 'integer', 'default' => null, 'placeholder' => 'Location Number', 'format' => 'id'],
		'jurisdiction_id' => ['name' => 'Jurisdiction #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Jurisdiction'],
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
		'item_master_id' => ['name' => 'Item Master #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Item Master', 'format' => 'id'],
		'item_master_id_sequence' => ['name' => 'Item Master #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'territory_id' => ['name' => 'Territory #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Territory', 'format' => 'id', 'searchable' => true, 'tree' => true],
		'territory_id_sequence' => ['name' => 'Territory #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id', 'searchable' => true, 'tree' => true],
		'channel_id' => ['name' => 'Channel #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Channel', 'format' => 'id'],
		'channel_id_sequence' => ['name' => 'Channel #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'queue_id' => ['name' => 'Queue #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Queue', 'format' => 'id'],
		'queue_id_sequence' => ['name' => 'Queue #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'step_id' => ['name' => 'Step #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Step', 'format' => 'id'],
		'step_id_sequence' => ['name' => 'Step #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'canvas_id' => ['name' => 'Canvas #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Canvas', 'format' => 'id'],
		'canvas_id_sequence' => ['name' => 'Canvas #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'dashboard_id' => ['name' => 'Dashboard #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Dashboard', 'searchable' => true],
		'dashboard_id_sequence' => ['name' => 'Dashboard #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'day_id' => ['name' => 'Day #', 'type' => 'smallint', 'placeholder' => 'Day', 'format' => 'id'],
		// order management
		'lead_id' => ['name' => 'Lead #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Lead', 'format' => 'id'],
		'lead_id_sequence' => ['name' => 'Lead #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'order_id' => ['name' => 'Order #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Order', 'format' => 'id'],
		'order_id_sequence' => ['name' => 'Order #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'job_id' => ['name' => 'Job #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Job', 'format' => 'id'],
		'job_id_sequence' => ['name' => 'Job #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'asset_id' => ['name' => 'Asset #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Asset', 'format' => 'id'],
		'asset_id_sequence' => ['name' => 'Asset #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// document management
		'file_id' => ['name' => 'File #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'file_id_sequence' => ['name' => 'File #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'file_name' => ['name' => 'File Name', 'type' => 'varchar', 'length' => 255],
		'file_extension' => ['name' => 'File Extension', 'type' => 'varchar', 'length' => 30],
		'file_size' => ['name' => 'File Size', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'file_path' => ['name' => 'File Path', 'type' => 'varchar', 'length' => 500],
		// advertizing management
		'adcode_id' => ['name' => 'Adcode #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'adcode_id_sequence' => ['name' => 'Adcode #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'promocode_id' => ['name' => 'Promocode #', 'type' => 'integer', 'default' => null, 'format' => 'id'],
		'promocode_id_sequence' => ['name' => 'Promocode #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// project management
		'product_id' => ['name' => 'Product #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Product', 'format' => 'id'],
		'product_id_sequence' => ['name' => 'Product #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'project_id' => ['name' => 'Project #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Project', 'format' => 'id'],
		'project_id_sequence' => ['name' => 'Project #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'repository_id' => ['name' => 'Repository #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Repository', 'format' => 'id'],
		'repository_id_sequence' => ['name' => 'Repository #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// services
		'workflow_id' => ['name' => 'Workflow #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Workflow', 'format' => 'id', 'searchable' => true],
		'workflow_id_sequence' => ['name' => 'Workflow #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'executed_workflow_id' => ['name' => 'Executed Workflow #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Executed Workflow', 'format' => 'id'],
		'executed_workflow_id_sequence' => ['name' => 'Workflow #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'dimension' => ['name' => 'Dimension', 'type' => 'integer', 'default' => 0, 'format' => 'id'],
		'version_code' => ['name' => 'Version Code', 'type' => 'varchar', 'length' => 30, 'placeholder' => 'Version Code'],
		'service_script_id' => ['name' => 'Service Script #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Service Script', 'format' => 'id', 'searchable' => true],
		'service_script_id_sequence' => ['name' => 'Service Script #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'executed_service_script_id' => ['name' => 'Executed Service Script #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Executed Service Script', 'format' => 'id'],
		'executed_service_script_id_sequence' => ['name' => 'Executed Service Script #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'question_id' => ['name' => 'Question #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Question', 'format' => 'id', 'searchable' => true],
		'question_id_sequence' => ['name' => 'Question #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'print_template_id' => ['name' => 'Print Template #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Print Template', 'format' => 'id', 'searchable' => true],
		'print_template_id_sequence' => ['name' => 'Print Template #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// relations & models
		'relation_id' => ['name' => 'Relation #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'relation_id_sequence' => ['name' => 'Relation #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		// amounts
		'amount' => ['name' => 'Amount', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'align' => 'right'],
		'quantity' => ['name' => 'Quantity', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 4, 'default' => '0.0000', 'format' => 'quantity', 'align' => 'right'],
		'bigamount' => ['name' => 'Amount (Big)', 'type' => 'bcnumeric', 'precision' => 32, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'align' => 'right'],
		'bigquantity' => ['name' => 'Quantity (Big)', 'type' => 'bcnumeric', 'precision' => 32, 'scale' => 4, 'default' => '0.0000', 'format' => 'quantity', 'align' => 'right'],
		'quantity_int' => ['name' => 'Quantity (Integer)', 'type' => 'integer', 'default' => 0, 'format' => 'id', 'placeholder' => 'Quantity'],
		'percent' => ['name' => 'Percent', 'type' => 'integer', 'default' => 0, 'format' => 'id', 'placeholder' => 'Percent (%)'],
		'percent_float' => ['name' => 'Percent (Float)', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 2, 'default' => 0, 'format' => 'id', 'placeholder' => 'Percent (%)', 'default' => '0.00', 'format' => 'number', 'align' => 'right'],
		'unit_price' => ['name' => 'Unit Price', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 4, 'default' => '0.0000', 'format' => 'unitPrice', 'align' => 'right'],
		// accounting
		'classification_id' => ['name' => 'Classification #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Classification'],
		'classification_id_sequence' => ['name' => 'Classification #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'customer_id' => ['name' => 'Customer #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Customer', 'format' => 'id'],
		'customer_id_sequence' => ['name' => 'Customer #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'vendor_id' => ['name' => 'Vendor #', 'type' => 'bigint', 'default' => null, 'placeholder' => 'Vendor', 'format' => 'id'],
		'vendor_id_sequence' => ['name' => 'Vendor #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'year' => ['name' => 'Year', 'type' => 'smallint', 'validator_method' => '\Object\Validator\Year::validate', 'format' => 'id', 'searchable' => true],
		'period' => ['name' => 'Period', 'type' => 'smallint', 'validator_method' => '\Object\Validator\Period::validate', 'format' => 'id', 'searchable' => true],
		'uom' => ['name' => 'UOM', 'type' => 'varchar', 'length' => 15, 'placeholder' => 'Unit of Measure'],
		'uom_id' => ['name' => 'UoM #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Unit of Measure', 'format' => 'id'],
		'uom_id_sequence' => ['name' => 'UoM #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'cs_segment_delimiter' => ['name' => 'Segment Delimiter', 'type' => 'char', 'length' => 1],
		'cs_account' => ['name' => 'C/S Account', 'type' => 'varchar', 'length' => 109, 'placeholder' => 'Account', 'searchable' => true, 'tree' => true, 'format' => 'id'],
		'gl_source_code' => ['name' => 'Source Code', 'type' => 'char', 'length' => 3, 'validator_method' => '\Object\Validator\UpperCase::validate'],
		'tax_group_id' => ['name' => 'Tax Group #', 'type' => 'integer', 'default' => null, 'placeholder' => 'Tax Group', 'format' => 'id'],
		'tax_group_id_sequence' => ['name' => 'Tax Group #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'term_id' => ['name' => 'Term #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Term'],
		'term_id_sequence' => ['name' => 'Term #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'document_id' => ['name' => 'Document #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'document_id_sequence' => ['name' => 'Document #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'reconciliation_id' => ['name' => 'Reconciliation #', 'type' => 'bigint', 'default' => null, 'format' => 'id', 'placeholder' => 'Reconciliation'],
		'reconciliation_id_sequence' => ['name' => 'Reconciliation #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'document_number' => ['name' => 'Document Number', 'type' => 'varchar', 'length' => 30],
		'bank_id' => ['name' => 'Bank #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Bank'],
		'bank_id_sequence' => ['name' => 'Bank #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'bank_deposit_number' => ['name' => 'Bank Deposit #', 'type' => 'bigint', 'default' => null, 'format' => 'id'],
		'ic_item_id' => ['name' => 'I/C Item #', 'type' => 'varchar', 'length' => 109, 'placeholder' => 'Item', 'searchable' => true, 'tree' => true, 'format' => 'id', 'placeholder' => 'Item'],
		'billing_cycle_id' => ['name' => 'Billing Cycle #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Billing Cycle'],
		'billing_cycle_id_sequence' => ['name' => 'Billing Cycle #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'commission_code_id' => ['name' => 'Commission #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Commission Code'],
		'commission_code_id_sequence' => ['name' => 'Commission #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'interest_profile_id' => ['name' => 'Interest Profile #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Interest Profile'],
		'interest_profile_id_sequence' => ['name' => 'Interest Profile #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'distribution_code_id' => ['name' => 'Distribution Code #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Distribution Code'],
		'distribution_code_id_sequence' => ['name' => 'Distribution Code #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'price_list_id' => ['name' => 'Price List #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Price List'],
		'price_list_id_sequence' => ['name' => 'Price List #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'ap_item_id' => ['name' => 'A/P Item #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Item'],
		'ap_item_id_sequence' => ['name' => 'A/P Item #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'ar_item_id' => ['name' => 'A/R Item #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Item'],
		'ar_item_id_sequence' => ['name' => 'A/R Item #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'payment_code_id' => ['name' => 'Payment Code #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Payment Code'],
		'payment_code_id_sequence' => ['name' => 'Payment Code #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'discount_id' => ['name' => 'Discount #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Discount'],
		'discount_id_sequence' => ['name' => 'Discount #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'account_set_id' => ['name' => 'Account Set #', 'type' => 'integer', 'default' => null, 'format' => 'id', 'placeholder' => 'Account Set'],
		'account_set_id_sequence' => ['name' => 'Account Set #', 'type' => 'serial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'invoice_id' => ['name' => 'Invoice #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Invoice #'],
		'invoice_id_sequence' => ['name' => 'Invoice #', 'type' => 'serial', 'format' => 'id', 'placeholder' => 'Sequence'],
		'tax_amount' => ['name' => 'Tax Amount', 'type' => 'bcnumeric', 'precision' => 24, 'scale' => 2, 'default' => '0.00', 'format' => 'amount', 'align' => 'right'],
		'budget_id' => ['name' => 'Budget #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Budget'],
		'budget_id_sequence' => ['name' => 'Budget #', 'type' => 'serial', 'format' => 'id', 'placeholder' => 'Sequence'],
		'detail_id' => ['name' => 'Detail #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Detail'],
		'revaluation_id' => ['name' => 'Revaluation #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Revaluation'],
		'revaluation_id_sequence' => ['name' => 'Revaluation #', 'type' => 'serial', 'format' => 'id', 'placeholder' => 'Sequence'],
		'allocation_id' => ['name' => 'Allocation #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Allocation'],
		'allocation_id_sequence' => ['name' => 'Allocation #', 'type' => 'serial', 'format' => 'id', 'placeholder' => 'Sequence'],
		'entry_id' => ['name' => 'Entry #', 'type' => 'bigint', 'format' => 'id', 'placeholder' => 'Entry'],
		'entry_id_sequence' => ['name' => 'Entry #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'batch_id' => ['name' => 'Batch #', 'type' => 'bigint', 'format' => 'id', 'placeholder' => 'Batch'],
		'batch_id_sequence' => ['name' => 'Batch #', 'type' => 'bigserial', 'placeholder' => 'Sequence', 'format' => 'id'],
		'structure_id' => ['name' => 'Structure #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Structure'],
		'structure_id_sequence' => ['name' => 'Structure #', 'type' => 'serial', 'format' => 'id', 'placeholder' => 'Sequence'],
		'class_id' => ['name' => 'Class #', 'type' => 'integer', 'format' => 'id', 'placeholder' => 'Class'],
		'class_id_sequence' => ['name' => 'Class #', 'type' => 'serial', 'format' => 'id', 'placeholder' => 'Sequence'],
		// HTML
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

	/**
	 * Get non sequence domain
	 *
	 * @param string $domain
	 * @return string
	 */
	public static function getNonSequenceDomain(string $domain) : string {
		if (strpos($domain, '_sequence') !== false) {
			return str_replace('_sequence', '', $domain);
		}
		return $domain;
	}
}