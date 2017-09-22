<?php

namespace Object\Error;
class Base {

	/**
	 * All errors would be kept here
	 *
	 * @var array 
	 */
	public static $errors = [];

	/**
	 * Intercepted
	 *
	 * @var array
	 */
	public static $error_hashes_intersepted = [];

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
	 * Database/tenant not found
	 *
	 * @var boolean
	 */
	public static $flag_database_tenant_not_found = false;

	/**
	 * List of error codes
	 *
	 * @var array 
	 */
	public static $error_codes = [
		-1 => 'Visible To User',
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
		set_error_handler(['\Object\Error\Base', 'errorHandler']);
		set_exception_handler(['\Object\Error\Base', 'exceptionHandler']);
		ini_set('display_errors', 0);
	}

	/**
	 * Error handler function
	 *
	 * @param int $errno
	 * @param string $error
	 * @param string $file
	 * @param int $line
	 */
	public static function errorHandler($errno, $error, $file, $line) {
		// if its a javascript error submitted to backend
		if ($errno == 'javascript') {
			\Debug::$data['js'][] = [
				'errno' => $errno,
				'error' => [$error],
				'file' => $file,
				'line' => $line,
				'code' => '',
				'backtrace' => []
			];
		} else if (error_reporting() !== 0) { // important: we do not process suppressed errors
			self::$errors[] = [
				'errno' => $errno,
				'error' => [$error],
				'file' => $file,
				'line' => $line,
				'code' => self::getCode($file, $line),
				'backtrace' => self::debugBacktraceString()
			];
		} else if (\Debug::$debug) {
			\Debug::$data['suppressed'][] = [
				'errno' => $errno,
				'error' => [$error],
				'file' => $file,
				'line' => $line,
				'code' => self::getCode($file, $line),
				'backtrace' => self::debugBacktraceString()
			];
		}
		// hashing errors
		if ($errno != 'javascript') {
			if (!isseT(self::$error_hashes_intersepted[$file][$line])) {
				self::$error_hashes_intersepted[$file][$line] = [];
			}
			self::$error_hashes_intersepted[$file][$line][] = ['errno' => $errno, 'error' => $error];
		}
	}

	/**
	 * Exception handler function
	 *
	 * @param Exception $e
	 */
	public static function exceptionHandler(\Throwable $e) {
		self::$errors[] = [
			'errno' => $e->getCode(),
			'error' => [$e->getMessage()],
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'code' => self::getCode($e->getFile(), $e->getLine()),
			'backtrace' => self::debugBacktraceString($e->getTrace())
		];
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
	public static function getCode($file, $line) {
		if (empty($file)) {
			return '';
		}
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

	/**
	 * Format debug trace
	 *
	 * @param array $trace
	 * @return array
	 */
	public static function debugBacktraceString($trace = null) {
		$result = [];
		if (!\Application::get('debug.debug')) return $result;
		$i = 1;
		// if trace is not provided
		if (empty($trace)) {
			$trace = debug_backtrace();
			unset($trace[0]);
		}
		foreach($trace as $v) {
			$stack = '#' . $i . ' ' . (isset($v['file']) ? $v['file'] : 'Unknown');
			if (isset($v['line'])) {
				$stack.= '(' . $v['line'] . ')';
			}
			// do not show error handler
			if (!(isset($v['class']) && $v['class'] == 'error' && $v['function'] == 'error_handler')) {
				$stack.= ': ';
				if(isset($v['class'])) {
					$stack.= $v['class'] . $v['type'];
				}
				$params = [];
				if (isset($v['args'])) {
					foreach ($v['args'] as $v2) {
						if (gettype($v2) == 'string') {
							$params[] = str_replace(["\n", "\r", "\t"], ' ', $v2);
						} else if (is_array($v2)) {
							$temp = var_export_condensed($v2, ['skip_objects' => true]);
							$params[] = substr($temp, 0, 100) . '...';
						} else {
							$params[] = var_export_condensed($v2, ['skip_objects' => true]);
						}
					}
				}
				$stack.= $v['function'] . '(' . implode(', ', $params) . ');';
			}
			$i++;
			$result[] = $stack;
		}
		return $result;
	}
}