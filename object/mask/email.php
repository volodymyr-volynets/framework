<?php

class object_mask_email {

	/**
	 * Mask
	 *
	 * @param string $value
	 * @param array $options
	 *		mask - symbol
	 * @return string
	 */
	public function mask(string $value, array $options = []) {
		$options['mask'] = $options['mask'] ?? '*';
		$temp = explode('@', trim($value));
		$length = strlen($temp[0]);
		$result = substr($temp[0], 0, floor($length / 2));
		for ($i = 0; $i <= $length - strlen($result); $i++) {
			$result.= $options['mask'];
		}
		return $result . '@' . $temp[1];
	}
}