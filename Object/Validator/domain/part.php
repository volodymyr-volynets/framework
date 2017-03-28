<?php

namespace Object\Validator\Domain;
class Part extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
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