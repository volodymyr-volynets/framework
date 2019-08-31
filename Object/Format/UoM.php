<?php

namespace Object\Format;
class UoM extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Object Data UoM';
	public $column_key = 'no_frmtuom_code';
	public $column_prefix = 'no_frmtuom_';
	public $orderby;
	public $columns = [
		'no_frmtuom_code' => ['name' => 'Code', 'domain' => 'group_code'],
		'no_frmtuom_name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $options_map = [
		'no_frmtuom_name' => 'name',
	];
	public $data = [
		'METRIC' => ['no_frmtuom_name' => 'Metric'],
		'IMPERIAL' => ['no_frmtuom_name' => 'Imperial'],
	];
}