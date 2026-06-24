<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Pipeline
{
    /**
     * @var array
     */
    protected array $args = [];

    /**
     * @var array
     */
    protected array $result = [
        'success' => false,
        'error' => [],
        'count' => 0,
        'data' => []
    ];

    /**
     * Arguments
     *
     * @param array $args
     * @return Pipeline
     */
    public function args(...$args): Pipeline
    {
        $this->args = func_get_args();
        return $this;
    }

    /**
     * Call
     *
     * @param callable $func
     * @return Pipeline
     */
    public function call(callable $func): Pipeline
    {
        $this->result['count']++;
        try {
            $this->result['data'][] = call_user_func_array($func, $this->args);
        } catch (Throwable $e) {
            $this->result['error'][] = 'Pipeline: ' . $e->getMessage();
        }
        return $this;
    }

    /**
     * Finish
     *
     * @return array
     */
    public function finish(): array
    {
        $this->result['success'] = count($this->result['error']) == 0;
        return $this->result;
    }
}

/*
 * Usage example:
$result = new Pipeline()
    ->args(1, 2, 3)
    ->call(fn ($a, $b, $c) => $a . '-' . $b . '-' . $c)
    ->call(fn ($a, $b, $c) => $a . '_' . $b . '_' . $c)
    ->finish();

print_r2($result);
*/
