<?php

namespace Object\Validator;
class Day extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = '##';
		$result['placeholder_select'] = 'Day';
		$value = (int) $value;
		if ($value < 1 || $value > 31) {
			$result['error'][] = 'Invalid day!';
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}