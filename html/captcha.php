<?php

class html_captcha {

	/**
	 * Generate captcha
	 *
	 * @param array $options
	 * @return string
	 */
	public static function captcha($options = []) {
		$captcha_object = self::get_object();
		return $captcha_object->captcha($options);
	}

	/**
	 * Validate captcha value
	 *
	 * @param string $captcha_id
	 * @param string $password
	 */
	public static function validate($captcha_id, $password) {
		$captcha_object = self::get_object();
		return $captcha_object->validate($captcha_id, $password);
	}

	/**
	 * Get submodule object
	 *
	 * @return captcha_class
	 * @throws Exception
	 */
	private static function get_object() {
		$captcha_class = application::get('flag.global.captcha.submodule', ['class' => 1]);
		if (empty($captcha_class)) {
			Throw new Exception('You must indicate captcha submodule!');
		}
		return new $captcha_class();
	}
}