<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\ACL\Resources;
use Object\Data\Domains;

class Format
{
    /**
     * Options
     *
     * @var array
     */
    public static $options = [
        'language_code' => 'sm0',
        'locale_code' => 'en_CA.UTF-8',
        'timezone_code' => 'America/Toronto', // user timezone
        'server_timezone_code' => 'America/Toronto',
        'format_date' => 'Y-m-d',
        'format_time' => 'H:i:s',
        'format_datetime' => 'Y-m-d H:i:s',
        'format_timestamp' => 'Y-m-d H:i:s.u',
        'format_amount_frm' => 20, // Amounts In Forms
        'format_amount_fs' => 40, // Amounts In Financial Statement
        'format_uom' => 'METRIC',
        // computed settings
        'locale_locales' => [], // list of locale codes
        'locale_locale_js' => null, // javascript locale name
        'locale_set_name' => null, // set locale
        'locale_options' => [], // localeconv() output would be stored here
        'locale_override_class' => null, // override class
        // symbols
        'symbols' => []
    ];

    /**
     * Default options
     *
     * @var array
     */
    public static $defaut_options;

    /**
     * Symbols
     *
     * For overrides to work you must define it in $symbol_defaults as well
     */
    public static $symbol_comma = ',';
    public static $symbol_dash = '-';
    public static $symbol_dot = '.';
    public static $symbol_question_mark = '?';
    public static $symbol_semicolon = ';';

    /**
     * Default symbols
     *
     * @var array
     */
    public static $symbol_defaults = [
        'comma' => ',',
        'dash' => '-',
        'dot' => '.',
        'question_mark' => '?',
        'semicolon' => ';'
    ];

    /**
     * Cached currencies
     *
     * @var array
     */
    public static $cached_currencies;

    /**
     * Initialized
     *
     * @var bool
     */
    public static $initialized = false;

    /**
     * Initialize
     *
     * @param array $options
     */
    public static function init(array $options = [])
    {
        // default options
        self::$defaut_options = [
            'language_code' => 'sm0',
            'locale_code' => 'en_CA.UTF-8',
            'timezone_code' => 'America/Toronto', // user timezone
            'server_timezone_code' => Application::get('php.date.timezone'),
            'format_date' => 'Y-m-d',
            'format_time' => 'H:i:s',
            'format_datetime' => 'Y-m-d H:i:s',
            'format_timestamp' => 'Y-m-d H:i:s.u',
            'format_amount_frm' => 20, // Amounts In Forms
            'format_amount_fs' => 40, // Amounts In Financial Statement
            'format_uom' => 'METRIC',
            // computed settings
            'locale_locales' => [], // list of locale codes
            'locale_locale_js' => null, // javascript locale name
            'locale_set_name' => null, // set locale
            'locale_options' => [], // localeconv() output would be stored here
            'locale_override_class' => null, // override class
            // symbols
            'symbols' => []
        ];
        // settings from config files
        $config = Application::get('flag.global.i18n');
        // settings from user account
        $user_settings = User::get('internalization');
        if (!empty($user_settings)) {
            foreach ($user_settings as $k => $v) {
                if (empty($v)) {
                    unset($user_settings[$k]);
                }
            }
        }
        // merge all of them together
        self::$options = array_merge_hard(self::$defaut_options, $config, I18n::$options, $user_settings, $options);
        // fix utf8
        self::$options['locale_code'] = str_replace(['utf8', 'utf-8'], 'UTF-8', self::$options['locale_code']);
        // generate a list of available locales
        $locale_settings = self::setLocale(self::$options['locale_code'], self::$defaut_options['locale_code']);
        self::$options = array_merge_hard(self::$options, $locale_settings);
        // fix values
        self::$options['format_amount_frm'] = (int) self::$options['format_amount_frm'];
        self::$options['format_amount_fs'] = (int) self::$options['format_amount_fs'];
        self::$options['locale_options']['mon_thousands_sep'] = self::$options['locale_options']['mon_thousands_sep'] ?: ',';
        self::$options['locale_options']['mon_decimal_point'] = self::$options['locale_options']['mon_decimal_point'] ?: '.';
        if (empty(self::$options['locale_options']['mon_grouping'])) {
            self::$options['locale_options']['mon_grouping'] = [3, 3];
        }
        // load data from models
        if (!empty(self::$options['model'])) {
            foreach (self::$options['model'] as $k => $v) {
                $method = Factory::method($v, null);
                self::$options['settings'][$k] = Factory::model($method[0], true)->{$method[1]}();
            }
            unset(self::$options['model']);
        }
        // handle overrides
        if (!empty(self::$options['locale_override_class'])) {
            // push js format version to frontend
            $locale_override_class = self::$options['locale_override_class'];
            $locale_override_class::js();
            // preset symbols
            if (!empty($locale_override_class::$symbol_defaults)) {
                foreach ($locale_override_class::$symbol_defaults as $k => $v) {
                    if (empty(self::$symbol_defaults[$k])) {
                        continue;
                    }
                    self::${'symbol_' . $k} = $v;
                }
            }
        }
        // symbols
        foreach (self::$symbol_defaults as $k => $v) {
            self::$options['symbols'][$k] = self::${'symbol_' . $k};
        }
        self::$initialized = true;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @param string $backup
     * @return array
     */
    public static function setLocale(string $locale, string $backup): array
    {
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
        // grab settings
        $result['locale_options'] = localeconv();
        // form a class name from locale name
        // todo: finilize locale
        $class = str_replace('-', '', $locale);
        $class = str_replace('.', '_', $class);
        $class = strtolower($class);
        // see if class exists
        $override_format_filename = __DIR__ . '/Object/Format/Locales/' . $class . '.php';
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
        } elseif (empty($result['locale_set_name']) && !empty($backup)) {
            $result = self::setLocale($backup, '');
        }
        return $result;
    }

    /**
     * Determine if we can use locales
     *
     * @return boolean
     */
    public static function useLocale()
    {
        return !empty(self::$options['locale_set_name']);
    }

    /**
     * Get date format
     *
     * @param string $type
     * @return string
     */
    public static function getDateFormat($type)
    {
        if (!self::$initialized) {
            self::init();
        }
        if (!isset(self::$options['format_' . $type])) {
            $type = 'date';
        }
        if (empty(self::$options['format_date'])) {
            self::$options['format_date'] = 'Y-m-d';
        }
        return self::$options['format_' . $type];
    }

    /**
     * Get placeholder base on format
     *
     * @param string $format
     * @return string
     */
    public static function getDatePlaceholder(string $format): string
    {
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
     *		skip_i18n
     *		skip_user_timezone
     * @return string|NULL
     */
    public static function dateFormat($value, string $type = 'date', array $options = [])
    {
        if (empty($value)) {
            return null;
        }
        $format = $options['format'] ?? self::getDateFormat($type);
        // additional handling for timestamp
        if (is_float($value)) {
            $temp = explode('.', $value . '');
            $value = date('Y-m-d H:i:s', (int) $temp[0]) . (isset($temp[1]) ? '.' . $temp[1] : '');
        }
        try {
            $server_timezone = self::$options['server_timezone_code'] ?? Application::get('php.date.timezone');
            if (is_a($value, 'DateTime')) {
                $object = $value;
            } else {
                $object = new DateTime($value, new DateTimeZone($server_timezone));
            }
            // change timezone
            if (empty($options['skip_user_timezone'])) {
                $object->setTimezone(new DateTimeZone(self::$options['timezone_code']));
            }
            $value = $object->format($format);
        } catch (Exception $e) {
            // on exception we return as is
        }
        // localize
        if (empty($options['skip_i18n'])) {
            $value = str_replace(['am', 'pm'], [i18n(null, 'am', ['skip_i_symbol' => true]), i18n(null, 'pm', ['skip_i_symbol' => true])], $value);
            return self::numberToFromNativeLanguage($value, $options);
        } else {
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
    public static function date($value, array $options = [])
    {
        return self::dateFormat($value, 'date', $options);
    }

    /**
     * Format sysdate
     *
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public static function sysdate($value, array $options = [])
    {
        return self::dateFormat($value, 'date', ['format' => 'Y-m-d']);
    }

    /**
     * Format sysdatetime no seconds
     *
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public static function sysdatetime($value, array $options = [])
    {
        return self::dateFormat($value, 'date', ['format' => $options['format'] ?? 'Y-m-d H:i:s']);
    }

    /**
     * Format time
     *
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public static function time($value, array $options = [])
    {
        return self::dateFormat($value, 'time', $options);
    }

    /**
     * Format datetime
     *
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public static function datetime($value, array $options = [])
    {
        return self::dateFormat($value, 'datetime', $options);
    }

    /**
     * Format nice datetime
     *
     * @param mixed $value
     * @param array $options
     *		boolean skip_user_timezone
     * @return string
     */
    public static function niceDatetime($value, array $options = [])
    {
        if (!isset($value)) {
            return '';
        }
        try {
            $server_timezone = self::$options['server_timezone_code'] ?? Application::get('php.date.timezone');
            $object = new DateTime($value, new DateTimeZone($server_timezone));
            // change timezone
            if (empty($options['skip_user_timezone'])) {
                $object->setTimezone(new DateTimeZone(self::$options['timezone_code']));
            }
            $value = i18n(null, $object->format('F')) . ' ' . $object->format('j') . self::$symbol_comma . ' ' . $object->format('Y');
            // process time
            $hours = intval($object->format('G'));
            $minutes = intval($object->format('i'));
            $seconds = intval($object->format('s'));
            // if we have time
            if (!($hours == 0 && $minutes == 0 && $seconds == 0)) {
                $timeformat = self::getDateFormat('time');
                $am_pm = stripos($timeformat, 'a') !== false;
                $timeformat = str_ireplace(['a', ' '], '', $timeformat);
                $time = $object->format($timeformat);
                $time = trim2($time, ':00$', '');
                $time = trim2($time, ':00$', '');
                if (!empty($time)) {
                    $value .= ' ' . $time;
                    if ($am_pm) {
                        $value .= ' ' . str_ireplace(['am', 'pm'], [i18n(null, 'am', ['skip_i_symbol' => true]), i18n(null, 'pm', ['skip_i_symbol' => true])], $object->format('a'));
                    }
                }
            }
        } catch (Exception $e) {
            // on exception we return as is
        }
        return $value;
    }

    /**
     * Format nice timestamp
     *
     * @param mixed $value
     * @param array $options
     *		boolean skip_user_timezone
     * @return string
     */
    public static function niceTimestamp($value, array $options = [])
    {
        if (!isset($value)) {
            return '';
        }
        try {
            $server_timezone = self::$options['server_timezone_code'] ?? Application::get('php.date.timezone');
            $object = new DateTime($value, new DateTimeZone($server_timezone));
            // change timezone
            if (empty($options['skip_user_timezone'])) {
                $object->setTimezone(new DateTimeZone(self::$options['timezone_code']));
            }
            // now and date
            $now = new DateTime('now', new DateTimeZone($server_timezone));
            $other = new DateTime($object->format(self::getDateFormat('date')), new DateTimeZone($server_timezone));
            // if its today we show time
            if ($now->diff($other)->days === 0) {
                $value = $object->format(self::getDateFormat('time'));
            } else {
                $value = $object->format(self::getDateFormat('date'));
            }
        } catch (Exception $e) {
            // on exception we return as is
        }
        return $value;
    }

    /**
     * Format timestamp
     *
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public static function timestamp($value, array $options = [])
    {
        return self::dateFormat($value, 'timestamp', $options);
    }

    /**
     * Time in seconds
     *
     * @param mixed $time
     * @param array $options
     * @return string
     */
    public static function timeSeconds($time, array $options = [])
    {
        return number_format($time, 4);
    }

    /**
     * Current date and time
     *
     * @param string $type
     * @param array $options
     * @return mixed
     */
    public static function now(string $type = 'datetime', array $options = [])
    {
        // todo: convert to proper timezone, important!!!
        list($msec, $time) = explode(" ", microtime());
        if (!empty($options['add_seconds'])) {
            $time += $options['add_seconds'];
        }
        // if we need to format
        if (!empty($options['format'])) {
            return self::{$type}($time + $msec, $options);
        }
        // rendering
        switch ($type) {
            case 'unix':
                return $time;
                break;
            case 'timestamp':
                return date('Y-m-d H:i:s', $time) . '.' . str_pad(round($msec * 1000000, 0), 6, '0', STR_PAD_LEFT);
                break;
            case 'timestamp_file':
                return date('Ymd_His', $time) . '_' . str_pad(round($msec * 1000000, 0), 6, '0', STR_PAD_LEFT);
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
    public static function datetimeParts($time = null): array
    {
        if (empty($time)) {
            // important to call Format::now function
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
     * @param string|null $date
     * @param string $type
     * @return string|NULL
     */
    public static function readDate($date, string $type = 'date')
    {
        if (empty($date)) {
            return null;
        }
        // initialize default settings if not initialized
        if (empty(self::$options)) {
            self::init();
        }
        // convert numbers
        $date = self::numberToFromNativeLanguage($date . '', [], true);
        $date = str_replace([i18n(null, 'am'), i18n(null, 'pm')], ['am', 'pm'], $date);
        // dates are accepted as is
        if ($type == 'date') {
            $timezone = new DateTimeZone(self::$options['server_timezone_code']);
        } else {
            $timezone = new DateTimeZone(self::$options['timezone_code']);
        }
        // try to get a date from user format
        $object = DateTime::createFromFormat(self::$options['format_' . $type], $date, $timezone);
        if ($object === false) { // system format
            $object = DateTime::createFromFormat(self::$defaut_options['format_' . $type], $date, $timezone);
        }
        if ($object === false) { // strtotime
            $date = date('Y-m-d H:i:s', strtotime($date));
            $object = new DateTime($date, $timezone);
        }
        // convert between timezones
        $object->setTimezone(new DateTimeZone(self::$options['server_timezone_code']));
        return $object->format(self::$defaut_options['format_' . $type]);
    }

    /**
     * Read 5 character time string
     *
     * @param mixed $time
     * @return NULL|string
     */
    public static function readTime5($time)
    {
        if (empty($time) || $time == 'NULL') {
            return null;
        }
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
     *		valid_check_type - FILTER_VALIDATE_INT or FILTER_VALIDATE_FLOAT
     * @return mixed
     */
    public static function readFloatval($amount, array $options = [])
    {
        $amount = self::numberToFromNativeLanguage($amount . '', $options, true);
        $negative = strpos($amount, '-') !== false || strpos($amount, '(') !== false;
        if (!empty(self::$options['locale_options']['mon_thousands_sep'])) {
            $amount = str_replace(self::$options['locale_options']['mon_thousands_sep'], '', $amount);
        }
        // handle decimal separator
        if (!empty(self::$options['locale_options']['mon_decimal_point']) && self::$options['locale_options']['mon_decimal_point'] !== '.') {
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
    public static function readBcnumeric($amount, array $options = [])
    {
        $options['bcnumeric'] = true;
        return self::readFloatval($amount, $options);
    }

    /**
     * Read integer
     *
     * @param mixed $amount
     * @param array $options
     *		boolean - valid_check
     * @return number|array
     */
    public static function readIntval($amount, array $options = [])
    {
        if (is_numeric_key_array($amount)) {
            return $amount;
        }
        $options['valid_check_type'] = FILTER_VALIDATE_INT;
        return self::readFloatval($amount, $options);
    }


    /**
     * Strip
     *
     * @param mixed $value
     * @param array $options
     * 		length - as int
     * @return string
     */
    public static function strip($value, array $options = []): string
    {
        $value = $value . '';
        $options['length'] = $options['length'] ?? 50;
        if (strlen($value) > $options['length']) {
            $value = substr($value, 0, $options['length']) . '...';
        }
        return $value;
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
    public static function amount($amount, $options = [])
    {
        if (!isset($amount) || $amount === '') {
            return null;
        }
        $amount = trim($amount . '');
        // if currency code is passed we need to load symbol
        if (!empty($options['currency_code'])) {
            if (!isset(self::$cached_currencies)) {
                $temp = Resources::getStatic('currencies', 'primary');
                if (!empty($temp)) {
                    self::$cached_currencies = $temp;
                } else {
                    self::$cached_currencies = [];
                }
            }
            if (!isset($options['symbol']) || (isset($options['symbol']) && $options['symbol'] !== false)) {
                $options['symbol'] = self::$cached_currencies[$options['currency_code']]['symbol'] ?? null;
            }
            // override decimals only if not set
            if (!isset($options['decimals'])) {
                $options['decimals'] = self::$cached_currencies[$options['currency_code']]['fraction_digits'] ?? null;
            }
        }
        // decimals
        if (!isset($options['decimals'])) {
            $options['decimals'] = Domains::getSetting('amount', 'scale');
        }
        // user defined monetary options
        if (empty($options['skip_user_settings'])) {
            // if type is not set then grab it from settings
            if (!empty($options['type'])) {
                $type = $options['type'];
            } elseif (empty($options['fs'])) {
                $type = self::$options['format_amount_frm'];
            } else {
                $type = self::$options['format_amount_fs'];
            }
            if ($type == 10) { // Amount (Locale, With Currency Symbol)
                $options['symbol'] = $options['symbol'] ?? self::$options['locale_options']['currency_symbol'];
            } elseif ($type == 20) { // Amount (Locale, Without Currency Symbol)
                $options['symbol'] = false;
            } elseif ($type == 30) { // Accounting (Locale, With Currency Symbol)
                $options['symbol'] = $options['symbol'] ?? self::$options['locale_options']['currency_symbol'];
                $options['accounting'] = $options['accounting'] ?? true;
            } elseif ($type == 40) { // Accounting (Locale, Without Currency Symbol)
                $options['symbol'] = false;
                $options['accounting'] = $options['accounting'] ?? true;
            } elseif ($type == 99) { // Plain Amount
                $options['accounting'] = false;
                $options['symbol'] = false;
                $options['locale_options']['mon_thousands_sep'] = '';
            }
        }
        // we need to clean number
        $amount = self::readFloatval($amount);
        // formatting if we use locale
        if (self::useLocale()) {
            if ($options['symbol'] ?? false) {
                $formater = new NumberFormatter(self::$options['locale_code'], NumberFormatter::CURRENCY);
            } else {
                $formater = new NumberFormatter(self::$options['locale_code'], NumberFormatter::PATTERN_DECIMAL);
            }
            if (!empty($options['currency_code'])) {
                $formater->setTextAttribute($formater::CURRENCY_CODE, $options['currency_code']);
            }
            $formater->setAttribute($formater::FRACTION_DIGITS, $options['decimals']);
            $result = $formater->format($amount);
            if (!empty($options['skip_decimal_zeros'])) {
                $result = rtrim($result, '0');
                $result = rtrim($result, '.');
            }
            if (!empty($options['accounting']) && $amount < 0) {
                return '(' . $result . ')';
            } else {
                return $result;
            }
        } else { // if we are not using locale
            if (!empty($options['accounting']) && $amount < 0) {
                return '(' . number_format(abs($amount), $options['decimals'], self::$options['locale_options']['mon_decimal_point'] ?? '.', self::$options['locale_options']['mon_thousands_sep']) . ')';
            } else {
                return number_format($amount, $options['decimals'], self::$options['locale_options']['mon_decimal_point'] ?? '.', self::$options['locale_options']['mon_thousands_sep']);
            }
        }
    }

    /**
     * Number
     *
     * @see Format::amount()
     */
    public static function number($amount, $options = [])
    {
        $options['symbol'] = false;
        return self::amount($amount, $options);
    }

    /**
     * Percent
     *
     * @see Format::amount()
     */
    public static function percent($amount, $options = [])
    {
        $options['symbol'] = false;
        return self::amount($amount, $options) . '%';
    }

    /**
     * Currency Rate
     *
     * @param float $amount
     * @param array $options
     * @return string
     */
    public static function currencyRate($amount, $options = [])
    {
        $options['decimals'] = Domains::getSetting('currency_rate', 'scale');
        $options['symbol'] = false;
        return self::amount($amount, $options);
    }

    /**
     * Unit price
     *
     * @param float $amount
     * @param array $options
     * @return string
     */
    public static function unitPrice($amount, $options = [])
    {
        $options['decimals'] = Domains::getSetting('unit_price', 'scale');
        return self::amount($amount, $options);
    }

    /**
     * Unit price (no symbol)
     *
     * @param float $amount
     * @param array $options
     * @return string
     */
    public static function unitPrice2($amount, $options = [])
    {
        $options['symbol'] = false;
        $options['decimals'] = Domains::getSetting('unit_price', 'scale');
        return self::amount($amount, $options);
    }

    /**
     * Quantity
     *
     * @see Format::amount()
     */
    public static function quantity($amount, $options = [])
    {
        $options['symbol'] = false;
        $options['decimals'] = Domains::getSetting('quantity', 'scale');
        return self::amount($amount, $options);
    }

    /**
     * Id
     *
     * @param mixed $id
     * @param array $options
     */
    public static function id($id, $options = [])
    {
        if (is_array($id)) {
            $result = [];
            foreach ($id as $v) {
                $result[] = self::numberToFromNativeLanguage($v . '', $options);
            }
            return implode(self::$symbol_comma . ' ', $result);
        } else {
            return self::numberToFromNativeLanguage($id . '', $options);
        }
    }

    /**
     * Translate a number to/from native language
     *
     * @param string $amount
     * @param array $options
     * @return string
     */
    public static function numberToFromNativeLanguage($number, $options, $from = false)
    {
        if (!empty(self::$options['locale_override_class'])) {
            $locale_override_class = self::$options['locale_override_class'];
            if (!$from) {
                $number = $locale_override_class::amount($number . '', $options);
            } else {
                $number = $locale_override_class::readFloatval($number . '', $options);
            }
        }
        return $number;
    }

    /**
     * Format amount to show on cheques
     *
     * @param float $amount
     */
    public static function cheque($amount)
    {
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
    public static function memory($memory, $type = 'm')
    {
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

    /**
     * First name
     *
     * @param string $name
     * @return string
     */
    public static function firstName(string $name): string
    {
        $result = explode(' ', trim($name));
        if (count($result) <= 2) {
            return $result[0];
        } else {
            return $result[0] . ' ' . $result[1];
        }
    }

    /**
     * Distance
     *
     * @param int|float $distance_in_m
     * @return string
     */
    public static function distance($distance_in_m): string
    {
        if (self::$options['format_uom'] == 'METRIC') {
            return round($distance_in_m / 1000, 2) . i18n(null, 'km');
        } else {
            return round($distance_in_m / 1.60934 / 1000, 2) . i18n(null, 'mi');
        }
    }

    /**
     * Nice duration
     *
     * @param int $value
     * @param array $options
     * @return string
     */
    public static function niceDuration($value, array $options = []): string
    {
        if (empty($value)) {
            return '';
        }
        if ($value < 1) {
            return self::id(round($value, 4)) . i18n(null, 'ms');
        } elseif ($value < 60) {
            return self::id(round($value, 0)) . i18n(null, 'sec');
        } elseif ($value < 360) {
            return self::id(round($value / 60, 2)) . i18n(null, 'min');
        } else {
            return self::id(round($value / 360, 2)) . i18n(null, 'hours');
        }
    }

    /**
     * Encode (HTML)
     *
     * @param string $value
     * @return string
     */
    public static function encode($value): string
    {
        return htmlentities($value);
    }

    /**
     * Phone
     *
     * @param string|number $value
     * @return string
     */
    public static function phone($value): string
    {
        $value = preg_replace('/[^0-9]/', '', $value . '');
        if (strlen($value) > 10) {
            $country = substr($value, 0, strlen($value) - 10);
            $area = substr($value, -10, 3);
            $next = substr($value, -7, 3);
            $last = substr($value, -4, 4);
            $value = '+' . $country . ' (' . $area . ') ' . $next . '-' . $last;
        } elseif (strlen($value) == 10) {
            $area = substr($value, 0, 3);
            $next = substr($value, 3, 3);
            $last = substr($value, 6, 4);
            $value = '(' . $area . ') ' . $next . '-' . $last;
        } elseif (strlen($value) == 7) {
            $next = substr($value, 0, 3);
            $last = substr($value, 3, 4);
            $value = $next . '-' . $last;
        }
        return $value;
    }
}
