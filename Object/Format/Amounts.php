<?php

namespace Object\Format;
class Amounts extends \Object\Data {
	public $column_key = 'in_frmtamnt_id';
	public $column_prefix = 'in_frmtamnt_';
	public $orderby;
	public $columns = [
		'in_frmtamnt_id' => ['name' => 'Amount Format #', 'domain' => 'type_id'],
		'in_frmtamnt_name' => ['name' => 'Name', 'type' => 'text'],
		'in_frmtamnt_title' => ['name' => 'Title', 'type' => 'text']
	];
	public $options_map = [
		'in_frmtamnt_name' => 'name',
		'in_frmtamnt_title' => 'title'
	];
	public $data = [
		10 => ['in_frmtamnt_name' => 'Amount (Locale, With Currency Symbol)', 'in_frmtamnt_title' => '$ -123,456.00'],
		20 => ['in_frmtamnt_name' => 'Amount (Locale, Without Currency Symbol)', 'in_frmtamnt_title' => '-123,456.00'],
		30 => ['in_frmtamnt_name' => 'Accounting (Locale, With Currency Symbol)', 'in_frmtamnt_title' => '$(123,456.00)'],
		40 => ['in_frmtamnt_name' => 'Accounting (Locale, Without Currency Symbol)', 'in_frmtamnt_title' => '(123,456.00)'],
		99 => ['in_frmtamnt_name' => 'Plain Amount', 'in_frmtamnt_title' => '-123456.00']
	];
}