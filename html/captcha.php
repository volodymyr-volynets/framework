<?php

class html_captcha implements numbers_frontend_html_interface_captcha {

	/**
	 * Generate captcha
	 *
	 * @param array $options
	 * @return string
	 */
	public static function captcha($options = []) {
		return factory::submodule('flag.global.captcha.submodule')->captcha($options);
	}

	/**
	 * Validate captcha value
	 *
	 * @param string $captcha_id
	 * @param string $password
	 */
	public static function validate($captcha_id, $password) {
		return factory::submodule('flag.global.captcha.submodule')->validate($captcha_id, $password);
	}
}