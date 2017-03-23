<?php

class I18n {

	/**
	 * Initialized
	 *
	 * @var boolean
	 */
	public static $initialized = false;

	/**
	 * Options
	 *
	 * @var array
	 */
	public static $options = [];

	/**
	 * Initializing i18n
	 *
	 * @param array $options
	 */
	public static function init($options = []) {
		
		// todo   
		return ['success' => true];
		
		$i18n = Application::get('flag.global.i18n') ?? [];
		$i18n = array_merge_hard($i18n, $options ?? []);
		// determine final language
		$languages = Factory::model('numbers_backend_i18n_languages_model_languages')->get();
		$final_language = Application::get('flag.global.__language_code') ?? Session::get('numbers.entity.format.language_code') ?? $i18n['language_code'] ?? 'sys';
		if (empty($languages[$final_language])) {
			$final_language = 'sys';
			$i18n['rtl'] = 0;
		}
		// put settings into system
		if (!empty($languages[$final_language])) {
			foreach ($languages[$final_language] as $k => $v) {
				$k = str_replace('lc_language_', '', $k);
				if (in_array($k, ['code', 'inactive'])) continue;
				if (empty($v)) continue;
				$i18n[$k] = $v;
			}
		}
		$i18n['language_code'] = $final_language;
		self::$options = $i18n;
		Session::set('numbers.entity.format.language_code', $final_language);
		Application::set('flag.global.i18n', $i18n);
		self::$initialized = true;
		// initialize the module
		return Factory::submodule('flag.global.i18n.submodule')->init($i18n);
	}

	/**
	 * Destroy
	 */
	public static function destroy() {
		return;
		Factory::submodule('flag.global.i18n.submodule')->destroy();
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
		//$text = factory::submodule('flag.global.i18n.submodule')->get($i18n, $text, $options);
		// if we need to handle replaces, for example:
		//		"Error occured on line [line_number]"
		// important: replaces must be translated/formatted separatly
		if (!empty($options['replace'])) {
			foreach ($options['replace'] as $k => $v) {
				$text = str_replace($k, $v, $text);
			}
		}
		return $text;
	}

	/**
	 * Check if language is rtl or return direction
	 *
	 * @param boolean $flag
	 * @return mixed
	 */
	public static function rtl($flag = true) {
		if ($flag) {
			return !empty(self::$options['rtl']);
		} else {
			return !empty(self::$options['rtl']) ? ' dir="rtl" ' : ' dir="ltr" ';
		}
	}
}