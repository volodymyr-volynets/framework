<?php

namespace Object\Validator;
class LowerCase extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$result['placeholder'] = 'lowercase only';
		$value.= '';
		if (strtolower($value) !== $value) {
			$result['error'][] = \Object\Content\Messages::STRING_LOWERCASE;
		} else {
			$result['success'] = true;
			$result['data'] = $value;
		}
		return $result;
	}
}