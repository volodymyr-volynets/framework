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

class Order extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data Order';
    public $column_key = 'no_data_model_order_id';
    public $column_prefix = 'no_data_model_order_';
    public $columns = [
        'no_data_model_order_id' => ['name' => '#', 'type' => 'smallint', 'default' => 0],
        'no_data_model_order_name' => ['name' => 'Name', 'type' => 'text'],
    ];
    public $options_map = [
        'no_data_model_order_name' => 'name'
    ];
    public $data = [
        SORT_ASC => ['no_data_model_order_name' => 'Ascending'],
        SORT_DESC => ['no_data_model_order_name' => 'Descending']
    ];
}
