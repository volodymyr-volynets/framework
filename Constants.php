<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\ACL\Resources;
use Object\Error\Base;

$settings = Application::get('flag');
// process array into constant keys
$constants = [];
array_iterate_recursive_get_keys($settings, $constants, [], [
    'prefix' => 'NUMBERS_FLAG_',
    'uppercase' => true
]);
// formated values
$constants['NUMBERS_FLAG_TIMESTAMP_FORMATED_DATE'] = Format::getDatePlaceholder(Application::get('flag.global.i18n.format_date'));
$constants['NUMBERS_FLAG_TIMESTAMP_FORMATED_DATETIME'] = Format::getDatePlaceholder(Application::get('flag.global.i18n.format_datetime'));
$constants['NUMBERS_FLAG_TIMESTAMP_FORMATED_TIME'] = Format::getDatePlaceholder(Application::get('flag.global.i18n.format_time'));
$constants['NUMBERS_FLAG_TIMESTAMP_FORMATED_TIMESTAMP'] = Format::getDatePlaceholder(Application::get('flag.global.i18n.format_timestamp'));
// inject timestamp constants
$constants['NUMBERS_FLAG_TIMESTAMP_DATE'] = Format::now('date');
$constants['NUMBERS_FLAG_TIMESTAMP_DATETIME'] = Format::now('datetime');
$constants['NUMBERS_FLAG_TIMESTAMP_TIME'] = Format::now('time');
$constants['NUMBERS_FLAG_TIMESTAMP_TIMESTAMP'] = Format::now('timestamp');
$constants['NUMBERS_FLAG_TIMESTAMP_UNIX_TIMESTAMP'] = Format::now('unix');
$constants['NUMBERS_FLAG_CURRENT_YEAR'] = (int) date('Y');
// hostname
$constants['NUMBERS_HOSTNAME'] = Request::host();
// define constants
foreach ($constants as $k => $v) {
    define($k, $v);
}

// user values.
$user_data = User::get(null);
$user_variables = [
    'id',
    'code',
    'name',
    'company',
    'email',
    'phone',
    'cell',
    'fax',
    'login_username',
    'login_last_set',
    'hold',
    'inactive',
    'roles',
    'role_ids',
    'permissions',
    'organizations',
    'organization_countries',
    'super_admin',
    'maximum_role_weight',
    'linked_accounts',
    'teams',
    'features',
    'notifications',
    'subresources',
    'flags',
    'apis',
    'organization_id',
    'photo_file_id',
    'operating_country_code',
    'operating_province_code',
    'internalization'
];
foreach ($user_variables as $v) {
    define('NUMBERS_USER_PROFILE_' . strtoupper($v), $user_data[$v] ?? null);
}

// Owners.
if (is_null(User::$cached_owners) && !Base::$flag_database_tenant_not_found && Base::$flag_database_default_initiated) {
    User::$cached_owners = Resources::getStatic('owners', 'primary');
}
foreach (array_keys(User::$cached_owners ?? []) as $v) {
    define('NUMBERS_USER_PROFILE_' . $v, Can::userIsOwner($v));
}

// Include all constants from all modules
require_if_exists(Application::get(['application', 'path_full']) . 'Miscellaneous/Constants/AllConstants.php');
