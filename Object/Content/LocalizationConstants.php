<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Content;

use Object\Reflection;

class LocalizationConstants
{
    /**
     * @var array
     */
    public $loc = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $class = get_called_class();
        $constants = Reflection::getConstants($class);
        foreach ($constants as $k => $v) {
            $key = array_key_first($v);
            $this->loc[$key] = $v[$key];
        }
    }
}
