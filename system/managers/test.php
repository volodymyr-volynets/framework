<?php

// benchmark
$benchmark = microtime(true);

// command line parameters available in $argv variable

// must change working directory to public_html
chdir('public_html');

// autoloading composer first
if (file_exists('../libraries/vendor/autoload.php')) {
	require('../libraries/vendor/autoload.php');
}

// automatic class loading
require('../libraries/vendor/numbers/framework/application.php');
spl_autoload_register(array('application', 'autoloader'));

// running application
application::run(['__run_only_bootstrap' => 1]);

// increase in memory and unlimited execution time
ini_set('memory_limit', '2048M');
set_time_limit(0);

// confirmation whether to run the script
//if (!helper_cmd::confirm("Conitune?")) exit;

// define result variable to keep scripts messages
$result = [
	'success' => false,
	'error' => [],
	'hint' => []
];

// wrapping everything into try-catch block for system exceptions
try {
	// testing memcached
	/*
	$memcached = new numbers_backend_cache_memcached_connection();
	$temp = $memcached->connect(['host' => '127.0.0.1', 'port' => 11211]);
	$temp = $memcached->set('test_key', 'some data', 5, $memcached::flag_tags, ['test_tag']);
	print_r2($temp);
	$temp = $memcached->get('test_key');
	print_r2($temp);
	exit;
	*/

/*
	$settings = application::get('cache.default2');
	$cache = new cache('default', $settings['submodule'], $settings);
	$temp = $cache->connect(current($settings['servers']));
	//print_r2($temp, 'connect');

	$temp = $cache->set('test1', ['some_data' => 555], 3, ['+tag1', 'tag2', 'tag3']);
	$temp = $cache->set('test2', ['some_data' => 555], 3, ['+tag1', 'tag2', 'tag3']);
	$temp = $cache->set('test3', ['some_data' => 555], 3, ['+tag1', 'tag3']);
	$temp = $cache->set('test4', ['some_data' => 555], 3, ['tag2', 'tag3']);
	$temp = $cache->set('test5', ['some_data' => 555], 3, ['tag2', 'tag3']);
	//print_r2($temp, 'set');
	//$temp = $cache->get('test1');
	//print_r2($temp, 'get');

	//sleep(4);

	$temp = $cache->gc(3, [['+tag1', 'tag2']]);
	print_r2($temp, 'gc');
	$temp = $cache->gc(3, [['tag3']]);
	print_r2($temp, 'gc');
	
	//$temp = $cache->get('test');
	//print_r2($temp, 'get');

	$temp = $cache->close();
	print_r2($temp, 'close');
*/

// error label
error:
	// hint
	if (!empty($result['hint'])) {
		echo "\n" . helper_cmd::color_string(implode("\n", $result['hint']), null, null, false) . "\n\n";
	}
	// if we did not succeed
	if (!$result['success']) {
		echo "\n" . helper_cmd::color_string(implode("\n", $result['error']), 'red', null, true) . "\n\n";
		exit;
	}
} catch(Exception $e) {
	echo "\n" . helper_cmd::color_string($e->getMessage(), 'red', null, true) . "\n\n" . $e->getTraceAsString() . "\n\n";
	exit;
}

// success message
$seconds = format::time_seconds(microtime(true) - application::get('application.system.request_time'));
echo "\nOperation completed in {$seconds} seconds!\n\n";
