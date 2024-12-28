<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Table;

use Object\Data;

class Constraints extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object Table Constraints';
    public $column_key = 'no_table_constraint_type';
    public $column_prefix = 'no_table_constraint_';
    public $orderby = [];
    public $columns = [
        'no_table_constraint_code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
        'no_table_constraint_name' => ['name' => 'Name', 'type' => 'text']
    ];
    public $data = [
        'pk' => ['no_table_constraint_name' => 'Primary Key'],
        'unique' => ['no_table_constraint_name' => 'Unique'],
        'fk' => ['no_table_constraint_name' => 'Foreign Key'],
    ];
}
