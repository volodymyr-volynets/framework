<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Data\PHP;

use Object\Data;

class Types extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data PHP Types';
    public $column_key = 'code';
    public $column_prefix = null; // you must not change it !!!
    public $columns = [
        'code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
        'name' => ['name' => 'Name', 'type' => 'text'],
    ];
    public $data = [
        'integer' => ['name' => 'Integer'],
        'float' => ['name' => 'Float'],
        'bcnumeric' => ['name' => 'BC Numeric'], // floats represented as strings
        'string' => ['name' => 'String'],
        'array' => ['name' => 'Array'],
        'mixed' => ['name' => 'Mixed']
    ];
}
