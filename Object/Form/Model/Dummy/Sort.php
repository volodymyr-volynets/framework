<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Model\Dummy;

use Object\Table;

class Sort extends Table
{
    public $db_link;
    public $db_link_flag;
    public $module_code = 'SM';
    public $title = 'S/M Dummy Sort';
    public $schema;
    public $name = 'sm_dummy_sort';
    public $pk = ['sort'];
    public $tenant;
    public $orderby;
    public $limit;
    public $column_prefix;
    public $columns = [
        'sort' => ['name' => 'Sort', 'type' => 'group_code'],
        'order' => ['name' => 'Order', 'type' => 'text'],
    ];
    public $constraints = [];
    public $indexes = [];
    public $history = false;
    public $audit = false;
    public $optimistic_lock = false;
    public $options_map = [];
    public $options_active = [];
    public $engine = [
        'MySQLi' => 'InnoDB'
    ];

    public $cache = false;
    public $cache_tags = [];
    public $cache_memory = false;

    public $data_asset = [
        'classification' => 'proprietary',
        'protection' => 2,
        'scope' => 'global'
    ];
}
