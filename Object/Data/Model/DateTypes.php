<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Data\Model;

use Object\Data;
use Helper\Date;

class DateTypes extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data Date Types';
    public $column_key = 'no_data_date_type_code';
    public $column_prefix = 'no_data_date_type_';
    public $columns = [
        'no_data_date_type_code' => ['name' => 'Code', 'domain' => 'group_code'],
        'no_data_date_type_name' => ['name' => 'Name', 'domain' => 'name'],
        'no_data_date_type_order' => ['name' => 'Order', 'domain' => 'order'],
    ];
    public $options_map = [
        'no_data_date_type_name' => 'name'
    ];
    public $orderby = [
        'no_data_date_type_order' => SORT_ASC,
    ];
    public $data = [
        'LAST_30_DAYS' => ['no_data_date_type_name' => 'Last 30 days', 'no_data_date_type_order' => 1000],
        'LAST_7_DAYS' => ['no_data_date_type_name' => 'Last 7 days', 'no_data_date_type_order' => 2000],
    ];

    /**
     * Generate start and end dates
     *
     * @param string $type
     * @param string|null $now
     * @return array{date1: null, date2: mixed, date_type: string}
     */
    public static function generateStartAndEndDates(string $type, string|null $now = null): array
    {
        // set current date
        if (empty($now)) {
            $now = \Format::now('datetime');
        }
        $result = [
            'date1' => null,
            'date2' => $now,
            'date_type' => $type,
        ];
        switch ($type) {
            case 'LAST_7_DAYS':
                $result['date_type'] = 'LAST_7_DAYS';
                $result['date1'] = Date::addInterval($result['date2'], '-7 days');
                break;
            case 'LAST_30_DAYS':
            default:
                $result['date_type'] = 'LAST_30_DAYS';
                $result['date1'] = Date::addInterval($result['date2'], '-30 days');
        }
        return $result;
    }
}
