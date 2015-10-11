<?php

// parameters
$environment = !empty($argv[0]) ? $argv[0] : 'production';
$flag_full_run = !empty($argv[1]) ? true : false;

// loading application class & functions
require('libraries/vendor/numbers/framework/application.php');
require('libraries/vendor/numbers/framework/functions.php');

// processing main dependency file
$main_dep_filename = 'app/application.ini';
if (!file_exists($main_dep_filename)) {
	echo "Main dep. file not found!\n";
	exit;
}
$data = application::ini($main_dep_filename, $environment);
$data = isset($data['dep']) ? $data['dep'] : array();
$data['composer'] = isset($data['composer']) ? $data['composer'] : array();
$data['submodule'] = isset($data['submodule']) ? $data['submodule'] : array();
$data['apache'] = isset($data['apache']) ? $data['apache'] : array();
$data['php'] = isset($data['php']) ? $data['php'] : array();

// we have small chicken and egg problem with composer
$composer_data = json_decode(file_get_contents('libraries/composer.json'), true);
$composer_dirs = array();

/**
 * Special function for data processing
 *
 * @param type $data
 * @param type $composer_data
 * @param type $composer_dirs
 */
function process_dep_array($data, & $composer_data, & $composer_dirs) {
	if (empty($data)) return;
	foreach ($data as $k => $v) {
		foreach ($v as $k2 => $v2) {
			if (!is_array($v2)) {
				$composer_data[$k . '/' . $k2] = $v2;
				$composer_dirs[$k . '/' . $k2] = 'libraries/vendor/' . $k . '/' . $k2 . '/';
			} else {
				foreach ($v2 as $k3 => $v3) {
					if (!is_array($v3)) {
						$composer_dirs[$k . '/' . $k2 . '/' . $k3] = 'libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/';
					} else {
						foreach ($v3 as $k4 => $v4) {
							if (!is_array($v4)) {
								$composer_dirs[$k . '/' . $k2 . '/' . $k3 . '/' . $k4] = 'libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/';
							} else {
								foreach ($v4 as $k5 => $v5) {
									if (!is_array($v5)) {
										$composer_dirs[$k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5] = 'libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/';
									} else {
										echo " - maximum depth has been reached!\n";
										exit;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

if (!empty($data['composer']) || !empty($data['submodules'])) {
	$composer_data['required'] = array();
	process_dep_array($data['composer'], $composer_data['required'], $composer_dirs);
	process_dep_array($data['submodule'], $composer_data['required'], $composer_dirs);

	// updating composer.json file
	if (!$flag_full_run) {
		file_put_contents('libraries/composer.json', json_encode($composer_data, JSON_PRETTY_PRINT));
		echo " - Updated composer.json file!\n";
	}
}

if (!$flag_full_run) exit;

// if we are doing full run we have to process all dependency.ini files from everywhere
echo " - Processing dependencies:!\n";
$temp = array();
for ($i = 0; $i < 3; $i++) {
	foreach ($composer_dirs as $k => $v) {
		if (isset($temp[$k])) {
			continue;
		} else {
			$temp[$k] = 1;
		}
		echo "   -> processing {$v}module.ini\n";
		if (file_exists($v . 'module.ini')) {
			$sub_data = application::ini($v . 'module.ini', $environment);
			$sub_data = isset($sub_data['dep']) ? $sub_data['dep'] : array();
			if (!empty($sub_data['composer'])) {
				process_dep_array($sub_data['composer'], $composer_data['required'], $composer_dirs);
			}
			if (!empty($sub_data['submodule'])) {
				process_dep_array($sub_data['submodule'], $composer_data['required'], $composer_dirs);
			}
			if (!empty($sub_data['apache'])) {
				$data['apache'] = array_merge_recursive($data['apache'], $sub_data['apache']);
			}
			if (!empty($sub_data['php'])) {
				$data['php'] = array_merge_recursive($data['php'], $sub_data['php']);
			}
		} else {
			echo "     |-> file not found!\n";
		}
	}
}
	
// writing composer file
file_put_contents('libraries/composer.json', json_encode($composer_data, JSON_PRETTY_PRINT));
echo " - Updated composer.json file!\n";

// displaying composer deps
echo "\n ---Composer Deps.---\n";
foreach ($composer_data['required'] as $k => $v) {
	echo "   -> $k = $v\n";
}

// displaying php extensions
$php_extensions = get_loaded_extensions();
echo "\n ---PHP Extensions---\n";
if (!empty($data['php']['extension'])) {
	foreach ($data['php']['extension'] as $k => $v) {
		if (in_array($k, $php_extensions)) {
			echo "   -> $k : loaded\n";
		} else {
			echo "   -> $k : not loaded!!!\n";
		}
	}
}