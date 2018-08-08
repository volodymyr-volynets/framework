<?php

namespace Object\Format\Locales;
class arSAUTF8 extends \Object\Format\Locales\Locale {

	public static $symbol_defaults = [
		'comma' => '،',
		'question_mark' => '؟',
		'semicolon' => '؛'
	];

	public static function localeconv($options) {
		$options['decimal_point'] = $options['mon_decimal_point'] = '٫';
		$options['thousands_sep'] = $options['mon_thousands_sep'] = '٬';
		$options['int_curr_symbol'] = 'SAR';
		$options['currency_symbol'] = 'ر.س.';
		$options['grouping'] = $options['mon_grouping'] = [3, 3];
		$options['frac_digits'] = $options['int_frac_digits'] = 2;
		$options['positive_sign'] = '';
		$options['negative_sign'] = '-';
		$options['p_cs_precedes'] = 0;
		$options['p_sep_by_space'] = 1;
		$options['n_cs_precedes'] = 0;
		$options['n_sep_by_space'] = 1;
		$options['p_sign_posn'] = 1;
		$options['n_sign_posn'] = 1;
		return $options;
	}

	public static function readFloatval($amount, $options = []) {
		return \Object\Format\Locales\Arabic::readFloatval($amount);
	}

	public static function amount($amount, $options = []) {
		return \Object\Format\Locales\Arabic::amount($amount);
	}

	public static function js() {
		\Object\Format\Locales\Arabic::js();
	}
}