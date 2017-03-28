<?php

class object_validator_period extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = '###';
		$value = (int) $value;
		if ($value < 1 || $value > 999) {
			$result['error'][] = 'Invalid period!';
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}