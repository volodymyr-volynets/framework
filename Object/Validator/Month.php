<?php

namespace Object\Validator;
class Month extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = '##';
		$result['placeholder_select'] = 'Month';
		$value = (int) $value;
		if ($value < 1 || $value > 12) {
			$result['error'][] = 'Invalid month!';
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}