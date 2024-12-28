<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Traits;

trait Stringable
{
    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        // it will call __debugInfo on an object
        return print_r($this, true);
    }
}
