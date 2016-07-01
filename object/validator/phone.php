<?php

class object_validator_phone extends object_validator_base implements object_validator_interface {

	/**
	 * Validate phone number with extension
	 *
	 * @param string $value
	 * @return array
	 */
	public function validate($value) {
		// handle overrides first
		if (!empty($this->override)) {
			return $this->override->validate($value);
		}
		$result = [
			'success' => false,
			'error' => [],
			'data' => null
		];
		if (!preg_match('/^[0-9+\(\)#\.\s\/ext-]+$/', $value)) {
			$result['error'][] = 'Invalid phone number!';
		} else {
			$result['success'] = true;
			$result['data'] = $value . '';
		}
		return $result;
	}
}