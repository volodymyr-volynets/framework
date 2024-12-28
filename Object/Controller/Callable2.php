<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Controller;

use Object\Controller;

class Callable2 extends Controller
{
    /**
     * Acl settings
     *
     * Public only
     *
     * @var array
     */
    public $acl = [
        'public' => true,
        'authorized' => true,
        'permission' => false
    ];

    /**
     * Callable action
     *
     * @param array $input
     */
    public function actionCallable()
    {
        $input = \Request::input();
        return call_user_func_array($this->route->callable, [$this, $input]);
    }
}
