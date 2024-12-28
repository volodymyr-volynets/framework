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

class WeekDays extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Date Week Days';
    public $column_key = 'id';
    public $column_prefix = ''; // do not change this
    public $orderby = [
        'id' => SORT_ASC
    ];
    public $columns = [
        'id' => ['name' => 'Week Day #', 'domain' => 'type_id'],
        'name' => ['name' => 'Name', 'type' => 'text']
    ];
    public $data = [
        1 => ['name' => 'Monday'],
        2 => ['name' => 'Tuesday'],
        3 => ['name' => 'Wednesday'],
        4 => ['name' => 'Thursday'],
        5 => ['name' => 'Friday'],
        6 => ['name' => 'Saturday'],
        7 => ['name' => 'Sunday'],
    ];
}
