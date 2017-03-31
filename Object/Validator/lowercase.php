<?php

class object_validator_lowercase extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'lowercase only';
		$value.= '';
		if (strtolower($value) !== $value) {
			$result['error'][] = \Object\Content\Messages::string_lowercase;
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}