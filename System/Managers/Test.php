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
