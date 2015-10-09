<?php

class captcha {

	// presets
	public static $numbers = '1234567890';
	public static $alpha = 'abcdefghijklmnopqrstuvwxyz';
	public static $full = 'abcdefghijklmnopqrstuvwxyz1234567890';

	/**
	 * Get new pass phrase
	 * 
	 * @param string $id
	 * @param string $charset
	 * @param number $length
	 * @return string
	 */
	public static function get($id, $charset = '1234567890', $length = 5) {
		// generate pass phrase
		$result = '';
		$chars = str_split($charset);
		for ($i = 0; $i < $length; $i++) {
			$result.= $chars[array_rand($chars)];
		}
		// set it in sessions
		session::set(array('CAPTCHAS', $id), array('phrase'=>$result));
		// important to return pass phrase so we can generate an image
		return $result;
	}

	/**
	 * Check phrase
	 * @param string $id
	 * @param string $phrase
	 * @return boolean
	 */
	public static function check($id, $phrase) {
		$stored = session::get(array('CAPTCHAS', $id, 'phrase'));
		if (!empty($phrase) && strtolower($stored)==strtolower($phrase)) {
			return true;
		} else {
			return false;
		}
	}
}