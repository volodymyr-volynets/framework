<?php

namespace Numbers\Framework\Helper\Model\Date;
class Days extends \Object\Data {
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
		1 => ['name' => '1'],
		2 => ['name' => '2'],
		3 => ['name' => '3'],
		4 => ['name' => '4'],
		5 => ['name' => '5'],
		6 => ['name' => '6'],
		7 => ['name' => '7'],
		8 => ['name' => '8'],
		9 => ['name' => '9'],
		10 => ['name' => '10'],
		11 => ['name' => '11'],
		12 => ['name' => '12'],
		13 => ['name' => '13'],
		14 => ['name' => '14'],
		15 => ['name' => '15'],
		16 => ['name' => '16'],
		17 => ['name' => '17'],
		18 => ['name' => '18'],
		19 => ['name' => '19'],
		20 => ['name' => '20'],
		21 => ['name' => '21'],
		22 => ['name' => '22'],
		23 => ['name' => '23'],
		24 => ['name' => '24'],
		25 => ['name' => '25'],
		26 => ['name' => '26'],
		27 => ['name' => '27'],
		28 => ['name' => '28'],
		29 => ['name' => '29'],
		30 => ['name' => '30'],
		31 => ['name' => '31'],
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