<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Framework\Helper\Model\Date;

use Object\Data;

class Months extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Date Months';
    public $column_key = 'id';
    public $column_prefix = ''; // do not change this
    public $orderby = [
        'id' => SORT_ASC
    ];
    public $columns = [
        'id' => ['name' => 'Month #', 'domain' => 'type_id'],
        'name' => ['name' => 'Name', 'type' => 'text']
    ];
    public $data = [
        1 => ['name' => 'January'],
        2 => ['name' => 'February'],
        3 => ['name' => 'March'],
        4 => ['name' => 'April'],
        5 => ['name' => 'May'],
        6 => ['name' => 'June'],
        7 => ['name' => 'July'],
        8 => ['name' => 'August'],
        9 => ['name' => 'September'],
        10 => ['name' => 'October'],
        11 => ['name' => 'November'],
        12 => ['name' => 'December'],
    ];
}
