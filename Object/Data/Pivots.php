<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Data;

use Object\Data;

class Pivots extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data Aliases';
    public $column_key = 'no_data_pivot_code';
    public $column_prefix = 'no_data_pivot_';
    public $orderby = ['no_data_pivot_name' => SORT_ASC];
    public $columns = [
        'no_data_pivot_code' => ['name' => 'Alias Code', 'type' => 'varchar', 'length' => 50],
        'no_data_pivot_name' => ['name' => 'Name', 'type' => 'text'],
        'no_data_pivot_model' => ['name' => 'Model', 'type' => 'text'],
        'no_data_pivot_column' => ['name' => 'Code Column', 'type' => 'text']
    ];
    public $data = [
        // data would come from overrides
    ];
}
