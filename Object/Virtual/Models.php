<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Virtual;

use Object\ACL\Resources;

class Models
{
    /**
     * Model
     *
     * @param string $class
     */
    public static function model($class, $options = [])
    {
        $temp = explode('\0Virtual0\\', $class);
        $last = array_pop($temp);
        $temp2 = explode('\\', $last);
        $model = Resources::getStatic(strtolower($temp2[0]), strtolower($temp2[1]), 'model');
        // create an object
        $parent_class = implode('\0Virtual0\\', $temp);
        $object = new $model($parent_class, $class, $options);
        return $object;
    }
}
