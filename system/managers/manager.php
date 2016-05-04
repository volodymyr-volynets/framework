<?php

// parameters
$type = $argv[1];
$mode = $argv[2];

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

// wrapping everything into try-catch block for system exceptions
try {
	// running proper class
	switch ($type) {
		case 'deploy':
			$result = system_deployments::deploy(['mode' => $mode]);
			break;
		case 'schema':
			$result = system_dependencies::process_models(['mode' => $mode]);
			break;
		case 'dependency':
		default:
			$result = system_dependencies::process_deps_all(['mode' => $mode]);
	}

	// hint
	if (!empty($result['hint'])) {
		echo implode("\n", $result['hint']) . "\n";
	}

	// if we did not succede
	if (!$result['success']) {
		echo implode("\n", $result['error']) . "\n";
		exit;
	}
} catch(Exception $e) {
	echo $e->getMessage() . "\n";
}

// if we succedded
echo "\nOperation \"$type\" with mode \"$mode\" completed!\n";