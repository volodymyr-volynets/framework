<?php

class object_name_code implements object_name_interface {

	/**
	 * Various types
	 *
	 * @var array
	 */
	public static $types = [
		'common' => ['name' => 'Common'],
		// php
		'php_class' => ['name' => 'PHP Class, Interface, Trait'],
		'php_function' => ['name' => 'PHP Function, Method'],
		'php_variable' => ['name' => 'PHP Variable, Parameter'],
		'php_constant' => ['name' => 'PHP Constant'],
		// js
		'js_function' => ['name' => 'JS Function, Method'],
		'js_variable' => ['name' => 'JS Variable, Parameter'],
		// css, scss
		'css_class' => ['name' => 'CSS Class'],
		'css_id' => ['name' => 'CSS ID'],
	];

	/**
	 * Explain naming conventions
	 *
	 * @param string $type
	 * @return string
	 */
	public static function explain($type = null, $options = []) {
		$result = [];
		foreach (self::$types as $k => $v) {
			if (isset($type) && $type != $k) {
				continue;
			}
			switch ($k) {
				case 'common':
					$result[]= 'Common:';
					$result[]= '1. Only letters, numbers and the underscore are allowed in names.';
					$result[]= '2. All names are in "lowercase".';
					$result[]= '3. The first character in the name must be letter.';
					$result[]= '4. Words in names should be separated by underscores.';
					$result[]= '5. Keep the names meaningful and as short as possible.';
					break;
				case 'php_class':
				case 'php_function':
				case 'php_variable':
				case 'php_constant':
				case 'js_function':
				case 'js_variable':
				case 'css_class':
				case 'css_id':
					$result[] = self::$types[$k]['name'];
					$result[]= '1. Follow Common rules.';
					break;
			}
			$result[]= '';
		}
		if (!empty($options['html'])) {
			return nl2br(implode("\n", $result));
		} else {
			return $result;
		}
	}

	/**
	 * Check
	 *
	 * @param string $type
	 * @param string $name
	 */
	public static function check($type, $name) {
		$result = [
			'success' => false,
			'error' => []
		];
		if (!preg_match('/^[a-z]{1}[a-z0-9_]{0,30}$/', $name . '')) {
			$result['error'][] = 'Only letters, numbers and the underscore, no longer than 30 characters, name must start with a letter!';
		} else {
			$result['success'] = true;
		}
		return $result;
	}
}