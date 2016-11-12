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
		// default options
		self::$defaut_options = [
			'language_code' => 'sys',
			'locale' => 'en_CA.UTF-8',
			'timezone' => 'America/Toronto', // user timezone
			'server_timezone' => application::get('php.date.timezone'),
			'date' => 'Y-m-d',
			'time' => 'H:i:s',
			'datetime' => 'Y-m-d H:i:s',
			'timestamp' => 'Y-m-d H:i:s.u',
			'amount_frm' => 20, // Amounts In Forms
			'amount_fs' => 40, // Amounts In Financial Statement
			'settings' => [
				'currency_codes' => [], // a list of currency settings
			],
			// computed settings
			'locale_locales' => [], // list of locale codes
			'locale_locale_js' => null, // javascript locale name
			'locale_set_name' => null, // set locale
			'locale_options' => [], // localeconv() output would be stored here
			'locale_override_class' => null, // override class
		];
		// settings from config files
		$config = application::get('flag.global.format');
		// settings from user account
		$entity = entity::groupped('format');
		// merge all of them together
		self::$options = array_merge_hard(self::$defaut_options, $config, i18n::$options, $entity, $options);
		// fix utf8
		self::$options['locale'] = str_replace(['utf8', 'utf-8'], 'UTF-8', self::$options['locale']);
		// generate a list of available locales
		$locale_settings = self::set_locale(self::$options['locale'], self::$defaut_options['locale']);
		self::$options = array_merge_hard(self::$options, $locale_settings);
		// fix values
		self::$options['amount_frm'] = (int) self::$options['amount_frm'];
		self::$options['amount_fs'] = (int) self::$options['amount_fs'];
		self::$options['locale_options']['mon_thousands_sep'] = self::$options['locale_options']['mon_thousands_sep'] ?? ',';
		self::$options['locale_options']['mon_decimal_point'] = self::$options['locale_options']['mon_decimal_point'] ?? '.';
		if (empty(self::$options['locale_options']['mon_grouping'])) {
			self::$options['locale_options']['mon_grouping'] = [3, 3];
		}
		// load data from models
		if (!empty(self::$options['model'])) {
			foreach (self::$options['model'] as $k => $v) {
				$method = factory::method($v, null);
				self::$options['settings'][$k] = factory::model($method[0], true)->{$method[1]}();
			}
			unset(self::$options['model']);
		}
		// push js format version to frontend
		if (!empty(self::$options['locale_override_class'])) {
			$locale_override_class = self::$options['locale_override_class'];
			$locale_override_class::js();
		}
	}

	/**
	 * Set locale
	 *
	 * @param string $locale
	 * @return array
	 */
	public static function set_locale($locale, $backup) {
		$result = [
			'locale_locales' => [],
			'locale_locale_js' => null,
			'locale_set_name' => null,
			'locale_options' => [],
			'locale_override_class' => null
		];
		$temp = $locale;
		$result['locale_locales'] = [];
		$result['locale_locales'][] = $temp;
		if (strpos($temp, '@') !== false) {
			$temp = explode('@', $temp);
			$temp = $temp[0];
			$result['locale_locales'][] = $temp;
		}
		if (strpos($temp, '.') !== false) {
			$temp = explode('.', $temp);
			$temp = $temp[0];
			$result['locale_locales'][] = $temp;
		}
		$result['locale_locale_js'] = str_replace('_', '-', $temp);
		$result['locale_set_name'] = setlocale(LC_ALL, $result['locale_locales']);
		// if we are unsuccessful
		if (empty($result['locale_set_name'])) {
			$result_backup = self::set_locale($backup, null);
		}
		// grab settings
		$result['locale_options'] = localeconv();
		// form a class name from locale name
		$class = str_replace('-', '', $locale);
		$class = str_replace('.', '_', $class);
		$class = strtolower($class);
		// see if class exists
		$override_format_filename = __DIR__ . '/object/format/locales/' . $class . '.php';
		if (file_exists($override_format_filename)) {
			require_once($override_format_filename);
			$class = 'object_format_locales_' . $class;
			$result['locale_override_class'] = $class;
			// grab overrides
			$result['locale_options'] = $class::localeconv($result['locale_options']);
			// if locale does not exists but we have override we simply accept it
			if (empty($result['locale_set_name'])) {
				$result['locale_set_name'] = $locale;
			}
		} else if (empty($result['locale_set_name'])) {
			$result = $result_backup;
		}
		return $result;
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
			$temp = explode('.', $value . '');
			$value = date('Y-m-d H:i:s', (int) $temp[0]) . '.' . $temp[1];
		}
		try {
			$object = new DateTime($value, new DateTimeZone(self::$options['server_timezone']));
			$object->setTimezone(new DateTimeZone(self::$options['timezone']));
			$value = $object->format($format);
		} catch (Exception $e) { // on exception we return as is
			// nothing
		}
		// localize
		$value = str_replace(['am', 'pm'], [i18n(null, 'am'), i18n(null, 'pm')], $value);
		return self::number_to_from_native_language($value, $options);
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
		// initialize default settings if not initialized
		if (empty(self::$options)) {
			self::init();
		}
		// convert numbers
		$date = self::number_to_from_native_language($date . '', [], true);
		$date = str_replace([i18n(null, 'am'), i18n(null, 'pm')], ['am', 'pm'], $date);
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
		$amount = self::number_to_from_native_language($amount . '', $options, true);
		$negative = strpos($amount, '-') !== false || strpos($amount, '(') !== false;
		$amount = str_replace(self::$options['locale_options']['mon_thousands_sep'], '', $amount);
		// handle decimal separator
		if (self::$options['locale_options']['mon_decimal_point'] !== '.') {
			$amount = str_replace(self::$options['locale_options']['mon_decimal_point'], '.', $amount);
		}
		$amount = preg_replace('/[^0-9.]/', '', $amount);
		if ($negative) {
			$amount = '-' . $amount;
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
	 * Amount
	 *
	 * @param mixed $amount
	 * @param array $options
	 *		boolean skip_user_settings
	 *		string format
	 *		string symbol
	 *		boolean accounting
	 *		int digits
	 *		int decimals
	 *		string currency_code
	 * @return string
	 */
	public static function amount($amount, $options = []) {
		// if currency code is passed we need to load symbol
		if (!empty($options['currency_code'])) {
			$options['symbol'] = self::$options['settings']['currency_codes'][$options['currency_code']]['symbol'] ?? null;
		}
		// user defined monetary options
		if (empty($options['skip_user_settings'])) {
			// if type is not set then grab it from settings
			if (!empty($options['type'])) {
				$type = $options['type'];
			} else if (empty($options['fs'])) {
				$type = self::$options['amount_frm'];
			} else {
				$type = self::$options['amount_fs'];
			}
			if ($type == 10) { // Amount (Locale, With Currency Symbol)
				$options['symbol'] = $options['symbol'] ?? self::$options['locale_options']['currency_symbol'];
			} else if ($type == 20) { // Amount (Locale, Without Currency Symbol)
				$options['symbol'] = false;
			} else if ($type == 30) { // Accounting (Locale, With Currency Symbol)
				$options['symbol'] = $options['symbol'] ?? self::$options['locale_options']['currency_symbol'];
				$options['accounting'] = $options['accounting'] ?? true;
			} else if ($type == 40) { // Accounting (Locale, Without Currency Symbol)
				$options['symbol'] = false;
				$options['accounting'] = $options['accounting'] ?? true;
			} else if ($type == 99) { // Plain Amount
				$options['accounting'] = false;
				$options['symbol'] = false;
				$options['locale_options']['mon_thousands_sep'] = '';
			}
		}
		// formatting if we use locale
		if (self::use_locale()) {
			$amount = self::money_format($amount, $options);
		} else { // if we are not using locale
			if (!empty($options['accounting']) && $amount < 0) {
				$amount = '(' . number_format(abs($amount), $options['decimals'] ?? 2, self::$options['locale_options']['mon_decimal_point'], self::$options['locale_options']['mon_thousands_sep']) . ')';
			} else {
				$amount = number_format($amount, $options['decimals'] ?? 2, self::$options['locale_options']['mon_decimal_point'], self::$options['locale_options']['mon_thousands_sep']);
			}
		}
		return $amount;
	}

	/**
	 * Number
	 * 
	 * @see format::amount()
	 */
	public static function number($amount, $options = []) {
		$options['symbol'] = false;
		return self::amount($amount, $options);
	}

	/**
	 * Quantity
	 *
	 * @see format::amount()
	 */
	public static function quantity($amount, $options = []) {
		$options['symbol'] = false;
		$options['decimals'] = $options['decimals'] ?? 4;
		return self::amount($amount, $options);
	}

	/**
	 * Id
	 *
	 * @param mixed $id
	 * @param array $options
	 */
	public static function id($id, $options = []) {
		return self::number_to_from_native_language($id . '', $options);
	}

	/**
	 * Translate a number to/from native language
	 *
	 * @param string $amount
	 * @param array $options
	 * @return string
	 */
	public static function number_to_from_native_language($number, $options, $from = false) {
		if (!empty(self::$options['locale_override_class'])) {
			$locale_override_class = self::$options['locale_override_class'];
			if (!$from) {
				$number = $locale_override_class::amount($number . '', $options);
			} else {
				$number = $locale_override_class::read_floatval($number . '', $options);
			}
		}
		return $number;
	}

	/**
	 * Money format
	 *
	 * @param mixed $amount
	 * @param array $options
	 * @return string
	 */
	public static function money_format($amount, $options = []) {
		$format = array_merge_hard(self::$options['locale_options'], $options['locale_options'] ?? []);
		$options['decimals'] = $options['decimals'] ?? 2;
		// sometimes symbols contain decimal point, we change it to thousands_sep
		if (!empty($options['symbol'])) {
			$options['symbol'] = str_replace($format['mon_decimal_point'], $format['mon_thousands_sep'], $options['symbol']);
		} else {
			$options['symbol'] = '';
		}
		// convert to string
		if (!is_string($amount)) {
			$amount = $amount . '';
		}
		$negative = strpos($amount, '-') !== false;
		$amount = ltrim($amount, '-');
		// if the number portion has been formatted
		if (empty($options['amount_partially_formatted'])) {
			$temp = explode('.', $amount);
			$number = $temp[0];
			$fraction = $temp[1] ?? '';
			// process number
			if (empty($number)) $number = '0';
			if ($format['mon_thousands_sep'] . '' !== '' && !empty($format['mon_grouping'])) {
				$counter = 0;
				$mon_grouping = [];
				$symbols = array_reverse(mb_str_split($number), true);
				$number = '';
				foreach ($symbols as $k => $v) {
					// grab group size
					if ($counter == 0) {
						if (empty($mon_grouping)) $mon_grouping = $format['mon_grouping'];
						if (count($mon_grouping) > 1) {
							$counter = array_shift($mon_grouping);
						} else {
							$counter = $mon_grouping[0];
						}
					}
					// skip number of characters
					$counter--;
					$number = $v . $number;
					if ($counter == 0 && $k > 0) {
						$number = $format['mon_thousands_sep'] . $number;
					}
				}
			}
			// left precision
			if (!empty($options['digits'])) {
				if (strlen($number) < $options['digits']) {
					$number = str_pad($number, $options['digits'], ' ', STR_PAD_LEFT);
				}
			}
			// right precision
			if ($options['decimals'] > 0) {
				$fraction = substr(str_pad($fraction, $options['decimals'], '0', STR_PAD_RIGHT), 0, $options['decimals']);
				$number = $number . $format['mon_decimal_point'] . $fraction;
			}
		} else {
			$number = $amount;
		}
		// translate characters
		$number = self::number_to_from_native_language($number, $options);
		// format based on settings
		$cs_precedes = $negative ? $format['n_cs_precedes'] : $format['p_cs_precedes'];
		$sep_by_space = $negative ? $format['n_sep_by_space'] : $format['p_sep_by_space'];
		$sign_posn = $negative ? $format['n_sign_posn'] : $format['p_sign_posn'];
		// if accounting formatting
		if (!empty($options['accounting'])) {
			// if we have currency symbol we added it based on settings
			if (!empty($options['symbol'])) {
				$number = ($cs_precedes ? ($options['symbol'] . ($sep_by_space === 1 ? ' ' : '')) : '') . $number . (!$cs_precedes ? (($sep_by_space === 1 ? ' ' : '') . $options['symbol']) : '');
			}
			if ($negative) {
				$number = '(' . $number . ')';
			} else {
				$number = ' ' . $number . ' ';
			}
		} else {
			$positive_sign = $format['positive_sign'];
			$negative_sign = $format['negative_sign'];
			$sign = $negative ? $negative_sign : $positive_sign;
			$other_sign = $negative ? $positive_sign : $negative_sign;
			$sign_padding = '';
			if ($sign_posn) {
				for ($i = 0; $i < (strlen($other_sign) - strlen($sign)); $i++) {
					$sign_padding.= ' ';
				}
			}
			$temp_value = '';
			switch ($sign_posn) {
				case 0: // parentheses surround value and currency symbol
					if (!empty($options['symbol'])) {
						$number = $cs_precedes ? ($options['symbol'] . ($sep_by_space === 1 ? ' ' : '') . $number) : ($number . ($sep_by_space === 1 ? ' ' : '') . $options['symbol']);
					}
					$number = '(' . $number . ')';
					break;
				case 1: // sign precedes
					if (!empty($options['symbol'])) {
						$number = $cs_precedes ? ($options['symbol'] . ($sep_by_space === 1 ? ' ' : '') . $number) : ($number . ($sep_by_space === 1 ? ' ' : '') . $options['symbol']);
					}
					$number = $sign_padding . $sign . ($sep_by_space === 2 ? ' ' : '') . $number;
					break;
				case 2: // sign follows
					if (!empty($options['symbol'])) {
						$number = $cs_precedes ? ($options['symbol'] . ($sep_by_space === 1 ? ' ' : '') . $number) : ($number . ($sep_by_space === 1 ? ' ' : '') . $options['symbol']);
					}
					$number = $number . ($sep_by_space === 2 ? ' ' : '') . $sign . $sign_padding;
					break;
				case 3: //sign precedes currency symbol
					$symbol = '';
					if (!empty($options['symbol'])) {
						$symbol = $cs_precedes ? ($options['symbol'] . ($sep_by_space === 1 ? ' ' : '')) : (($sep_by_space === 2 ? ' ' : '') . $options['symbol']);
					}
					$number = $cs_precedes ? ($sign_padding . $sign . ($sep_by_space === 2 ? ' ' : '') . $symbol . $number) : ($number . ($sep_by_space === 1 ? ' ' : '') . $sign . $sign_padding . $symbol);
					break;
				case 4: // sign succeeds currency symbol
					$symbol = '';
					$symbol_sep = '';
					if (!empty($options['symbol'])) {
						$symbol = $options['symbol'];
						$symbol_sep = ($sep_by_space === 1 ? ' ' : '');
					}
					$number = $cs_precedes ? ($symbol . ($sep_by_space === 2 ? ' ' : '') . $sign_padding . $sign . $symbol_sep . $number) : ($number . $symbol_sep . $symbol . ($sep_by_space === 2 ? ' ' : '') . $sign . $sign_padding);
					break;
			}
		}
		return $number;
	}

	/**
	 * Amount format options
	 *
	 * @param array $options
	 * @return array
	 */
	public function amount_format_options($options = []) {
		return [
			10 => ['name' => 'Amount (Locale, With Currency Symbol)', 'title' => '$ -123,456.00'],
			20 => ['name' => 'Amount (Locale, Without Currency Symbol)', 'title' => '-123,456.00'],
			30 => ['name' => 'Accounting (Locale, With Currency Symbol)', 'title' => '$(123,456.00)'],
			40 => ['name' => 'Accounting (Locale, Without Currency Symbol)', 'title' => '(123,456.00)'],
			99 => ['name' => 'Plain Amount', 'title' => '-123456.00']
		];
	}

	/**
	 * Currency Rate
	 * 
	 * @param float $amount
	 * @param array $options
	 * @return string
	 */
	public static function currency_rate($amount, $options = []) {
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
	public static function hourly_rate($amount, $options = []) {
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