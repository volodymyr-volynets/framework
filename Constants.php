<?php

$settings = \Application::get('flag');
// process array into constant keys
$constants = [];
array_iterate_recursive_get_keys($settings, $constants, [], [
	'prefix' => 'NUMBERS_FLAG_',
	'uppercase' => true
]);
// define constants
foreach ($constants as $k => $v) {
	define($k, $v);
}