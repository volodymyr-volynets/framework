<?php

class object_validator_email extends object_validator_base implements object_validator_interface {

	/**
	 * Validate email address
	 *
	 * @param string $value
	 * @return array
	 */
	public function validate($value) {
		// handle overrides first
		if (!empty($this->override)) {
			return $this->override->validate($value);
		}
		// process
		$result = [
			'success' => false,
			'error' => [],
			'data' => null
		];
		$data = filter_var($value, FILTER_VALIDATE_EMAIL);
		if ($data !== false) {
			$result['success'] = true;
			$result['data'] = $data . '';
		} else {
			$result['error'][] = 'Invalid email address!';
		}
		return $result;
	}
}