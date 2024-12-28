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

class Inactive extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data Inactive';
    public $column_key = 'no_data_model_inactive_id';
    public $column_prefix = 'no_data_model_inactive_';
    public $columns = [
        'no_data_model_inactive_id' => ['name' => '#', 'type' => 'smallint', 'default' => 0],
        'no_data_model_inactive_name' => ['name' => 'Name', 'type' => 'text'],
    ];
    public $options_map = [
        'no_data_model_inactive_name' => 'name'
    ];
    public $data = [
        0 => ['no_data_model_inactive_name' => 'No'],
        1 => ['no_data_model_inactive_name' => 'Yes']
    ];
}
