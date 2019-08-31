<?php

namespace Object\Format;
class Amounts extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Object Data Amounts';
	public $column_key = 'no_frmtamnt_id';
	public $column_prefix = 'no_frmtamnt_';
	public $orderby;
	public $columns = [
		'no_frmtamnt_id' => ['name' => 'Amount Format #', 'domain' => 'type_id'],
		'no_frmtamnt_name' => ['name' => 'Name', 'type' => 'text'],
		'no_frmtamnt_title' => ['name' => 'Title', 'type' => 'text']
	];
	public $options_map = [
		'no_frmtamnt_name' => 'name',
		'no_frmtamnt_title' => 'title'
	];
	public $data = [
		10 => ['no_frmtamnt_name' => 'Amount (Locale, With Currency Symbol)', 'no_frmtamnt_title' => '$ -123,456.00'],
		20 => ['no_frmtamnt_name' => 'Amount (Locale, Without Currency Symbol)', 'no_frmtamnt_title' => '-123,456.00'],
		30 => ['no_frmtamnt_name' => 'Accounting (Locale, With Currency Symbol)', 'no_frmtamnt_title' => '$(123,456.00)'],
		40 => ['no_frmtamnt_name' => 'Accounting (Locale, Without Currency Symbol)', 'no_frmtamnt_title' => '(123,456.00)'],
		99 => ['no_frmtamnt_name' => 'Plain Amount', 'no_frmtamnt_title' => '-123456.00']
	];
}