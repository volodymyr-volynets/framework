<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

declare(ticks=1);

class Fiber2
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Add
     *
     * @param string|int $name
     * @param callable $callback
     * @param array $params
     * @return void
     */
    public function add(string|int $name, callable $callback, array $params): Fiber2
    {
        $this->data[$name] = [
            'fiber' => new Fiber($callback),
            'params' => $params,
        ];
        return $this;
    }

    /**
     * Run
     *
     * @return array<array|TReturn>|array{data: array, error: array, success: bool}
     */
    public function run(): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => []
        ];
        $has_error = false;
        while ($this->data) {
            foreach ($this->data as $k => $v) {
                try {
                    if (!$v['fiber']->isStarted()) {
                        register_tick_function('Fiber2::scheduler', $this->data);
                        $v['fiber']->start(...$v['params']);
                    } elseif ($v['fiber']->isTerminated()) {
                        $result['data'][$k] = $v['fiber']->getReturn();
                        unset($this->data[$k]);
                    } elseif ($v['fiber']->isSuspended()) {
                        $v['fiber']->resume();
                    }
                } catch (Throwable $e) {
                    $result['error'][$k] = $e;
                    unset($this->data[$k]);
                    $has_error = true;
                }
            }
        }
        unregister_tick_function('Fiber2::scheduler');
        if (!$has_error) {
            $result['success'] = true;
        }
        return $result;
    }

    /**
     * Iterate
     *
     * @return Generator
     */
    public function iterate(): Generator
    {
        while ($this->data) {
            foreach ($this->data as $k => $v) {
                try {
                    if (!$v['fiber']->isStarted()) {
                        register_tick_function('Fiber2::scheduler', $this->data);
                        $v['fiber']->start(...$v['params']);
                    } elseif ($v['fiber']->isTerminated()) {
                        yield $k => $v['fiber']->getReturn();
                        unset($this->data[$k]);
                    } elseif ($v['fiber']->isSuspended()) {
                        $v['fiber']->resume();
                    }
                } catch (Throwable $e) {
                    yield $k => $e;
                    unset($this->data[$k]);
                }
            }
        }
        unregister_tick_function('Fiber2::scheduler');
    }

    /**
     * Scheduler
     *
     * @param array $fibers
     * @return void
     */
    public static function scheduler(array $fibers): void
    {
        if (Fiber::getCurrent() === null) {
            return;
        }
        if (count($fibers) > 1) {
            Fiber::suspend();
        }
    }
}
