<?php

namespace Numbers\Framework\Helper\Model\Date;
class Days extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Date Days';
	public $column_key = 'id';
	public $column_prefix = ''; // do not change this
	public $orderby = [
		'id' => SORT_ASC
	];
	public $columns = [
		'id' => ['name' => 'Day #', 'domain' => 'type_id'],
		'name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		1 => ['name' => '1st'],
		2 => ['name' => '2nd'],
		3 => ['name' => '3rd'],
		4 => ['name' => '4th'],
		5 => ['name' => '5th'],
		6 => ['name' => '6th'],
		7 => ['name' => '7th'],
		8 => ['name' => '8th'],
		9 => ['name' => '9th'],
		10 => ['name' => '10th'],
		11 => ['name' => '11th'],
		12 => ['name' => '12th'],
		13 => ['name' => '13th'],
		14 => ['name' => '14th'],
		15 => ['name' => '15th'],
		16 => ['name' => '16th'],
		17 => ['name' => '17th'],
		18 => ['name' => '18th'],
		19 => ['name' => '19th'],
		20 => ['name' => '20th'],
		21 => ['name' => '21st'],
		22 => ['name' => '22nd'],
		23 => ['name' => '23rd'],
		24 => ['name' => '24th'],
		25 => ['name' => '25th'],
		26 => ['name' => '26th'],
		27 => ['name' => '27th'],
		28 => ['name' => '28th'],
		29 => ['name' => '29th'],
		30 => ['name' => '30th'],
		31 => ['name' => '31th'],
	];

	/**
	 * @see $this->options();
	 */
	public function optionsWithLastDay($options = []) : array {
		$result = $this->options($options);
		$result[99] = ['name' => i18n(null, 'Last Day')];
		return $result;
	}
}