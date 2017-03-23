<?php

class object_validator_domain_part extends object_validator_base {

	/**
	 * @see object_validator_base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		if (!preg_match('/[^a-zA-Z0-9_]+/', $value)) {
			$result['success'] = true;
			$result['data'] = strtolower($value);
		} else {
			$result['error'][] = 'Invalid domain part!';
		}
		return $result;
	}
}