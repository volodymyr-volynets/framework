<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Framework\Object\Form\Model;

use Object\Data;

class PageSizes extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Form Page Sizes';
    public $column_key = 'number';
    public $column_prefix = '';
    public $orderby = ['number' => SORT_ASC];
    public $columns = [
        'number' => ['name' => 'Page Size', 'type' => 'integer'],
        'name' => ['name' => 'Name', 'type' => 'text']
    ];
    public $data = [];

    /**
     * Get
     *
     * @param array $options
     */
    public function get($options = [])
    {
        $this->data = [
            // 1 => ['name' => 1], // for testing
            10 => ['name' => 10],
            20 => ['name' => 20],
            30 => ['name' => 30],
            50 => ['name' => 50],
            100 => ['name' => 100],
            250 => ['name' => 250],
            500 => ['name' => 500],
            PHP_INT_MAX => ['name' => 'All']
        ];
        return parent::get($options);
    }
}
