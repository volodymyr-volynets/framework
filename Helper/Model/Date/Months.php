<?php

namespace Numbers\Framework\Helper\Model\Date;
class Months extends \Object\Data {
	public $column_key = 'id';
	public $column_prefix = ''; // do not change this
	public $orderby = [
		'id' => SORT_ASC
	];
	public $columns = [
		'id' => ['name' => 'Month #', 'domain' => 'type_id'],
		'name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		1 => ['name' => 'January'],
		2 => ['name' => 'February'],
		3 => ['name' => 'March'],
		4 => ['name' => 'April'],
		5 => ['name' => 'May'],
		6 => ['name' => 'June'],
		7 => ['name' => 'July'],
		8 => ['name' => 'August'],
		9 => ['name' => 'September'],
		10 => ['name' => 'October'],
		11 => ['name' => 'November'],
		12 => ['name' => 'December'],
	];
}