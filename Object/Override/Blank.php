<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Override;

#[\AllowDynamicProperties]
class Blank
{
    /**
     * Create new object and set properties
     *
     * @param array $vars
     * @return object
     */
    public static function __set_state($vars)
    {
        $object = new Blank();
        object_merge_values($object, $vars);
        return $object;
    }
}
