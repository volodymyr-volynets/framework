<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Format;

use Object\Data;

class UoM extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object Data UoM';
    public $column_key = 'no_frmtuom_code';
    public $column_prefix = 'no_frmtuom_';
    public $orderby;
    public $columns = [
        'no_frmtuom_code' => ['name' => 'Code', 'domain' => 'group_code'],
        'no_frmtuom_name' => ['name' => 'Name', 'type' => 'text'],
    ];
    public $options_map = [
        'no_frmtuom_name' => 'name',
    ];
    public $data = [
        'METRIC' => ['no_frmtuom_name' => 'Metric'],
        'IMPERIAL' => ['no_frmtuom_name' => 'Imperial'],
    ];
}
