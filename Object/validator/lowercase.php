<?php

class object_validator_lowercase extends object_validator_base {

	/**
	 * @see object_validator_base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'lowercase only';
		$value.= '';
		if (strtolower($value) !== $value) {
			$result['error'][] = object_content_messages::string_lowercase;
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}