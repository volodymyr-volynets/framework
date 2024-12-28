<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Cmd;

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
ini_set('memory_limit', '8192M');
set_time_limit(0);
Application::set('debug.debug', 0);

// confirmation whether to run the script
if (!Cmd::confirm("Run Unit Tests?")) {
    exit;
}

// this is a must to read configuration file
chdir('..');
