<?php

class error {

	/**
	 * All errors would be kept here
	 *
	 * @var array 
	 */
	public static $errors = [];

	/**
	 * If we have an exception
	 *
	 * @var boolean 
	 */
	public static $flag_exception = false;

	/**
	 * Whether we are rendering error screen
	 *
	 * @var boolean
	 */
	public static $flag_error_already = false;

	/**
	 * List of error codes
	 *
	 * @var array 
	 */
	public static $error_codes = [
		0 => 'EXCEPTION',
		1 => 'E_ERROR',
		2 => 'E_WARNING',
		4 => 'E_PARSE',
		8 => 'E_NOTICE',
		16 => 'E_CORE_ERROR',
		32 => 'E_CORE_WARNING',
		64 => 'E_COMPILE_ERROR',
		128 => 'E_COMPILE_WARNING',
		256 => 'E_USER_ERROR',
		512 => 'E_USER_WARNING',
		1024 => 'E_USER_NOTICE',
		2048 => 'E_STRICT',
		4096 => 'E_RECOVERABLE_ERROR',
		8192 => 'E_DEPRECATED',
		16384 => 'E_USER_DEPRECATED',
		32767 => 'E_ALL'
	];

	/**
	 * Initialize error handler
	 */
	public static function init() {
		set_error_handler(array('error', 'error_handler'));
		set_exception_handler(array('error', 'exception_handler'));
		ini_set('display_errors', 0);
	}

	/**
	 * Error handler function
	 *
	 * @param int $errno
	 * @param string $errmsg
	 * @param string $file
	 * @param int $line
	 */
	public static function error_handler($errno, $errmsg, $file, $line) {
		// important: we do not process suppressed errors
		if (error_reporting() !== 0) {
			$result = [
				'errno' => $errno,
				'error' => [$errmsg],
				'file' => $file,
				'line' => $line,
				'code' => self::get_code($file, $line)
			];
			self::$errors[] = $result;
		} else if (debug::$debug) {
			debug::$data['suppressed'][] = [
				'errno' => $errno,
				'error' => [$errmsg],
				'file' => $file,
				'line' => $line,
				'code' => self::get_code($file, $line)
			];
		}
	}

	/**
	 * Exception handler function
	 *
	 * @param Exception $e
	 */
	public static function exception_handler(Exception $e) {
		$result = [
			'errno' => $e->getCode(),
			'error' => [$e->getMessage()],
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'code' => self::get_code($e->getFile(), $e->getLine())
		];
		self::$errors[] = $result;
		self::$flag_exception = true;
		exit;
	}

	/**
	 * Get code snippet
	 *
	 * @param string $file
	 * @param int $line
	 * @return string
	 */
	public static function get_code($file, $line) {
		$rows = explode("\n", file_get_contents($file));
		$start = ($line - 6) > 0 ? ($line - 6) : 0;
		$end = ($line + 5) < count($rows) ? ($line + 5) : count($rows);
		$result = [];
		for ($i = $start; $i < $end; $i++) {
			if ($i == $line - 1) {
				$result[] = '<b>' . $rows[$i] . '</b>';
			} else {
				$result[] = $rows[$i];
			}
		}
		return implode("\n", $result);
	}
}