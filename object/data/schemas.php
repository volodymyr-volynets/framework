<?php

class object_data_schemas extends object_data {
	public $column_key = 'no_data_schema_code';
	public $column_prefix = 'no_data_schema_';
	public $orderby = ['no_data_schema_name' => SORT_ASC];
	public $columns = [
		'no_data_schema_code' => ['name' => 'Schema Code', 'type' => 'char', 'length' => 2],
		'no_data_schema_name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		// entities
		'em' => ['no_data_schema_name' => 'Entity Management'],
		// accounting modules
		'cs' => ['no_data_schema_name' => 'Common Services'],
		'gl' => ['no_data_schema_name' => 'General Ledger'],
		'ap' => ['no_data_schema_name' => 'Accounts Payable'],
		'ar' => ['no_data_schema_name' => 'Accounts Receivable'],
		'pr' => ['no_data_schema_name' => 'Payroll'],
		// other misc modules
		'rn' => ['no_data_schema_name' => 'Relations & Rules'],
		'lc' => ['no_data_schema_name' => 'Localization'],
		'dc' => ['no_data_schema_name' => 'Documents'],
		'dn' => ['no_data_schema_name' => 'Documentation'],
		'ms' => ['no_data_schema_name' => 'Miscellaneous'],
		'no' => ['no_data_schema_name' => 'Numbers Objects'],
		'sm' => ['no_data_schema_name' => 'System (Common)'],
		'sc' => ['no_data_schema_name' => 'System (Cron)'],
		'temp' => ['no_data_schema_name' => 'Temporary Tables']
	];
}