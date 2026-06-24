<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\cURL;
use Object\Reflection;

class ConcurrentCalls
{
    /**
     * @var array
     */
    public array $funcs = [
        'before' => [],
        'funcs' => [],
        'after' => [],
    ];

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (!Can::submoduleExists('\Numbers\Backend\System\Concurrencies')) {
            throw new Exception('You must include Numbers.Backend.System.Concurrencies submodule!');
        }
    }

    /**
     * Add
     *
     * @param Closure|callable $func
     * @param array $args
     * @return ConcurrentCalls
     */
    public function add(Closure|callable $func, array $args = []): ConcurrentCalls
    {
        $this->funcs['funcs'][] = [
            'func' => $func,
            'args' => $args,
        ];
        return $this;
    }

    /**
     * Before
     *
     * @param Closure|callable $func
     * @param array $args
     * @return ConcurrentCalls
     */
    public function before(Closure|callable $func, array $args = []): ConcurrentCalls
    {
        $this->funcs['before'][] = [
            'func' => $func,
            'args' => $args,
        ];
        return $this;
    }

    /**
     * Before
     *
     * @param Closure|callable $func
     * @param array $args
     * @return ConcurrentCalls
     */
    public function after(Closure|callable $func, array $args = []): ConcurrentCalls
    {
        $this->funcs['after'][] = [
            'func' => $func,
            'args' => $args,
        ];
        return $this;
    }

    /**
     * Run
     *
     * @return array
     */
    public function run(): array
    {
        $urls = [];
        $crypt = new Crypt();
        foreach ($this->funcs['funcs'] as $v) {
            $temp = [
                'before' => [],
                'funcs' => [],
                'after' => [],
            ];
            // before
            if (count($this->funcs['before']) > 0) {
                foreach ($this->funcs['before'] as $v2) {
                    $temp['before'][] = [
                        'func' => Reflection::getClosure($v2['func']),
                        'args' => $v2['args'],
                    ];
                }
            }
            // actual function to execute
            $temp['funcs'][] = [
                'func' => Reflection::getClosure($v['func']),
                'args' => $v['args'],
            ];
            // after
            if (count($this->funcs['after']) > 0) {
                foreach ($this->funcs['after'] as $v2) {
                    $temp['after'][] = [
                        'func' => Reflection::getClosure($v2['func']),
                        'args' => $v2['args']
                    ];
                }
            }
            $urls[] = [
                'url' => Route::getEndpoint(Numbers\Backend\System\Concurrencies\API\V1\SM\ConcurrentCalls::class, 'postConcurrentCall', ['include_host' => true]),
                'post' => true,
                'raw' => (new Json2([
                    'token' => $crypt->tokenCreate(0, 'sm.concurrent', null, ['skip_urlencoded' => true]),
                    'funcs' => $temp,
                ]))->toJSON(),
            ];
        }
        $return = cURL::multiExecPost($urls, ['json' => true]);
        $result = [
            'success' => true,
            'error' => [],
            'data' => []
        ];
        foreach ($return['data'] as $k => $v) {
            if (empty($v['success'])) {
                $result['success'] = false;
                if (is_string($v)) {
                    $result['error'][] = $v;
                } else {
                    $result['error'] = array_merge($result['error'], $v['error']);
                }
            }
            $result['data'][$k] = $v['data'] ?? $v;
        }
        return $result;
    }
}
