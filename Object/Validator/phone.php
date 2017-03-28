<?php

class object_validator_phone extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = '# (###) ###-#### ext ####';
		if (!preg_match('/^[0-9+\(\)#\.\s\/ext-]+$/', $value)) {
			$result['error'][] = 'Invalid phone number!';
		} else {
			$result['success'] = true;
			$result['data'] = $value . '';
		}
		return $result;
	}
}