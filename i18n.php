<?php

/**
 * Internalization class
 */
class i18n implements numbers_backend_i18n_interface_base {

	/**
	 * Initialized
	 *
	 * @var boolean
	 */
	public static $initialized = false;

	/**
	 * Initializing i18n
	 *
	 * @param array $options
	 */
	public static function init($options = []) {
		self::$initialized = true;
		$i18n = application::get('flag.global.i18n');
		// merge options into settings
		if (!empty($options)) {
			$i18n = array_merge2($i18n, $options);
		}
		// determine final language
		$languages = factory::submodule('flag.global.i18n.submodule')->languages();
		$final_language = application::get('flag.global.__language_code') ?? session::get('numbers.i18n.language_code') ?? $i18n['language_code'] ?? 'sys';
		if (empty($languages[$final_language])) {
			$final_language = 'sys';
		}
		$i18n['language_code'] = $final_language;
		session::set('numbers.i18n.language_code', $final_language);
		application::set('flag.global.i18n', $i18n);
		// initialize the module
		return factory::submodule('flag.global.i18n.submodule')->init($i18n);
	}

	/**
	 * Destroy
	 */
	public static function destroy() {
		factory::submodule('flag.global.i18n.submodule')->destroy();
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

	/**
	 * Check if language is rtl or return direction
	 *
	 * @param boolean $flag
	 * @return mixed
	 */
	public static function rtl($flag = true) {
		$languages = factory::submodule('flag.global.i18n.submodule')->languages();
		//print_r2($languages);
		$language_code = application::get('flag.global.i18n.language_code');
		if ($flag) {
			return $languages[$language_code]['lc_language_rtl'] ? true : false;
		} else {
			return $languages[$language_code]['lc_language_rtl'] ? 'dir="rtl"' : '';
		}
	}
}