<?php

$settings = \Application::get('flag');
// process array into constant keys
$constants = [];
array_iterate_recursive_get_keys($settings, $constants, [], [
	'prefix' => 'NUMBERS_FLAG_',
	'uppercase' => true
]);
// inject timestamp constants
$constants['NUMBERS_FLAG_TIMESTAMP_DATE'] = \Format::now('date');
$constants['NUMBERS_FLAG_TIMESTAMP_DATETIME'] = \Format::now('datetime');
$constants['NUMBERS_FLAG_TIMESTAMP_TIME'] = \Format::now('time');
$constants['NUMBERS_FLAG_TIMESTAMP_TIMESTAMP'] = \Format::now('timestamp');
$constants['NUMBERS_FLAG_TIMESTAMP_UNIX_TIMESTAMP'] = \Format::now('unix');
// define constants
foreach ($constants as $k => $v) {
	define($k, $v);
}