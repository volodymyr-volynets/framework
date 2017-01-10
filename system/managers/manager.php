<?php

// benchmark
$benchmark = microtime(true);

// parameters
$type = $argv[1];
$mode = $argv[2];
$verbose = $argv[3] ?? false;

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

// confirmation
if (!helper_cmd::confirm("Conitune operation \"$type\" with mode \"$mode\"?")) exit;

// wrapping everything into try-catch block for system exceptions
try {
	// running proper class
	switch ($type) {
		case 'deploy':
			$result = system_deployments::deploy(['mode' => $mode]);
			break;
		// generate migration
		case 'migration_generator':
			$result = [
				'success' => false,
				'error' => [],
				'hint' => []
			];
			// process models
			$code_result = numbers_backend_db_class_schemas::process_code_models(['db_link' => 'default']);
			if (!$code_result['success']) {
				$result['error'] = array_merge($result['error'], $code_result['error']);
				goto error;
			}
			
			print_r2($code_result);
			
			break;
		// execute migration
		case 'migration':
			// todo
			break;
		// direct schema changes
		case 'schema':
			// get settings for default db_link
			$settings = numbers_backend_db_class_schemas::get_settings(['db_link' => 'default']);
			if ($settings['success']) {
				$result = [
					'success' => false,
					'error' => [],
					'hint' => []
				];
				// go through each database
				foreach ($settings['db_list'] as $v) {
					$schema_temp = $settings['db_settings'];
					$schema_temp['dbname'] = $v;
					$db_object = new db('default', $schema_temp['submodule']);
					$db_status = $db_object->connect($schema_temp);
					if (!($db_status['success'] && $db_status['status'])) {
						Throw new Exception('Unable to open database connection!');
					}
					$result['hint'][] = " * Connected to {$v} database:";
					// start transaction
					$db_object->begin();
					// process models
					$code_result = numbers_backend_db_class_schemas::process_code_models([
						'db_link' => 'default',
						'db_schema_owner' => $settings['db_schema_owner']
					]);
					//print_r2($code_result);
					$result['hint'][] = "   -> Code objects:";
					foreach ($code_result['count']['default'] as $k2 => $v2) {
						$result['hint'][] = "       * {$k2}: $v2";
					}
					if (!$code_result['success']) {
						$result['error'] = array_merge($result['error'], $code_result['error']);
						$db_object->rollback();
						goto error;
					}
					$db_result = numbers_backend_db_class_schemas::process_db_schema(['db_link' => 'default']);
					if (!$db_result['success']) {
						$result['error'] = array_merge($result['error'], $db_result['error']);
						$db_object->rollback();
						goto error;
					}
					//print_r2($db_result);
					$result['hint'][] = "   -> Db objects:";
					foreach ($db_result['count']['default'] as $k2 => $v2) {
						$result['hint'][] = "       * {$k2}: $v2";
					}
					// if dropping we have empty objects from the code
					if ($mode == 'drop') {
						$code_result['objects']['default'] = [];
					}
					// compare objects
					$compare_result = numbers_backend_db_class_schemas::compare_two_set_of_objects($code_result['objects']['default'] ?? [], $db_result['objects']['default'] ?? [], [
						'type' => 'schema',
						'db_link' => 'default'
					]);
					$result['hint'][] = "   -> Schema changes: {$compare_result['count']};";
					// building hint
					if (!empty($verbose)) {
						$result['hint'] = array_merge($result['hint'], $compare_result['hint']);
					}
					// make schema changes
					if ($mode == 'drop' || $mode == 'commit') {
						$sql_result = numbers_backend_db_class_schemas::generate_sql_from_diff('default', $compare_result['data'], [
							'mode' => $mode,
							'execute' => true
						]);
						if (!$sql_result['success']) {
							$result['error'] = array_merge($result['error'], $db_result['error']);
							$db_object->rollback();
							goto error;
						}
						$result['hint'][] = "   -> SQL changes: {$sql_result['count']};";
					}
					// import data
					// 
					// 
					// 
					// 
					// todo   
					// 
					// 
					// 
					// 
					// commit
					$db_object->commit();
				}
				$result['success'] = true;
			} else {
				$result = $settings;
			}
			// todo: we need to reset all caches
			break;
		case 'dependency':
		default:
			$result = system_dependencies::process_deps_all(['mode' => $mode]);
	}

error:
	// hint
	if (!empty($result['hint'])) {
		echo implode("\n", $result['hint']) . "\n";
	}
	// if we did not succeed
	if (!$result['success']) {
		echo implode("\n", $result['error']) . "\n";
		exit;
	}
} catch(Exception $e) {
	echo $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n";
}

// benchmark
$seconds = format::time_seconds(microtime(true) - $benchmark);

// if we succedded
echo "\nOperation \"$type\" with mode \"$mode\" completed in {$seconds} seconds!\n\n";
