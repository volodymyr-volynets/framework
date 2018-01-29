<?php

namespace Numbers\Framework\Helper\Model\Date;
class WeekDays extends \Object\Data {
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
		1 => ['name' => 'Monday'],
		2 => ['name' => 'Tuesday'],
		3 => ['name' => 'Wednesday'],
		4 => ['name' => 'Thursday'],
		5 => ['name' => 'Friday'],
		6 => ['name' => 'Saturday'],
		7 => ['name' => 'Sunday'],
	];
}