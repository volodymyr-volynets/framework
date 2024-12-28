<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Content;

use Object\Data;

class ExportFormats extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Content Export Formats';
    public $column_key = 'format';
    public $column_prefix = '';
    public $columns = [
        'format' => ['name' => 'Format', 'type' => 'varchar', 'length' => 100],
        'name' => ['name' => 'Name', 'type' => 'text'],
        'model' => ['name' => 'Model', 'type' => 'text'],
        'delimiter' => ['name' => 'Delimiter', 'type' => 'text'],
        'enclosure' => ['name' => 'Enclosure', 'type' => 'text'],
        'extension' => ['name' => 'Extension', 'type' => 'text'],
        'content_type' => ['name' => 'Content Type', 'type' => 'text']
    ];
    public $data = [];
}
