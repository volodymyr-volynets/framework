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

class Indexes extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object Table Indexes';
    public $column_key = 'no_table_index_code';
    public $column_prefix = 'no_table_index_';
    public $orderby = [];
    public $columns = [
        'no_table_index_code' => ['name' => 'Type', 'type' => 'varchar', 'length' => 30],
        'no_table_index_name' => ['name' => 'Name', 'type' => 'text']
    ];
    public $data = [
        'btree' => ['no_table_index_name' => 'Btree'],
        'fulltext' => ['no_table_index_name' => 'Full Text']
    ];
}
