<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Validator;

abstract class Base
{
    /**
     * Result
     *
     * @var array
     */
    protected $result = [
        'success' => false,
        'error' => [],
        'data' => null,
        'placeholder' => null,
        'placeholder_select' => null
    ];

    /**
     * Validate
     *
     * @param string $value
     * @param array $options
     * @return array
     */
    abstract public function validate($value, $options = []);

    /**
     * Call validator method
     *
     * @param string $method
     * @param array $params
     * @param array $options
     * @param array $neighbouring_values
     * @return array
     */
    public static function method($method, $value, $params = [], $options = [], $neighbouring_values = [])
    {
        $method = \Factory::method($method);
        $params = $params ?? [];
        $params['options'] = $options;
        $params['neighbouring_values'] = $neighbouring_values;
        return \Factory::model($method[0], true)->{$method[1]}($value, $params);
    }
}
