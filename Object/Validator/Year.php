<?php

namespace Object\Validator;
class Year extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'YYYY';
		$result['placeholder_select'] = 'Year';
		$value = (int) $value;
		if ($value < 1000 || $value > 9999) {
			$result['error'][] = 'Invalid year!';
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}