<?php

namespace Object\Validator;
class Username extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'username or example@domain.com';
		if (strpos($value . '', '@') !== false) {
			$data = filter_var($value, FILTER_VALIDATE_EMAIL);
			if ($data !== false) {
				$result['success'] = true;
				$result['data'] = strtolower($data . '');
			} else {
				$result['error'][] = 'Invalid email address!';
			}
		} else {
			if (strtolower($value . '') !== $value) {
				$result['error'][] = \Object\Content\Messages::STRING_LOWERCASE;
			} else {
				$result['success'] = true;
				$result['data'] = $value . '';
			}
		}
		return $result;
	}
}