<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Data\Model;

class Inactive2 extends Inactive
{
    public $data = [
        0 => ['no_data_model_inactive_name' => 'Yes'],
        1 => ['no_data_model_inactive_name' => 'No']
    ];
    public $alias_model = true;
}
