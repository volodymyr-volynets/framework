<?php

class object_validator_password extends object_validator_base {

	/**
	 * @see object_validator_base::validate()
	 */
	public function validate($value, $options = []) {
		$value = $value . '';
		$result = $this->result;
		$result['placeholder'] = '8 characters, 1 number, 1 letter';
		if (strlen($value) < 8) {
			$result['error'][] = 'Password too short, should be atleast 8 characters!';
		}
		if (!preg_match('#[0-9]+#', $value)) {
			$result['error'][] = 'Password must include at least one number!';
		}
		if (!preg_match('#[a-zA-Z]+#', $value)) {
			$result['error'][] = 'Password must include at least one letter!';
		}
		if (empty($result['error'])) {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}