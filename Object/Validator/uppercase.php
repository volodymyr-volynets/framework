<?php

class object_validator_uppercase extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'UPPERCASE ONLY';
		$result['placeholder_select'] = '';
		$value.= '';
		if (strtoupper($value) !== $value) {
			$result['error'][] = object_content_messages::string_uppercase;
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}