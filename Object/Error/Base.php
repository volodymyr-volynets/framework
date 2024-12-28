<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Error;

class Base
{
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
     * Database initiated (default)
     *
     * @var boolean
     */
    public static $flag_database_default_initiated = false;

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
    public static function init()
    {
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
    public static function errorHandler($errno, $error, $file, $line)
    {
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
            \Log::add([
                'type' => 'Error',
                'only_chanel' => ['default'],
                'status' => $errno,
                'message' => 'Javascript error ocured!',
                'other' => 'Error: ' . $error,
                'affected_rows' => 0,
                'error_rows' => 1,
                'trace' => null,
                'level' => 'JAVASCRIPT'
            ]);
        } elseif (error_reporting() !== 0) { // important: we do not process suppressed errors
            self::$errors[] = [
                'errno' => $errno,
                'error' => [$error],
                'file' => $file,
                'line' => $line,
                'code' => self::getCode($file, $line),
                'backtrace' => self::debugBacktraceString()
            ];
            \Log::add([
                'type' => 'Error',
                'only_chanel' => ['default'],
                'status' => $errno,
                'message' => 'Error ocured!',
                'other' => 'Error: ' . $error,
                'affected_rows' => 0,
                'error_rows' => 1,
                'trace' => Base::debugBacktraceString(null, ['skip_params' => true]),
                'level' => self::$error_codes[$errno],
            ]);
        } elseif (\Debug::$debug) {
            \Debug::$data['suppressed'][] = [
                'errno' => $errno,
                'error' => [$error],
                'file' => $file,
                'line' => $line,
                'code' => self::getCode($file, $line),
                'backtrace' => self::debugBacktraceString()
            ];
            \Log::add([
                'type' => 'Error',
                'only_chanel' => ['default'],
                'status' => $errno,
                'message' => 'Supperssed error ocured!',
                'other' => 'Error: ' . $error,
                'affected_rows' => 0,
                'error_rows' => 1,
                'trace' => Base::debugBacktraceString(null, ['skip_params' => true]),
                'level' => self::$error_codes[$errno],
            ]);
        }
        // hashing errors
        if ($errno != 'javascript') {
            if (!isset(self::$error_hashes_intersepted[$file][$line])) {
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
    public static function exceptionHandler(\Throwable $e)
    {
        $code = $e->getCode();
        switch (get_class($e)) {
            case UserException::class:
                $code = -1;
                $type = 'Exception (User)';
                break;
            case ResourseNotFoundException::class:
                $code = -1;
                $type = 'Exception (Resourse Not Found)';
                break;
            case PermissionException::class:
                $code = -1;
                $type = 'Exception (Permission)';
                break;
            default:
                $type = 'Exception (General)';
        }
        self::$errors[] = [
            'errno' => $code,
            'error' => explode("\n", $e->getMessage()),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => self::getCode($e->getFile(), $e->getLine()),
            'backtrace' => self::debugBacktraceString($e->getTrace())
        ];
        \Log::add([
            'type' => $type,
            'only_chanel' => ['default'],
            'status' => $code,
            'message' => 'Exception ocured!',
            'other' => 'Exception: ' . $e->getMessage(),
            'affected_rows' => 0,
            'error_rows' => 1,
            'trace' => Base::debugBacktraceString($e->getTrace(), ['skip_params' => true]),
            'level' => 'EXCEPTION',
        ]);
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
    public static function getCode($file, $line)
    {
        if (empty($file) || !file_exists($file)) {
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
    public static function debugBacktraceString($trace = null, array $options = [])
    {
        $result = [];
        //if (!\Debug::$debug) return $result;
        $i = 1;
        // if trace is not provided
        if (empty($trace)) {
            $trace = debug_backtrace();
            unset($trace[0]);
        }
        foreach ($trace as $v) {
            $stack = '#' . $i . ' ' . (isset($v['file']) ? $v['file'] : 'Unknown');
            if (isset($v['line'])) {
                $stack .= '(' . $v['line'] . ')';
            }
            // do not show error handler
            if (!(isset($v['class']) && $v['class'] == 'Object\Error\Base' && $v['function'] == 'errorHandler')) {
                $stack .= ': ';
                if (isset($v['class'])) {
                    $stack .= $v['class'] . $v['type'];
                }
                $params = [];
                if (isset($v['args'])) {
                    foreach ($v['args'] as $v2) {
                        if (gettype($v2) == 'string') {
                            $params[] = str_replace(["\n", "\r", "\t", "'", '"'], ' ', $v2);
                        } elseif (is_array($v2)) {
                            $temp = var_export_condensed($v2, ['skip_objects' => true]);
                            $temp = str_replace(["\n", "\r", "\t", "'", '"'], ' ', $temp);
                            $params[] = substr($temp, 0, 200) . '...';
                        } else {
                            $temp = var_export_condensed($v2, ['skip_objects' => true]);
                            $temp = str_replace(["\n", "\r", "\t", "'", '"'], ' ', $temp);
                            $params[] = substr($temp, 0, 200) . '...';
                        }
                    }
                }
                $stack .= $v['function'] . '(' . implode(', ', $params) . ');';
            }
            $i++;
            $result[] = $stack;
        }
        return $result;
    }
}
