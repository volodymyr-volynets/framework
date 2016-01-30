<?php

/**
 * Internalization class
 */
class i18n implements numbers_backend_i18n_interface_base {

	/**
	 * Initializing i18n
	 *
	 * @param array $options
	 */
	public static function init($options = []) {
		return factory::submodule('flag.global.i18n.submodule')->init($options);
	}

	/**
	 * Get translation
	 *
	 * @param string $i18n
	 * @param string $text
	 * @param array $options
	 * @return string
	 */
	public static function get($i18n, $text, $options = []) {
		return factory::submodule('flag.global.i18n.submodule')->get($i18n, $text, $options);
	}

	/**
	 * Set variable into i18n
	 *
	 * @param string $variable
	 * @param mixed $value
	 */
	public static function set($variable, $value) {
		return factory::submodule('flag.global.i18n.submodule')->set($variable, $value);
	}
}