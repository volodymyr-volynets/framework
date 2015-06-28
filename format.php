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
	 * Format date
	 * 
	 * @param string/int $date
	 * @return string
	 */
	public static function date($date) {
		if (empty($date)) return '';
		$date = is_numeric($date) ? $date : @strtotime($date);
		return date('Y-m-d', $date);
	}
	
	/**
	 * Format time
	 * 
	 * @param string/int $date
	 * @return string
	 */
	public static function time($date) {
		if (empty($date)) return '';
		$date = is_numeric($date) ? $date : @strtotime($date);
		return date('H:i:s', $date);
	}
	
	/**
	 * Format datetime
	 * 
	 * @param string/int $date
	 * @return string
	 */
	public static function datetime($date) {
		if (empty($date)) return '';
		$date = is_numeric($date) ? $date : @strtotime($date);
		return date('Y-m-d H:i:s', $date);
	}
	
	/**
	 * Current date and time
	 * 
	 * @return string
	 */
	public static function now($date_only = false) {
		// todo: convert to proper timezone, important
		return date($date_only ? 'Y-m-d' : 'Y-m-d H:i:s', time());
	}
	
	/**
	 * Determine date format
	 * 
	 * @param string $type
	 * @return string
	 */
	public static function date_format($type = 'php', $display = false) {
		$replaces = array(
			'php' => array('year4'=>'Y', 'year2'=>'y', 'month2'=>'m', 'month1'=>'n', 'day2'=>'d', 'day1'=>'j'),
			'jquery' => array('year4'=>'yy', 'year2'=>'y', 'month2'=>'mm', 'month1'=>'m', 'day2'=>'dd', 'day1'=>'d'),
		);
		$result = '2013-09-08';
		// year
		if (strpos($result, '2013')!==false) $result = str_replace('2013', $replaces[$type]['year4'], $result);
		if (strpos($result, '13')!==false) $result = str_replace('13', $replaces[$type]['year2'], $result);
		// month
		if (strpos($result, '09')!==false) $result = str_replace('09', $replaces[$type]['month2'], $result);
		if (strpos($result, '9')!==false) $result = str_replace('9', $replaces[$type]['month1'], $result);
		// day
		if (strpos($result, '08')!==false) $result = str_replace('08', $replaces[$type]['day2'], $result);
		if (strpos($result, '8')!==false) $result = str_replace('8', $replaces[$type]['day1'], $result);
		// display value
		if ($display) {
			$result = str_replace(array('Y', 'y', 'm', 'n', 'd', 'j'), array('YYYY', 'YY', 'MM', 'M', 'DD', 'D'), $result);
		}
		return $result;
	}
	
	/**
	 * Transform date from locale into php
	 * 
	 * @param string $date
	 * @return string
	 */
	public static function read_date($date) {
		if (empty($date)) return null;
		$date = is_numeric($date) ? $date : @strtotime($date);
		return date('Y-m-d', $date);
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
		if ($locale['decimal_point']!='.') $amount = str_replace($locale['decimal_point'], '.', $amount);
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