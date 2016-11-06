<?php

class format {

	/**
	 * Options
	 * 
	 * @var array
	 */
	public static $options;

	/**
	 * Default options
	 *
	 * @var array
	 */
	public static $defaut_options;

	/**
	 * Initialize
	 * 
	 * @param array $options
	 */
	public static function init($options = []) {
		self::$defaut_options = [
			'locale' => 'en_CA.utf8',
			'timezone' => 'America/Toronto',
			'server_timezone' => application::get('php.date.timezone'),
			'date' => 'Y-m-d',
			'time' => 'H:i:s',
			'datetime' => 'Y-m-d H:i:s',
			'timestamp' => 'Y-m-d H:i:s.u'
		];
		// settings from config files
		$config = application::get('flag.global.format');
		// settings from user account
		$entity = entity::groupped('format');
		// merge all of them together
		self::$options = array_merge_hard(self::$defaut_options, $config, $entity, $options);
		// set locale
		$short_locale = explode('.', self::$options['locale']);
		self::$options['locale_set_name'] = setlocale(LC_ALL, self::$options['locale'], $short_locale[0]);
		self::$options['locale_options'] = localeconv();
		// fix locale values
		self::$options['locale_options']['mon_thousands_sep'] = self::$options['locale_options']['mon_thousands_sep'] ?? ',';
		self::$options['locale_options']['mon_decimal_point'] = self::$options['locale_options']['mon_decimal_point'] ?? '.';
	}

	/**
	 * Determine if we can use locales
	 * 
	 * @return boolean
	 */
	public static function use_locale() {
		return !empty(self::$options['locale_set_name']);
	}

	/**
	 * Get date format
	 *
	 * @param string $type
	 * @return string
	 */
	public static function get_date_format($type) {
		return self::$options[$type];
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
		$format = str_replace('u', '000000', $format);
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
	 *		timestamp
	 * @param array $options
	 *		format  - for date function
	 * @return string
	 */
	public static function date_format($value, $type = 'date', $options = []) {
		if (empty($value)) {
			return null;
		}
		$format = $options['format'] ?? self::get_date_format($type);
		// additional handling for timestamp
		if (is_float($value)) {
			$temp = explode(self::$options['locale_options']['mon_decimal_point'] ?? '.', $value . '');
			$value = date('Y-m-d H:i:s', (int) $temp[0]) . '.' . $temp[1];
		}
		try {
			$object = new DateTime($value, new DateTimeZone(self::$options['server_timezone']));
			$object->setTimezone(new DateTimeZone(self::$options['timezone']));
			return $object->format($format);
		} catch (Exception $e) { // on exception we return as is
			return $value;
		}
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
	 * Format timestamp
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	public static function timestamp($value, $options = []) {
		return self::date_format($value, 'timestamp', $options);
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
		// if we need to format
		if (!empty($options['format'])) {
			return self::{$type}($time);
		}
		// rendering
		switch ($type) {
			case 'unix':
				return $time;
				break;
			case 'timestamp':
				return date('Y-m-d H:i:s', $time) . '.' . str_pad(round($msec * 1000000, 0), 6, '0', STR_PAD_LEFT);
				break;
			case 'time':
				return date('H:i:s', $time);
				break;
			case 'date':
				return date('Y-m-d', $time);
				break;
			case 'datetime':
			default:
				return date('Y-m-d H:i:s', $time);
		}
	}

	/**
	 * Datetime parts
	 *
	 * @param mixed $time
	 * @return array
	 */
	public static function datetime_parts($time = null) {
		if (empty($time)) {
			// important to call format::now function
			$time = self::now('unix');
		}
		if (!is_numeric($time)) {
			$time = strtotime($time);
		}
		return [
			'year' => (int) date('Y', $time),
			'month' => (int) date('m', $time),
			'day' => (int) date('d', $time),
			'hour' => (int) date('H', $time),
			'minute' => (int) date('i', $time),
			'second' => (int) date('s', $time),
			'weekday' => (int) date('w', $time)
		];
	}

	/**
	 * Read date
	 * 
	 * @param string $date
	 * @param string $type
	 * @return string
	 */
	public static function read_date($date, $type = 'date') {
		if (empty($date)) {
			return null;
		}
		// initialize options
		if (!isset(self::$options)) {
			self::init();
		}
		// dates are accepted as is
		if ($type == 'date') {
			$timezone = new DateTimeZone(self::$options['server_timezone']);
		} else {
			$timezone = new DateTimeZone(self::$options['timezone']);
		}
		// try to get a date from user format
		$object = DateTime::createFromFormat(self::$options[$type], $date, $timezone);
		if ($object === false) { // system format
			$object = DateTime::createFromFormat(self::$defaut_options[$type], $date, $timezone);
		}
		if ($object === false) { // strtotime
			$date = date('Y-m-d H:i:s', strtotime($date));
			$object = new DateTime($date, $timezone);
		}
		// convert between timezones
		$object->setTimezone(new DateTimeZone(self::$options['server_timezone']));
		return $object->format(self::$defaut_options[$type]);
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
	 * Read float
	 * 
	 * @param string/float $amount
	 * @param array $options
	 *		boolean - bcnumeric
	 *		boolean - valid_check
	 * @return mixed
	 */
	public static function read_floatval($amount, $options = []) {
		// remove currency symbol and name, thousands separator
		$amount = str_replace([
			self::$options['locale_options']['int_curr_symbol'],
			self::$options['locale_options']['currency_symbol'],
			self::$options['locale_options']['mon_thousands_sep']
		], '', $amount . '');
		// handle decimal separator
		if (self::$options['locale_options']['mon_decimal_point'] !== '.') {
			$amount = str_replace(self::$options['locale_options']['mon_decimal_point'], '.', $amount);
		}
		// sanitize only check
		if (!empty($options['valid_check'])) {
			return (filter_var($amount, $options['valid_check_type'] ?? FILTER_VALIDATE_FLOAT) !== false);
		}
		// if we are processing bc numeric data type
		if (!empty($options['bcnumeric'])) {
			$temp = filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			if ($temp === false || $temp === '') {
				$temp = '0';
			}
			return $temp;
		}
		// process based on type
		if (($options['valid_check_type'] ?? '') === FILTER_VALIDATE_INT) {
			return intval($amount);
		} else {
			return floatval($amount);
		}
	}

	/**
	 * Read bcnumeric
	 *
	 * @param string $amount
	 * @param array $options
	 * @return mixed
	 */
	public static function read_bcnumeric($amount, $options = []) {
		$options['bcnumeric'] = true;
		return self::read_floatval($amount, $options);
	}

	/**
	 * Read integer
	 * 
	 * @param mixed $amount
	 * @param array $options
	 *		boolean - valid_check
	 * @return number
	 */
	public static function read_intval($amount, $options = []) {
		$options['valid_check_type'] = FILTER_VALIDATE_INT;
		return self::read_floatval($amount, $options);
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
		$options['decimals'] = $options['decimals'] ?? 8;
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

	/**
	 * Format memory
	 *
	 * @param int $memory
	 * @param string $type
	 *		m - Mb
	 *		k - Kb
	 *		b - Bytes
	 * @return string
	 */
	public static function memory($memory, $type = 'm') {
		switch ($type) {
			case 'm':
				$suffix = 'Mb';
				$divider = 1000000;
				break;
			case 'k':
				$suffix = 'Kb';
				$divider = 1000;
				break;
			default:
				$suffix = 'b';
				$divider = 1;
		}
		return round($memory / $divider, 2) . ' ' . $suffix;
	}
}