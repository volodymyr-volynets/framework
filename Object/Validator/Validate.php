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

class Validate
{
    /**
     * Validate method
     *
     * @param string|array $method
     * @param mixed $value
     * @return array
     */
    public static function validateMethod(string|array $method, $value, array $options = []): array
    {
        if (is_array($method)) {
            $method = implode('::', $method);
        }
        $object = \Factory::method($method, null, true);
        return call_user_func_array($object, [$value, $options]);
    }
}
