<?php

namespace Numbers\Framework\Helper\Model\Date;
class CalendarTypes extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Date Calendar Types';
	public $column_key = 'id';
	public $column_prefix = '';
	public $orderby = [
		'id' => SORT_ASC
	];
	public $columns = [
		'id' => ['name' => 'Type #', 'domain' => 'type_id'],
		'name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		//10 => ['name' => 'Day'],
		20 => ['name' => 'Week'],
		30 => ['name' => 'Month'],
	];
}