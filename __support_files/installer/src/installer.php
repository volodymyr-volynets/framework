<?php

// processing command line arguments
$temp = $argv;
$command = isset($temp[1]) ? trim($temp[1]) : 'version';
unset($temp[0], $temp[1]);
$get_params = array();
foreach ($temp as $k => $v) {
	$get_params[] = strpos($v, '=') !== false ? $v : ($v . '=');
}
$params = array();
parse_str(implode('&', $get_params), $params);

// version
if ($command == 'version') {
	echo "Numbers Installer: version 1.0.0\n";
	exit;
}

// if we need to make numbers.phar file
if ($command == '--build-phar-file') {
	$phar = new Phar('../build/numbers.phar', 0, 'numbers.phar');
	$phar->buildFromDirectory(dirname(__FILE__) . '/../src/');
	$phar->setStub($phar->createDefaultStub('installer.php'));
	chmod('../build/numbers.phar', 0777);
	exit;
}

// available commands
$commands = array('help', 'new_application', 'upgrade', 'clean', 'code_cleaner');

// redirecting to command handler
if (in_array($command, $commands)) {
	echo "Numbers Installer: entering $command action.\n";
	require_once "phar://numbers.phar/lib/functions.php";
	require_once "phar://numbers.phar/lib/{$command}.php";
} else {
	echo "Numbers Installer: Unknown command!\n";
}