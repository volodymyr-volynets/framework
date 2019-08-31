<?php

namespace Numbers\Framework\Helper\Model\Date;
class WeekDays2 extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Date Week Days (2)';
	public $column_key = 'id';
	public $column_prefix = ''; // do not change this
	public $orderby = [
		'id' => SORT_ASC
	];
	public $columns = [
		'id' => ['name' => 'Week Day #', 'domain' => 'type_id'],
		'name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		0 => ['name' => 'Sunday'],
		1 => ['name' => 'Monday'],
		2 => ['name' => 'Tuesday'],
		3 => ['name' => 'Wednesday'],
		4 => ['name' => 'Thursday'],
		5 => ['name' => 'Friday'],
		6 => ['name' => 'Saturday']
	];
}