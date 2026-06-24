<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Deferred
{
    public const DB_PRE_BEGIN = '__db_pre_commit';
    public const DB_AFTER_COMMIT = '__db_after_commit';
    public const APPLICATION_FINISH_AND_DESTROYED = '__application_finished_and_destroyed';
    public const ALL = '__all';
    public const OTHER = '__other';

    public const TYPES = [
        self::DB_PRE_BEGIN => ['name' => 'Db pre commit hook'],
        self::DB_AFTER_COMMIT => ['name' => 'Db after commit hook'],
        self::APPLICATION_FINISH_AND_DESTROYED => ['name' => 'Application finished and destroyed hook'],
    ];

    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * Run later
     *
     * @param string $name
     * @param array $args
     * @param callable $func
     * @return Deferred
     */
    public function runLater(string $name, callable $func, array $args = []): Deferred
    {
        if (isset(self::TYPES[$name])) {
            self::$data[$name] ??= [];
            self::$data[$name][] = [
                'func' => $func,
                'args' => $args,
            ];
        } else {
            self::$data[self::OTHER][$name] = [
                'func' => $func,
                'args' => $args,
            ];
        }
        return $this;
    }

    /**
     * Run later (static)
     *
     * @param string $name
     * @param callable $func
     * @param array $args
     * @return Deferred
     */
    public static function runLaterStatic(string $name, callable $func, array $args = []): Deferred
    {
        $object = new static();
        return $object->runLater($name, $func, $args);
    }

    /**
     * Forget
     *
     * @param string $name
     * @return Deferred
     */
    public function forget(string $name): Deferred
    {
        if (isset(self::TYPES[$name])) {
            self::$data[$name] = [];
        } else {
            unset(self::$data[self::OTHER][$name]);
        }
        return $this;
    }

    /**
     * Forget (static)
     *
     * @param string $name
     * @return Deferred
     */
    public static function forgetStatic(string $name): Deferred
    {
        $object = new static();
        return $object->forget($name);
    }

    /**
     * Execute all runs
     *
     * @param string $type
     * @return array[]|array{count: int, data: array, error: array, success: bool}
     */
    public static function executeAllRuns(string $type = self::ALL): array
    {
        $result = [
            'success' => true,
            'error' => [],
            'count' => 0,
            'data' => [],
        ];
        foreach (self::$data as $k => $v) {
            if ($type == self::ALL || $k == $type) {
                foreach ($v as $k2 => $v2) {
                    try {
                        $key = $k2;
                        if (isset(self::TYPES[$k])) {
                            $key = $k;
                        }
                        $result['data'][$key] = call_user_func_array($v2['func'], $v2['args']);
                    } catch (Throwable $e) {
                        $result['error'][] = 'Deferred: ' . $k . '::' . $k2 . ', message: ' . $e->getMessage();
                        $result['success'] = false;
                    }
                    $result['count']++;
                    // we need to unset data in case this method is called in the middle
                    unset(self::$data[$k][$k2]);
                }
            }
        }
        return $result;
    }
}
