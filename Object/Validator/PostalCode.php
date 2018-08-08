<?php

namespace Object\Validator;
class PostalCode extends \Object\Validator\Base {

	/**
	 * @see \Object\Validator\Base::validate()
	 */
	public function validate($value, $options = []) {
		$result = $this->result;
		$value.= '';
		// find country
		$country = null;
		foreach ($options['neighbouring_values'] as $k => $v) {
			if (strpos($k, 'country_code') !== false) {
				$country = $v;
				break;
			}
		}
		// postal code is different based on country
		switch ($country) {
			case 'CA':
				$result['placeholder'] = 'A#B#C#';
				if (!preg_match('/^[a-z][0-9][a-z][0-9][a-z][0-9]$/i', $value)) {
					$result['error'][] = 'Invalid postal code!';
				} else {
					$result['data'] = strtoupper($value);
					$result['success'] = true;
				}
				break;
			case 'US':
				$result['placeholder'] = '#####-####';
				if (!(preg_match('/^[0-9]{5}$/', $value) || preg_match('/^([0-9]{5})-([0-9]{4})$/', $value))) {
					$result['error'][] = 'Invalid zip code!';
				} else {
					$result['data'] = $value;
					$result['success'] = true;
				}
				break;
			default:
				$result['placeholder'] = '';
				$result['data'] = $value;
		}
		return $result;
	}
}