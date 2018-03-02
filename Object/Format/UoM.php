<?php

namespace Object\Format;
class UoM extends \Object\Data {
	public $column_key = 'in_frmtuom_code';
	public $column_prefix = 'in_frmtuom_';
	public $orderby;
	public $columns = [
		'in_frmtuom_code' => ['name' => 'Code', 'domain' => 'group_code'],
		'in_frmtuom_name' => ['name' => 'Name', 'type' => 'text'],
	];
	public $options_map = [
		'in_frmtuom_name' => 'name',
	];
	public $data = [
		'METRIC' => ['in_frmtuom_name' => 'Metric'],
		'IMPERIAL' => ['in_frmtuom_name' => 'Imperial'],
	];
}