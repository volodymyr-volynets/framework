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
		$result = [
			'success' => false,
			'error' => []
		];
		// initialize the module
		$i18n = \Application::get('flag.global.i18n') ?? [];
		// settings from user account
		$user_settings = User::get('internalization');
		if (!empty($user_settings)) {
			foreach ($user_settings as $k => $v) if (empty($v)) unset($user_settings[$k]);
		}
		$i18n = array_merge_hard($i18n, $user_settings, $options ?? []);
		if (!empty($i18n['submodule'])) {
			// check if backend has been enabled
			if (!\Application::get($i18n['submodule'], ['submodule_exists' => true])) {
				Throw new \Exception('You must enable ' . $i18n['submodule'] . ' first!');
			}
			self::$options = \Factory::model($i18n['submodule'], true)->init($i18n);
			//\Application::set('flag.global.i18n', self::$options);
			//\Session::set('numbers.user.i18n.language_code', $i18n['language_code']);
			$result['success'] = self::$initialized = true;
		}
		return $result;
	}

	/**
	 * Destroy
	 */
	public static function destroy() {
		if (!empty(self::$options['submodule'])) {
			\Factory::model(self::$options['submodule'], true)->destroy();
		}
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
		if (is_array($text)) {
			$result = [];
			foreach ($text as $v) {
				$result[] = self::getOne($i18n, $v, $options);
			}
			return implode('', $result);
		} else {
			return self::getOne($i18n, $text, $options);
		}
		return $text;
	}

	/**
	 * Get one
	 *
	 * @param int $i18n
	 * @param string $text
	 * @param array $options
	 * @return type
	 */
	public static function getOne($i18n, $text, $options = []) {
		// get text from submodule
		if (!empty(self::$options['submodule'])) {
			$text = \Factory::model(self::$options['submodule'], true)->get($i18n, $text, $options);
		}
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
	 * Check if language is RTL or return direction
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

	/**
	 * Change I/N group
	 *
	 * @param int $group_id
	 */
	public static function changeGroup(int $group_id) {
		\Application::set('flag.global.__in_group_id', $group_id);
		\I18n::init();
		setcookie("__in_group_id", $group_id);
	}

	/**
	 * Process message
	 *
	 * @param string $message
	 * @param array|json $replace
	 * @return string
	 */
	public static function processMessage(string $message, $replace) : string {
		if (is_json($replace)) {
			$replace = json_decode($replace, true);
		}
		foreach ($replace as $k => $v) {
			if (is_string($v)) {
				if (substr($v, 0, 2) == '~~') {
					$v = substr($v, 2);
				} else {
					$v = i18n(null, $v);
				}
			} else {
				$v = \Format::id($v);
			}
			$replace[$k] = $v;
		}
		return i18n(null, $message, ['replace' => $replace]);
	}
}