<?php

namespace Object\Validator;
class UpperCase extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'UPPERCASE ONLY';
		$result['placeholder_select'] = '';
		$value.= '';
		if (strtoupper($value) !== $value) {
			$result['error'][] = \Object\Content\Messages::STRING_UPPERCASE;
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}