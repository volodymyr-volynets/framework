<?php

namespace Object\Format\Locales;
class Locale {

	/**
	 * Get locale settings
	 *
	 * @param array $options
	 */
	public static function localeconv($options) {
		return $options;
	}

	/**
	 * Convert numbers from native to ASCII
	 *
	 * @param string $amount
	 * @param array $options
	 */
	public static function readFloatval($amount, $options = []) {
		return $amount;
	}

	/**
	 * Convert ASCII to native numbers
	 *
	 * @param string $amount
	 * @param array $options
	 */
	public static function amount($amount, $options = []) {
		return $amount;
	}

	/**
	 * JavaScript conversion functions
	 */
	public static function js() {
		
	}
}