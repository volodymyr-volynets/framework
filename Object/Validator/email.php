<?php

class object_validator_email extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'example@domain.com';
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