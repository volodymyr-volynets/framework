<?php

class object_format_locales_ar_sa_utf8 extends object_format_locales_locale {

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

	public static function read_floatval($amount, $options = []) {
		return object_format_locales_arabic::read_floatval($amount);
	}

	public static function amount($amount, $options = []) {
		return object_format_locales_arabic::amount($amount);
	}

	public static function js() {
		object_format_locales_arabic::js();
	}
}