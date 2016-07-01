<?php

class object_validator_password extends object_validator_base implements object_validator_interface {

	/**
	 * Validate password
	 *
	 * @param string $value
	 * @return array
	 */
	public function validate($value) {
		$value = $value . '';
		// handle overrides first
		if (!empty($this->override)) {
			return $this->override->validate($value);
		}
		$result = [
			'success' => false,
			'error' => [],
			'data' => null
		];
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