<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Model\Content;

use Object\Data;

class Types extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Form Content Types';
    public $column_key = 'no_form_content_type_code';
    public $column_prefix = 'no_form_content_type_';
    public $orderby = ['no_form_content_type_order' => SORT_ASC];
    public $columns = [
        'no_form_content_type_code' => ['name' => 'Type', 'type' => 'text'],
        'no_form_content_type_name' => ['name' => 'Name', 'type' => 'text'],
        'no_form_content_type_model' => ['name' => 'Model', 'type' => 'text'],
        'no_form_content_type_order' => ['name' => 'Order', 'type' => 'smallint', 'default' => 0]
    ];
    public $data = [
        'text/html' => ['no_form_content_type_name' => 'Screen (HTML)', 'no_form_content_type_model' => '\Numbers\Frontend\HTML\Form\Renderers\HTML\Base', 'no_form_content_type_order' => -32000],
    ];
}
