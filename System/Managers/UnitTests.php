<?php

// must change working directory to public_html
chdir('public_html');

// autoloading composer first
if (file_exists('../libraries/vendor/autoload.php')) {
	require('../libraries/vendor/autoload.php');
}

// automatic class loading
require('../libraries/vendor/Numbers/Framework/Application.php');
spl_autoload_register(array('Application', 'autoloader'));

// running application
Application::run(['__run_only_bootstrap' => 1]);

// increase in memory and unlimited execution time
ini_set('memory_limit', '2048M');
set_time_limit(0);

// confirmation whether to run the script
if (!\Helper\Cmd::confirm("Run Unit Tests?")) exit;

// this is a must to read configuration file
chdir('..');