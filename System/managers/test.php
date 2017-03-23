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
Application::run(['__run_only_bootstrap' => 1]);

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

	// add your code here
	$cache = new numbers_backend_cache_memcached_base('memcached', ['tags' => true]);
	$temp = $cache->connect(['host' => '127.0.0.1', 'port' => 11211]);
	print_r2($temp);
	// set tag
	$temp = $cache->set('test', ['some key' => 'some value'], null, ['+test1', 'test2']);
	print_r2($temp);
	// get tag
	$temp = $cache->get('test');
	print_r2($temp);
	// clear tags
	$temp = $cache->gc(3, [['+test1', 'test2', 'test3']]);
	print_r2($temp);

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
$seconds = Format::timeSeconds(microtime(true) - Application::get('application.system.request_time'));
echo "\nOperation completed in {$seconds} seconds!\n\n";
