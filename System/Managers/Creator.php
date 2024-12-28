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
use Helper\File;
use System\Dependencies;

// command line parameters
$command = $argv[1];
$type = $argv[2] ?? '';
$mode = 'commit';
$module = $argv[3] ?? '';
$name = $argv[4] ?? '';
$skip_confirmation = $argv[5] ?? false;

// must change working directory to public_html
chdir('public_html');

// autoloading composer first
if (file_exists('../libraries/vendor/autoload.php')) {
    require('../libraries/vendor/autoload.php');
}

// running application
require('../libraries/vendor/numbers/framework/Application.php');
Application::run(['__run_only_bootstrap' => 1]);

// include constants
require('../libraries/vendor/numbers/framework/Constants.php');

// disable debug
Debug::$debug = false;

// increase in memory and unlimited execution time
ini_set('memory_limit', '2048M');
set_time_limit(0);

// confirmation whether to run the script
if (empty($skip_confirmation) || $skip_confirmation == 2) {
    $type_new = $type;
    if ($type[0] == '\\') {
        $type_new = array_reverse(explode('\\', $type))[0];
    }
    if (!Cmd::confirm("Conitune operation \"$type_new\" with mode \"$mode\"?")) {
        exit;
    }
}

// define result variable to keep scripts messages
$result = [
    'success' => false,
    'error' => [],
    'hint' => []
];

// we need to put command into application
Application::set('manager.enabled', true);
Application::set('manager.command.type', $command . '_' . $type);
Application::set('manager.command.mode', 'commit');
Application::set('manager.command.full', $command . '_' . $type . '_' . $mode);

// wrapping everything into try-catch block for system exceptions
try {
    // load module
    $result = Dependencies::processDepsAll(['mode' => 'test', 'skip_confirmation' => $skip_confirmation, 'show_warnings' => true]);
    $modules = $result['data']['module'] ?? [];
    $module_codes = array_keys($result['data']['module']);
    $module_mains = [];
    foreach ($module_codes as $v) {
        if (strlen($v) == 2) {
            $module_mains[] = $v;
        }
    }
    $types = ['Controller', 'Model'];
    switch ($command . '_' . strtolower($type)) {
        case 'create_controller':
            // validate module
            if (empty($module) || !in_array($module, $module_codes)) {
                throw new Exception('Empty or unknown module, available are: ' . implode(', ', $module_mains));
            }
            if (empty($name)) {
                throw new Exception('Empty or unknown name');
            }
            // validate name
            $parts = (new String2($name))->replace('\\', '/')->explode('/')->toArray();
            foreach ($parts as $v) {
                if ((new String2($v))->spaceOnUpperCase()->pascalCase()->toString() !== $v) {
                    throw new Exception('Name with directory must be PascalCase\PascalCase\PascalCase... , given: ' . $v);
                }
            }
            $classname = array_pop($parts);
            $dirname = '';
            $filedir = $modules[$module]['dir'] . 'Controller';
            if ($parts) {
                $dirname = '\\' . implode('\\', $parts);
                $filedir .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
            }
            $namespace = $modules[$module]['name'] . '\\Controller' . $dirname;
            // generate template
            $template = File::read(__DIR__ . '/../Template/Controller.template.txt');
            $template = str_replace([
                '{classname}',
                '{namespace}',
            ], [
                $classname,
                $namespace,
            ], $template);
            if (!file_exists($filedir)) {
                File::mkdir($filedir, 0777, ['skip_realpath' => true]);
            }
            File::write($filedir . DIRECTORY_SEPARATOR . $classname . '.php', $template, 0777);
            break;
        case 'create_model':
            // validate module
            if (empty($module) || !in_array($module, $module_codes)) {
                throw new Exception('Empty or unknown module, available are: ' . implode(', ', $module_mains));
            }
            if (empty($name)) {
                throw new Exception('Empty or unknown name');
            }
            // validate name
            $parts = (new String2($name))->replace('\\', '/')->explode('/')->toArray();
            foreach ($parts as $v) {
                if ((new String2($v))->spaceOnUpperCase()->pascalCase()->toString() !== $v) {
                    throw new Exception('Name with directory must be PascalCase/PascalCase/PascalCase... , given: ' . $v);
                }
            }
            $classname = array_pop($parts);
            $dirname = '';
            $filedir = $modules[$module]['dir'] . 'Model';
            if ($parts) {
                $dirname = '\\' . implode('\\', $parts);
                $filedir .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
            }
            $namespace = $modules[$module]['name'] . '\\Model' . $dirname;
            // generate template
            $template = File::read(__DIR__ . '/../Template/Model.template.txt');
            $template = str_replace([
                '{classname}',
                '{namespace}',
                '{module}',
            ], [
                $classname,
                $namespace,
                $module,
            ], $template);
            if (!file_exists($filedir)) {
                File::mkdir($filedir, 0777, ['skip_realpath' => true]);
            }
            File::write($filedir . DIRECTORY_SEPARATOR . $classname . '.php', $template, 0777);
            break;
        default:
            throw new Exception('Unknown type, available are ' . implode(', ', $types));
    }
    // error label
    error:
        // hint
        if (!empty($result['hint'])) {
            echo "\n" . Cmd::colorString(implode("\n", $result['hint']), null, null, false) . "\n\n";
        }
    // if we did not succeed
    if (!empty($result['error'])) {
        echo "\n" . Cmd::colorString(implode("\n", $result['error']), 'red', null, true) . "\n\n";
        exit;
    }
} catch (Exception $e) {
    echo "\n" . Cmd::colorString($e->getMessage(), 'red', null, true) . "\n\n" . $e->getTraceAsString() . "\n\n";
    exit;
}

// success message
$seconds = Format::timeSeconds(microtime(true) - Application::get('application.system.request_time'));
echo "\nOperation \"$type\" with mode \"$mode\" completed in {$seconds} seconds!\n\n";
