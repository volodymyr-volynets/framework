<?php
/**
 * System manager, called from make commands
 */

// command line parameters
$type = $argv[1];
$mode = $argv[2];
$verbose = $argv[3] ?? false;

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
application::run(['__run_only_bootstrap' => 1]);

// increase in memory and unlimited execution time
ini_set('memory_limit', '2048M');
set_time_limit(0);

// confirmation whether to run the script
if (!helper_cmd::confirm("Conitune operation \"$type\" with mode \"$mode\"?")) exit;

// define result variable to keep scripts messages
$result = [
	'success' => false,
	'error' => [],
	'hint' => []
];

// wrapping everything into try-catch block for system exceptions
try {
	// running proper class
	switch ($type) {
		// deployment, mode: production, development
		case 'deployment':
			$result = system_deployments::deploy(['mode' => $mode]);
			break;
		// migration - code, mode: test, commit, drop
		case 'migration_code':
			// get settings for default db_link
			$settings = numbers_backend_db_class_schemas::get_settings([
				'db_link' => 'default'
			]);
			if ($settings['success']) {
				// process models
				$code_result = numbers_backend_db_class_schemas::process_code_models([
					'db_link' => 'default',
					'db_schema_owner' => $settings['db_schema_owner'],
					'skip_db_object' => true
				]);
				//print_r2($code_result);
				$result['hint'][] = "   -> Code objects:";
				foreach ($code_result['count']['default'] as $k2 => $v2) {
					$result['hint'][] = "       * {$k2}: $v2";
				}
				if (!$code_result['success']) {
					$result['error'] = array_merge($result['error'], $code_result['error']);
					goto error;
				}
				// process migrations
				$migration_result = numbers_backend_db_class_migration_processor::process_code_migrations([
					'db_link' => 'default',
					'mode' => $mode,
					'skip_db_object' => true
				]);
				//print_r2($migration_result);
				$result['hint'][] = "   -> Migration objects:";
				foreach ($migration_result['count']['default'] as $k2 => $v2) {
					$result['hint'][] = "       * {$k2}: $v2";
				}
				$result['hint'][] = "   -> Rollback objects:";
				foreach ($migration_result['rollback_count']['default'] as $k2 => $v2) {
					$result['hint'][] = "       * {$k2}: $v2";
				}
				if (!$migration_result['success']) {
					$result['error'] = array_merge($result['error'], $migration_result['error']);
					goto error;
				}
				// drop existing code migrations
				if ($mode == 'drop') {
					$drop_result = numbers_backend_db_class_migration_processor::drop_code_migrations(['db_link' => 'default']);
					$result['hint'][] = "   -> Migrations dropped: {$drop_result['count']};";
				} else {
					// compare objects
					$compare_result = numbers_backend_db_class_schemas::compare_two_set_of_objects($code_result['objects']['default'] ?? [], $migration_result['objects']['default'] ?? [], [
						'type' => 'migration',
						'db_link' => 'default'
					]);
					$result['hint'][] = "   -> Schema changes: {$compare_result['count']};";
					// building hint
					if (!empty($verbose)) {
						$result['hint'] = array_merge($result['hint'], $compare_result['hint']);
					}
					// make schema changes
					if ($compare_result['count'] > 0 && $mode == 'commit') {
						$generate_migration_result = numbers_backend_db_class_migration_processor::generate_migration('default', $compare_result, []);
						$result['hint'][] = "   -> Migrations created: {$generate_migration_result['count']};";
						$result['hint'][] = "       * name: {$generate_migration_result['migration_name']};";
					}
				}
			}
			break;
		// migration - db, mode: test, commit, rollback
		case 'migration_db':
			$migration_db_rollback_name = null;
			if ($mode == 'rollback') {
reask_for_migration:
				$migration_db_rollback_name = helper_cmd::ask('Enter migration name: ');
				if (empty($migration_db_rollback_name)) goto reask_for_migration;
			}
			// get settings for default db_link
			$settings = numbers_backend_db_class_schemas::get_settings([
				'db_link' => 'default'
			]);
			if ($settings['success']) {
				// load all migrations from the code
				$migration_result = numbers_backend_db_class_migration_processor::load_code_migrations([
					'db_link' => 'default',
					'load_migration_objects' => true
				]);
				if ($migration_result['count'] == 0) {
					$result['error'][] = 'Migrations not found!';
					goto error;
				}
				// validate entered rollback migration
				if ($mode == 'rollback') {
					if (empty($migration_result['data'][$migration_db_rollback_name])) {
						$result['error'][] = "Migration {$migration_db_rollback_name} not found in the code!";
						goto error;
					}
				}
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
					// fetch last migration name and count
					$db_last_migration_name = null;
					$db_migration_count = 0;
					$db_migrations = [];
					$migration_model = new numbers_backend_db_class_model_migrations();
					if ($migration_model->db_present()) {
						$temp = $migration_model->get(['where' => [
								'sm_migration_db_link' => 'default',
								'sm_migration_type' => 'migration',
								'sm_migration_action' => 'up',
								'sm_migration_rolled_back' => 0
							],
							'columns' => [
								'sm_migration_name'
							],
							'pk' => [
								'sm_migration_name'
							],
							'orderby' => [
								'sm_migration_name' => SORT_DESC
							]
						]);
						if (!empty($temp)) {
							$db_last_migration_name = key($temp);
							$db_migration_count = count($temp);
							$db_migrations = $temp;
						}
					}
					// validate if rollback migration is in database
					$new_migrations = [];
					if ($mode == 'rollback') {
						if (empty($db_migrations[$migration_db_rollback_name])) {
							$result['hint'][] = "   -> Migration {$migration_db_rollback_name} not found in database!";
							continue;
						}
						// find all migrations to roll down
						foreach ($db_migrations as $k2 => $v2) {
							$new_migrations[$k2] = $migration_result['data'][$k2];
							if ($k2 == $migration_db_rollback_name) break;
						}
					} else { // test and commit modes
						// find if we have new migrations
						$found = false;
						if (empty($db_last_migration_name)) $found = true;
						$new_migrations = [];
						foreach ($migration_result['data'] as $k2 => $v2) {
							if ($found) {
								$new_migrations[$k2] = $v2;
							} else {
								if ($k2 == $db_last_migration_name) $found = true;
							}
						}
					}
					$new_migration_count = count($new_migrations);
					// legend
					$result['hint'][] = "   -> Code migration(s): {$migration_result['count']}";
					$result['hint'][] = "       * last migration: {$migration_result['last_migration_name']}";
					$result['hint'][] = "   -> Db migration(s): {$db_migration_count}";
					$result['hint'][] = "       * last migration: {$db_last_migration_name}";
					$result['hint'][] = "   -> Apply migration(s): {$new_migration_count}";
					foreach ($new_migrations as $k2 => $v2) {
						$result['hint'][] = "       * {$k2}";
					}
					// if we have new migrations
					if (($mode == 'commit' || $mode == 'rollback') && $new_migration_count > 0) {
						$action = ($mode == 'commit') ? 'up' : 'down';
						$result['hint'][] = "   -> Applying migration(s):";
						$permissions = [];
						// apply migrations one by one
						foreach ($new_migrations as $k2 => $v2) {
							// execute migration in commit mode
							$execute_result = $v2['object']->execute($action);
							if (!$execute_result['success']) {
								$result['hint'][] = "       * {$k2}: {$action} " . helper_cmd::color_string('FAILED', 'red', null, true);
								$result['error'] = array_merge($result['error'], $execute_result['error']);
								goto error;
							}
							$result['hint'][] = "       * {$k2}: {$action} " . helper_cmd::color_string('OK', 'green');
							// assemble permissions
							$permissions = array_merge_hard($permissions, $execute_result['permissions']);
						}
						// set permissions
						if (!empty($permissions)) {
							$permission_result = numbers_backend_db_class_schemas::set_permissions('default', $settings['db_query_owner'], $permissions, ['database' => $v]);
							if (!$permission_result['success']) {
								$result['error'] = array_merge($result['error'], $permission_result['error']);
								goto error;
							}
							$result['hint'][] = "   -> Set permissions: {$permission_result['count']};";
							// building hint
							if (!empty($verbose)) {
								$result['hint'] = array_merge($result['hint'], $permission_result['legend']);
							}
						}
					}
				}
				$result['success'] = true;
			} else {
				$result = $settings;
			}
			break;
		// direct schema changes - mode: test, commit, drop
		case 'schema':
			// get settings for default db_link
			$settings = numbers_backend_db_class_schemas::get_settings([
				'db_link' => 'default'
			]);
			if ($settings['success']) {
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
					if (!empty($db_result['count']['default'])) {
						foreach ($db_result['count']['default'] as $k2 => $v2) {
							$result['hint'][] = "       * {$k2}: $v2";
						}
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
					if ($compare_result['count'] > 0 && ($mode == 'drop' || $mode == 'commit')) {
						$sql_result = numbers_backend_db_class_schemas::generate_sql_from_diff_and_execute('default', $compare_result['up'], [
							'mode' => $mode,
							'execute' => true,
							'legend' => $compare_result['legend']['up']
						]);
						if (!$sql_result['success']) {
							$result['error'] = array_merge($result['error'], $db_result['error']);
							$db_object->rollback();
							goto error;
						}
						$result['hint'][] = "   -> SQL changes: {$sql_result['count']};";
						// set permissions to allow access for query user
						if ($mode == 'commit' && !empty($code_result['permissions']['default'])) {
							$permission_result = numbers_backend_db_class_schemas::set_permissions('default', $settings['db_query_owner'], $code_result['permissions']['default'], ['database' => $v]);
							if (!$permission_result['success']) {
								$result['error'] = array_merge($result['error'], $permission_result['error']);
								goto error;
							}
							$result['hint'][] = "   -> Set permissions: {$permission_result['count']};";
							// building hint
							if (!empty($verbose)) {
								$result['hint'] = array_merge($result['hint'], $permission_result['legend']);
							}
						}
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
		// dependencies - mode: test, commit
		case 'dependency':
		default:
			$result = system_dependencies::process_deps_all(['mode' => $mode]);
	}
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
$seconds = format::time_seconds(microtime(true) - application::get('application.system.request_time'));
echo "\nOperation \"$type\" with mode \"$mode\" completed in {$seconds} seconds!\n\n";
