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

use Object\Data;

class Boolean extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data Boolean';
    public $column_key = 'no_data_model_boolean_id';
    public $column_prefix = 'no_data_model_boolean_';
    public $columns = [
        'no_data_model_boolean_id' => ['name' => '#', 'type' => 'smallint'],
        'no_data_model_boolean_name' => ['name' => 'Name', 'type' => 'text'],
    ];
    public $options_map = [
        'no_data_model_boolean_name' => 'name'
    ];
    public $data = [
        0 => ['no_data_model_boolean_name' => 'False'],
        1 => ['no_data_model_boolean_name' => 'True']
    ];
}
