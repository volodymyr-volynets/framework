<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Virtual;

use Object\Data;

class Controllers extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object Virtual Controllers';
    public $column_key = 'no_virtual_controller_code';
    public $column_prefix = 'no_virtual_controller_';
    public $columns = [
        'no_virtual_controller_code' => ['name' => 'Controller Code', 'type' => 'varchar', 'length' => 100],
        'no_virtual_controller_name' => ['name' => 'Name', 'type' => 'text'],
        // full controller path, for example /Numbers/Backend/Misc/TinyURL/Db/Controller/TinyURL
        'no_virtual_controller_path' => ['name' => 'Path', 'type' => 'text'],
    ];
    public $data = [];
}
