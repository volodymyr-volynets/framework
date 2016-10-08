<?php

class object_validator_year extends object_validator_base implements object_validator_interface {

	/**
	 * Validate year
	 *
	 * @param int $value
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