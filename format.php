<?php

class format {

	/**
	 * Options
	 * 
	 * @var array
	 */
	public static $options = null;

	/**
	 * Initialize locales
	 * 
	 * @param array $options
	 */
	public static function init($options) {
		// processing locale
		if (empty($options['locale'])) $options['locale'] = 'en_CA.utf8';
		$short_locale = explode('.', $options['locale']);
		setlocale(LC_ALL, $options['locale'], $short_locale[0]);
		// processing time zone
		if (empty($options['timezone'])) $options['timezone'] = 'America/Toronto';
		date_default_timezone_set($options['timezone']);
		// storing settings
		/*
		if (empty(self::$locales) && !empty($options['model_locales'])) {
			list($model, $method) = explode('::', $options['model_locales']);
			$locales_model = new $model();
			self::$locales = call_user_func(array($locales_model, $method));
			$options['locale_name'] = self::$locales[$options['locale']]['ss_locale_name'];
		}
		*/
		self::$options = $options;
	}

	/**
	 * Determine if we can use locales, windows system is not supported
	 * 
	 * @return boolean
	 */
	public static function use_locale() {
		// we disable locale for now, it causes issues with dates and amounts, need additional testing on linux environment
		return false;
		//return (PHP_OS == 'Linux') ? true : false;
	}

	/**
	 * Get date format
	 *
	 * @param string $type
	 * @return string
	 */
	public static function get_date_format($type) {
		// we load format from global flags
		$global_format = application::get('flag.global.format');
		if ($type == 'time') {
			$format = $global_format['time'] ?? 'H:i:s';
		} else if ($type == 'datetime') {
			$format = $global_format['datetime'] ?? 'Y-m-d H:i:s';
		} else {
			$format = $global_format['date'] ?? 'Y-m-d';
		}
		return $format;
	}

	/**
	 * Get placeholder base on format
	 *
	 * @param string $format
	 * @return string
	 */
	public static function get_date_placeholder($format) {
		$format = str_replace('Y', 'YYYY', $format);
		$format = str_replace('m', 'MM', $format);
		$format = str_replace('d', 'DD', $format);
		$format = str_replace('H', 'HH', $format);
		$format = str_replace('i', 'MM', $format);
		$format = str_replace('s', 'SS', $format);
		$format = str_replace('g', 'HH', $format);
		$format = str_replace('a', 'am', $format);
		return $format;
	}

	/**
	 * Format date based on format
	 *
	 * @param mixed $value
	 * @param string $type
	 *		date
	 *		datetime
	 *		time
	 * @param array $options
	 *		format for date function
	 * @return string
	 */
	public static function date_format($value, $type = 'date', $options = []) {
		if (empty($value)) {
			return null;
		}
		$value = is_numeric($value) ? $value : strtotime($value);
		// processing format
		if (isset($options['format'])) {
			$format = $options['format'];
		} else {
			$format = self::get_date_format($type);
		}
		return date($format, $value);
	}

	/**
	 * Format date
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	public static function date($value, $options = []) {
		return self::date_format($value, 'date', $options);
	}

	/**
	 * Format time
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	public static function time($value, $options = []) {
		return self::date_format($value, 'time', $options);
	}

	/**
	 * Format datetime
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	public static function datetime($value, $options = []) {
		return self::date_format($value, 'datetime', $options);
	}

	/**
	 * Time in seconds
	 *
	 * @param mixed $time
	 * @param array $options
	 * @return string
	 */
	public static function time_seconds($time, $options = []) {
		return number_format($time, 4);
	}

	/**
	 * Current date and time
	 *
	 * @param string $type
	 * @param array $options
	 * @return string
	 */
	public static function now($type = 'datetime', $options = []) {
		// todo: convert to proper timezone, important!!!
		list($msec, $time) = explode(" ", microtime());
		if (!empty($options['add_seconds'])) {
			$time+= $options['add_seconds'];
		}
		// rendering
		switch ($type) {
			case 'timestamp':
				return date('Y-m-d H:i:s', $time) . '.' . round($msec * 1000000, 0);
				break;
			case 'time':
				return date('H:i:s', $time);
				break;
			case 'datetime':
				return date('Y-m-d H:i:s', $time);
				break;
			case 'date':
			default:
				return date('Y-m-d', $time);
		}
	}

	/**
	 * Transform date from locale into php
	 * 
	 * @param string $date
	 * @param string $type
	 * @return string
	 */
	public static function read_date($date, $type = 'date') {
		if (empty($date)) return null;
		$date = is_numeric($date) ? $date : @strtotime($date);
		switch ($type) {
			case 'time':
				return date('H:i:s', $date);
				break;
			case 'datetime':
				return date('Y-m-d H:i:s', $date);
				break;
			case 'date':
			default:
				return date('Y-m-d', $date);
		}
	}

	/**
	 * Read 5 character time string
	 * 
	 * @param mixed $time
	 * @return NULL|string
	 */
	public static function read_time5($time) {
		if (empty($time) || $time=='NULL') return null;
		if (is_numeric($time)) {
			return '00:' . $time;
		} else {
			$temp = explode(':', $time);
			return str_pad(intval($temp[0]), 2, '0', STR_PAD_LEFT) . ':' .str_pad(intval($temp[1]), 2, '0', STR_PAD_LEFT);
		}
	}

	/**
	 * Transform float from locale to php
	 * 
	 * @param string/float $amount
	 * @return number
	 */
	public static function read_floatval($amount) {
		$locale = localeconv();
		if (!self::use_locale()) {
			$locale['thousands_sep'] = ',';
			$locale['decimal_point'] = '.';
		}
		// remove currency symbol and name, thousands separator
		$amount = str_replace(array($locale['int_curr_symbol'], $locale['currency_symbol'], $locale['mon_thousands_sep'], $locale['thousands_sep']), '', $amount . '');
		// handle decimal separator
		if ($locale['decimal_point'] != '.') $amount = str_replace($locale['decimal_point'], '.', $amount);
		return floatval($amount);
	}

	/**
	 * Transform integer from locale to php
	 * 
	 * @param unknown_type $amount
	 * @return number
	 */
	public static function read_intval($amount) {
		return round(self::read_floatval($amount), 0);
	}

	/**
	 * Display number as per currency code
	 * 
	 * @param float $amount
	 * @param string $currency_code
	 * @param array $options
	 * @return string
	 */
	public static function currency($amount, $currency_code, $options = array()) {
		$options['symbol'] = true;
		$result = self::amount($amount, $options);
		$locale = localeconv();
		if (trim($locale['int_curr_symbol'])!=$currency_code) {
			if (empty($locale['currency_symbol'])) {
				$result = @self::$currencies[$currency_code]['symbol'] . $result;
			} else {
				$result = str_replace($locale['currency_symbol'], self::$currencies[$currency_code]['symbol'], $result);
			}
		}
		return $result;
	}

	/**
	 * Display number as in locale
	 * 
	 * @param float $amount
	 * @param array $options
	 * @return string
	 */
	public static function amount($amount, $options = array()) {
		$amount = self::read_floatval($amount);
		// formatting
		if (self::use_locale()) {
			$format = '%';
			$format.= !empty($options['symbol']) ? '' : '!';
			$format.= !empty($options['accounting']) ? '(' : '';
			$format.= isset($options['digits']) ? ('#' . $options['digits']) : '';
			$format.= isset($options['decimals']) ? ('.' . $options['decimals']) : '';
			$format.= 'n';
			if (isset($options['format'])) $format = $options['format'];
			return money_format($format, $amount);
		} else {
			$options['decimals'] = isset($options['decimals']) ? $options['decimals'] : 2;
			if (!empty($options['accounting']) && $amount < 0) {
				return '(' . number_format(abs($amount), $options['decimals']) . ')';
			} else {
				return number_format($amount, $options['decimals']);
			}
		}
	}

	/**
	 * Format currency
	 * 
	 * @param float $amount
	 * @param array $options
	 * @return string
	 */
	public static function currency_rate($amount, $options = array()) {
		$options['decimals'] = 8;
		return self::amount($amount, $options);
	}

	/**
	 * Format hourly rate
	 * 
	 * @param float $amount
	 * @param array $options
	 * @return string
	 */
	public static function hourly_rate($amount, $options = array()) {
		$options['decimals'] = 4;
		return self::amount($amount, $options);
	}

	/**
	 * Format Fiscal Year/Period
	 * 
	 * @param int $year
	 * @param int $period
	 * @param boolean $flag_year_only
	 * @return string
	 */
	public static function fiscal_year_period($year, $period, $flag_year_only = false) {
		if ($flag_year_only) {
			if (empty($year)) return '';
			return substr($year, 0, 4) . '-' . substr($year, 4, 2);
		} else {
			if (empty($year) && empty($period)) return '';
			return $year . '-' . $period;
		}
	}

	/**
	 * Format amount to show on cheques
	 * 
	 * @param float $amount
	 */
	public static function cheque($amount) {
		return number_format($amount, 2, '.', '');
	}
}