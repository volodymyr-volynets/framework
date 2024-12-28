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

class Widgets extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object Table Widgets';
    public $column_key = 'code';
    public $column_prefix = '';
    public $columns = [
        'code' => ['name' => 'Code', 'domain' => 'code'],
        'name' => ['name' => 'Name', 'type' => 'text'],
    ];
    public $data = [
        'attributes' => ['name' => 'Attributes'],
        'addresses' => ['name' => 'Addresses'],
        'audit' => ['name' => 'Audit'],
        'comments' => ['name' => 'Comments'],
        'documents' => ['name' => 'Documents'],
        'tags' => ['name' => 'Tags'],
        'owners' => ['name' => 'Owners'],
        'dates' => ['name' => 'Dates'],
        'service_scripts' => ['name' => 'Service Scripts'],
        'complaints' => ['name' => 'Complaints'],
        'checkins' => ['name' => 'Check In / Out'],
        'ingestions' => ['name' => 'Ingestions'],
        'surveys' => ['name' => 'Surveys'],
        'voters' => ['name' => 'Voters'],
    ];
}
