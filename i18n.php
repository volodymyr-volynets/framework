<?php

/**
 * Internalization class
 */
class i18n {

	public static $default;
	public static $current;
	public static $path;

	public function __construct($default, $current, $path) {
		self::$default = $default;
		self::$current = $current;
		self::$path = $path;
	}

	public static function get($id, $lang = null) {
		if (empty(self::$default) || empty(self::$current)) {
			Throw new Exception('i18n: default or current?');
		}
		// require internalization file with translations
		global $internalization;
		if (empty($internalization)) require_once(self::$path);
		// processing
		if (isset($internalization[$id])) {
			$lang = $lang ? $lang : self::$current;
			if (isset($internalization[$id][$lang])) {
				return $internalization[$id][$lang];
			} else {
				return $internalization[$id][self::$default];
			}
		} else {
			Throw new Exception('i18n: id?');
		}
	}

	public static function download() {
		global $internalization;
		if (empty($internalization)) require_once(self::$path);
		// find all available languages
		$languages = array();
		foreach ($internalization as $k=>$v) foreach ($v as $k2=>$v2) $languages[$k2] = $k2;
		// build data array
		$data = array();
		foreach ($internalization as $k=>$v) {
			foreach ($languages as $v2) {
				$data[$k][$v2] = @$v[$v2];
			}
		}
		return $data;
	}
}

/**
 * Short alias to class name
 */
function i18n($id, $lang = null) {
	return i18n::get($id, $lang);
}