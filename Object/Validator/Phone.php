<?php

namespace Object\Validator;
class Phone extends \Object\Validator\Base {

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

	/**
	 * Generate plain number
	 *
	 * @param string $value
	 * @return int
	 */
	public static function plainNumber(string $value) : int {
		$temp = explode('ext', $value);
		$result = preg_replace('/[^0-9]/', '', $temp[0]);
		// remove first 1 in US numbers
		if (strlen($result) == 11 && $result[0] == 1) {
			$result = substr($result, 1);
		}
		return $result;
	}
}