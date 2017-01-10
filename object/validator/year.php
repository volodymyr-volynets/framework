<?php

class object_validator_year extends object_validator_base {

	/**
	 * @see object_validator_base::validate()
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