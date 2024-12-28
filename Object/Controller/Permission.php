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

class Permission extends Controller
{
    /**
     * Acl settings
     *
     * Permissions only
     *
     * @var array
     */
    public $acl = [
        'public' => false,
        'authorized' => false,
        'permission' => true
    ];
}
