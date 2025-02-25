<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object;

use Object\Error\ResultException;

abstract class Middleware
{
    /**
     * @var array|null
     */
    protected static ?array $middlewares = null;

    /**
     * Run
     *
     * @param \Request $request
     * @param mixed $response
     * @param array $options
     * @return bool|array
     */
    abstract public function run(\Request $request, mixed $response, array $options = []): bool|array;

    /**
     * Get middleware static
     *
     * @param string $name
     * @return array|null
     */
    public static function getMiddlewareStatic(string $name): ?array
    {
        self::loadAllMiddlewareStatic();
        if (isset(self::$middlewares[$name])) {
            return self::$middlewares[$name];
        }
        foreach (self::$middlewares as $v) {
            if ($v['name'] === $name) {
                return $v;
            }
        }
        throw new \Exception('Middleware not found: ' . $name);
        return null;
    }

    /**
     * Get all always middleware (static)
     *
     * @param string $channel
     * @return array
     */
    public static function getAlwaysMiddlewareStatic(string $channel): ?array
    {
        self::loadAllMiddlewareStatic();
        $result = [];
        foreach (self::$middlewares as $k => $v) {
            if (in_array($channel, $v['channel']) && in_array('Always', $v['channel'])) {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    /**
     * Load all middleware (static)
     */
    protected static function loadAllMiddlewareStatic(): void
    {
        if (!isset(self::$middlewares)) {
            $middlewares = [];
            if (require_if_exists('./Miscellaneous/Middlewares/AllMiddlewares.php', true, $middlewares)) {
                self::$middlewares = $middlewares;
            } else {
                self::$middlewares = [];
            }
        }
    }

    /**
     * Run middlewares (static)
     *
     * @param array $middlewares
     * @param string $check
     * @param array $options
     * @return bool
     */
    public static function runMiddlewareStatic(array $middlewares, string $check = 'Before', array $options = []): bool
    {
        foreach ($middlewares as $k => $v) {
            // processs check middlewares
            if (!in_array($check, $v['check'])) {
                continue;
            }
            // wrap middleware call into try-catch block
            try {
                $method = \Factory::method($v['submodule'] . '::run', null, true);
                $all_options = array_merge_hard($v['options'] ?? [], $options);
                $result = call_user_func($method, \Application::$request, \Application::$response, $all_options);
                $throw = false;
            } catch (\Throwable $e) {
                $result = [
                    'success' => false,
                    'error' => [$e->getMessage()],
                ];
                $throw = true;
            }
            if (!$result['success']) {
                // log if maiddleware fails
                if (in_array('Log', $v['error'])) {
                    \Log::add([
                        'type' => 'Exception (Middleware)',
                        'only_channel' => ['default'],
                        'message' => 'Middleware failed!',
                        'other' => 'Middleware name: ' . $v['name'] . ', ' . implode(', ', $result['error']),
                        'error_rows' => count($result['error']),
                        'level' => 'EXCEPTION',
                    ]);
                }
                if (in_array('Throw', $v['error']) || $throw) {
                    throw new ResultException($result, $result['http_status_code'] ?? 0);
                }
            }
        }
        return true;
    }
}
