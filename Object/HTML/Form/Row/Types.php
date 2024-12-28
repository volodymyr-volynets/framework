<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\HTML\Form\Row;

use Object\Data;

class Types extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object HTML Form Row Types';
    public $column_key = 'no_html_form_row_type_code';
    public $column_prefix = 'no_html_form_row_type_';
    public $columns = [
        'no_html_form_row_type_code' => ['name' => 'Row Type', 'type' => 'varchar', 'length' => 30],
        'no_html_form_row_type_name' => ['name' => 'Name', 'type' => 'text']
    ];
    public $data = [
        'grid' => ['no_html_form_row_type_name' => 'Grid'],
        'table' => ['no_html_form_row_type_name' => 'Table'],
        'details' => ['no_html_form_row_type_name' => 'Details'],
        'tabs' => ['no_html_form_row_type_name' => 'Tabs']
    ];
}
