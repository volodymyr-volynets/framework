<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Workflow;

use Object\ACL\Resources;

class Base
{
    /**
     * Render
     */
    public static function render()
    {
        $model = Resources::getStatic('workflow', 'renderer', 'method');
        $workflow = \Session::get(['numbers', 'workflow']);
        if (!empty($model) && !empty($workflow)) {
            $method = \Factory::method($model);
            return call_user_func_array([$method[0], $method[1]], [$workflow]);
        }
        return '';
    }
}
