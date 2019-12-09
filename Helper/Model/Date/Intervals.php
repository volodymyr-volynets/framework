<?php

namespace Numbers\Framework\Helper\Model\Date;
class Intervals extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Date Intervals';
	public $column_key = 'code';
	public $column_prefix = ''; // do not change this
	public $orderby = [
		'sort' => SORT_ASC
	];
	public $columns = [
		'code' => ['name' => 'Week Day #', 'domain' => 'code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'sort' => ['name' => 'Week Day #', 'domain' => 'type_id'],
	];
	public $data = [
		'5 minutes' => ['name' => '5 minutes', 'sort' => 100],
		'15 minutes' => ['name' => '15 minutes', 'sort' => 200],
		'30 minutes' => ['name' => '30 minutes', 'sort' => 300],
		'1 hour' => ['name' => '1 hour', 'sort' => 400],
		'2 hours' => ['name' => '2 hours', 'sort' => 500],
		'3 hours' => ['name' => '3 hours', 'sort' => 600],
		'6 hours' => ['name' => '6 hours', 'sort' => 700],
		'12 hours' => ['name' => '12 hours', 'sort' => 800],
		'1 day' => ['name' => '1 day', 'sort' => 900],
		'2 days' => ['name' => '2 days', 'sort' => 1000],
		'3 days' => ['name' => '3 days', 'sort' => 1100],
		'1 week' => ['name' => '1 week', 'sort' => 1200],
		'2 weeks' => ['name' => '2 weeks', 'sort' => 1300],
		'1 month' => ['name' => '1 month', 'sort' => 1400],
	];
}