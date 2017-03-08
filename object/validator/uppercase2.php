<?php

class object_validator_uppercase2 extends object_validator_base {

	/**
	 * @see object_validator_base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'UPPERCASE ONLY';
		$result['placeholder_select'] = '';
		$result['success'] = true;
		$result['data'] = strtoupper($value . '');
		return $result;
	}
}