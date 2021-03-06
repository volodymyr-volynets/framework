<?php

namespace Object\Validator;
class UpperCase2 extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
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