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

class Types extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Content Types';
    public $column_key = 'no_content_type_code';
    public $column_prefix = 'no_content_type_';
    public $columns = [
        'no_content_type_code' => ['name' => 'Content Type', 'type' => 'varchar', 'length' => 100],
        'no_content_type_name' => ['name' => 'Name', 'type' => 'text'],
        'no_content_type_presentation' => ['name' => 'Is Presentational Flag', 'type' => 'boolean'],
    ];
    public $data = [
        // data transfer
        'application/javascript' => ['no_content_type_name' => 'Javascript'],
        'application/json' => ['no_content_type_name' => 'JSON'],
        'application/xml' => ['no_content_type_name' => 'XML'],
        // presentation
        'text/html' => ['no_content_type_name' => 'HTML', 'no_content_type_presentation' => 1],
        'application/pdf' => ['no_content_type_name' => 'PDF', 'no_content_type_presentation' => 1],
        'text/plain' => ['no_content_type_name' => 'Text', 'no_content_type_presentation' => 1],
        'application/vnd.ms-excel' => ['no_content_type_name' => 'Excel (xls)', 'no_content_type_presentation' => 1],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['no_content_type_name' => 'Excel (xlsx)', 'no_content_type_presentation' => 1],
        // images
        'image/png' => ['no_content_type_name' => 'Png Image'],
    ];
}
