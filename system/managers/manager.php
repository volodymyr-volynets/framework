<?php

// parameters
$type = $argv[1];
$mode = $argv[2];

// change dir to app
chdir('application');

// autoloading composer first
if (file_exists('../libraries/vendor/autoload.php')) {
	require('../libraries/vendor/autoload.php');
}

// automatic class loading
require('../libraries/vendor/numbers/framework/application.php');
spl_autoload_register(array('application', 'autoloader'));

// functions
require('../libraries/vendor/numbers/framework/functions.php');

// running proper class
switch ($type) {
	case 'deploy':
		$result = system_deployments::deploy(array('mode' => $mode));
		break;
	case 'dependency':
	default:
		$result = system_dependencies::process_deps_all(array('mode' => $mode));
}
if (!$result['success']) {
	echo implode("\n", $result['error']) . "\n";
} else {
	echo "Operation \"$type\" with mode \"$mode\" succeeded!\n";
}