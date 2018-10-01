<?php
/**
 * System manager, called from make commands
 */

// command line parameters
$type = $argv[1];
$mode = $argv[2];
$verbose = $argv[3] ?? false;
$skip_confirmation = $argv[4] ?? false;

// must change working directory to public_html
chdir('public_html');

// autoloading composer first
if (file_exists('../libraries/vendor/autoload.php')) {
	require('../libraries/vendor/autoload.php');
}

// running application
require('../libraries/vendor/Numbers/Framework/Application.php');
Application::run(['__run_only_bootstrap' => 1]);

// disable debug
\Debug::$debug = false;

// increase in memory and unlimited execution time
ini_set('memory_limit', '2048M');
set_time_limit(0);

// confirmation whether to run the script
if (empty($skip_confirmation) || $skip_confirmation == 2) {
	if (!\Helper\Cmd::confirm("Conitune operation \"$type\" with mode \"$mode\"?")) exit;
}

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
			$result = \System\Deployments::deploy(['mode' => $mode]);
			break;
		// migration - code, mode: test, commit, drop
		case 'migration_code':
			// get settings for default db_link
			$settings = \Numbers\Backend\Db\Common\Schemas::getSettings([
				'db_link' => 'default'
			]);
			if ($settings['success']) {
				$db_object = new \Db('default', $settings['db_settings']['submodule']);
				// process models
				$code_result = \Numbers\Backend\Db\Common\Schemas::processCodeModels([
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
				$migration_result = \Numbers\Backend\Db\Common\Migration\Processor::processCodeMigrations([
					'db_link' => 'default',
					'mode' => $mode,
					'skip_db_object' => true
				]);
				//print_r2($migration_result);
				$result['hint'][] = "   -> Migration objects:";
				if (!empty($migration_result['count']['default'])) {
					foreach ($migration_result['count']['default'] as $k2 => $v2) {
						$result['hint'][] = "       * {$k2}: $v2";
					}
				}
				$result['hint'][] = "   -> Rollback objects:";
				if (!empty($migration_result['rollback_count']['default'])) {
					foreach ($migration_result['rollback_count']['default'] as $k2 => $v2) {
						$result['hint'][] = "       * {$k2}: $v2";
					}
				}
				if (!$migration_result['success']) {
					$result['error'] = array_merge($result['error'], $migration_result['error']);
					goto error;
				}
				// drop existing code migrations
				if ($mode == 'drop') {
					$drop_result = \Numbers\Backend\Db\Common\Migration\Processor::dropCodeMigrations(['db_link' => 'default']);
					$result['hint'][] = "   -> Migrations dropped: {$drop_result['count']};";
				} else {
					// compare objects
					$compare_result = \Numbers\Backend\Db\Common\Schemas::compareTwoSetsOfObjects($code_result['objects']['default'] ?? [], $migration_result['objects']['default'] ?? [], [
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
						$generate_migration_result = \Numbers\Backend\Db\Common\Migration\Processor::generateMigration('default', $compare_result, []);
						$result['hint'][] = "   -> Migrations created: {$generate_migration_result['count']};";
						$result['hint'][] = "       * name: {$generate_migration_result['migration_name']};";
					}
				}
			}
			break;
		// migration - db, mode: test, commit, rollback
		case 'migration_db':
			// check settings to see if we can run this command
			$temp = \Application::get('application.structure.db_migration');
			if (empty($temp)) {
				Throw new Exception('Migrations are disabled, you must use schema commands!');
			}
			$migration_db_rollback_name = null;
			if ($mode == 'rollback') {
reask_for_migration:
				$migration_db_rollback_name = \Helper\Cmd::ask('Enter migration name: ');
				if (empty($migration_db_rollback_name)) goto reask_for_migration;
			}
			// get settings for default db_link
			$settings = \Numbers\Backend\Db\Common\Schemas::getSettings([
				'db_link' => 'default'
			]);
			if ($settings['success']) {
				// load all migrations from the code
				\Helper\Cmd::message('Loading migrations...', 'blue');
				$migration_result = \Numbers\Backend\Db\Common\Migration\Processor::loadCodeMigrations([
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
					\Helper\Cmd::message('Processing database: ' . $v, 'blue');
					$schema_temp = $settings['db_settings'];
					if (isset($schema_temp['dbname']) && $schema_temp['dbname'] != $v) {
						$schema_temp['__original_dbname'] = $schema_temp['dbname'];
					}
					$schema_temp['dbname'] = $v;
					$db_object = new \Db('default', $schema_temp['submodule']);
					$db_status = $db_object->connect($schema_temp);
					if (!($db_status['success'] && $db_status['status'])) {
						Throw new \Exception('Unable to open database connection!');
					}
					$result['hint'][] = " * Connected to {$v} database:";
					// fetch last migration name and count
					$db_last_migration_name = null;
					$db_migration_count = 0;
					$db_migrations = [];
					$migration_model = new \Numbers\Backend\Db\Common\Model\Migrations();
					if ($migration_model->dbPresent()) {
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
						$counter = 0;
						foreach ($new_migrations as $k2 => $v2) {
							// execute migration in commit mode
							$execute_result = $v2['object']->execute($action);
							if (!$execute_result['success']) {
								$result['hint'][] = "       * {$k2}: {$action} " . \Helper\Cmd::colorString('FAILED', 'red', null, true);
								array_merge3($result['error'], $execute_result['error']);
								goto error;
							}
							$result['hint'][] = "       * {$k2}: {$action} " . \Helper\Cmd::colorString('OK', 'green');
							// assemble permissions
							$permissions = array_merge_hard($permissions, $execute_result['permissions']);
							// progress bar
							$counter++;
							\Helper\Cmd::progressBar(25 + round($counter / count($new_migrations) * 100 / 2, 0), 100, 'Executing migrations');
						}
						// cleanup permissions
						foreach ($permissions as $k2 => $v2) {
							foreach ($v2 as $k3 => $v4) {
								if (empty($v4)) unset($permissions[$k2][$k3]);
							}
							if (empty($permissions[$k2])) unset($permissions[$k2]);
						}
						// set permissions
						// todo: maybe reset permissions for old migrations,
						// useful when we make changes to DDL classes
						\Helper\Cmd::progressBar(75, 100, 'Setting permissions');
						if (!empty($permissions)) {
							$permission_result = \Numbers\Backend\Db\Common\Schemas::setPermissions(
								'default',
								$settings['db_query_owner'],
								$permissions,
								[
									'database' => $v,
									'db_query_password' => $settings['db_query_password']
								]
							);
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
					if ($mode == 'commit') {
						\Helper\Cmd::progressBar(90, 100, 'Importing preset data');
						$code_result = \Numbers\Backend\Db\Common\Schemas::processCodeModels([
							'db_link' => 'default',
							'db_schema_owner' => $settings['db_schema_owner'],
							'skip_db_object' => true
						]);
						if (!empty($code_result['data']['\Object\Import'])) {
							$import_data_result = \Numbers\Backend\Db\Common\Schemas::importData('default', $code_result['data'], []);
							if (!$import_data_result['success']) {
								$result['error'] = array_merge($result['error'], $import_data_result['error']);
								goto error;
							}
							$result['hint'][] = "   -> Import data: {$import_data_result['count']};";
							// building hint
							if (!empty($verbose)) {
								$result['hint'] = array_merge($result['hint'], $import_data_result['legend']);
							}
						}
					}
					\Helper\Cmd::progressBar(100, 100, 'Migration completed');
				}
				$result['success'] = true;
			} else {
				$result = $settings;
			}
			break;
		// direct schema changes - mode: test, commit, drop
		case 'schema':
			// check settings to see if we can run this command
			$temp = \Application::get('application.structure.db_migration');
			if (!empty($temp) && $mode == 'commit') {
				Throw new Exception('Direct schema changes are disabled, you must use migration commands!');
			}
			// get settings for default db_link
			$settings = \Numbers\Backend\Db\Common\Schemas::getSettings([
				'db_link' => 'default'
			]);
			if ($settings['success']) {
				// go through each database
				foreach ($settings['db_list'] as $v) {
					\Helper\Cmd::message('Processing database: ' . $v, 'blue');
					$schema_temp = $settings['db_settings'];
					// for multi database we need to store original database name
					if ($schema_temp['dbname'] != $v) {
						$schema_temp['__original_dbname'] = $schema_temp['dbname'];
					}
					$schema_temp['dbname'] = $v;
					$db_object = new \Db('default', $schema_temp['submodule']);
					$db_status = $db_object->connect($schema_temp);
					if (!($db_status['success'] && $db_status['status'])) {
						Throw new Exception('Unable to open database connection!');
					}
					$result['hint'][] = " * Connected to {$v} database:";
					// start transaction
					$db_object->begin();
					// process models
					\Helper\Cmd::progressBar(0, 100, 'Loading Code objects');
					$code_result = \Numbers\Backend\Db\Common\Schemas::processCodeModels([
						'db_link' => 'default',
						'db_schema_owner' => $settings['db_schema_owner']
					]);
					if (!$code_result['success']) {
						$result['error'] = array_merge($result['error'], $code_result['error']);
						$db_object->rollback();
						goto error;
					}
					$result['hint'][] = "   -> Code objects:";
					foreach ($code_result['count']['default'] as $k2 => $v2) {
						$result['hint'][] = "       * {$k2}: $v2";
					}
					\Helper\Cmd::progressBar(25, 100, 'Loading DB objects');
					$db_result = \Numbers\Backend\Db\Common\Schemas::processDbSchema(['db_link' => 'default']);
					if (!$db_result['success']) {
						$result['error'] = array_merge($result['error'], $db_result['error']);
						$db_object->rollback();
						goto error;
					}
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
					\Helper\Cmd::progressBar(50, 100, 'Comparing objects');
					$compare_result = \Numbers\Backend\Db\Common\Schemas::compareTwoSetsOfObjects($code_result['objects']['default'] ?? [], $db_result['objects']['default'] ?? [], [
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
						\Helper\Cmd::progressBar(50, 100, 'Executing SQL changes');
						$sql_result = \Numbers\Backend\Db\Common\Schemas::generateSqlFromDiffAndExecute('default', $compare_result['up'], [
							'mode' => $mode,
							'execute' => true,
							'legend' => $compare_result['legend']['up']
						]);
						if (!$sql_result['success']) {
							$result['error'] = array_merge($result['error'], $sql_result['error']);
							$db_object->rollback();
							goto error;
						}
						$result['hint'][] = "   -> SQL changes: {$sql_result['count']};";
					}
					// set permissions to allow access for query user
					if ($mode == 'commit' && !empty($code_result['permissions']['default'])) {
						\Helper\Cmd::progressBar(75, 100, 'Setting permissions');
						$permission_result = \Numbers\Backend\Db\Common\Schemas::setPermissions(
							'default',
							$settings['db_query_owner'],
							$code_result['permissions']['default'],
							[
								'database' => $v,
								'db_query_password' => $settings['db_query_password']
							]
						);
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
					// import data
					if ($mode == 'commit' && !empty($code_result['data']['\Object\Import'])) {
						\Helper\Cmd::progressBar(90, 100, 'Importing preset data');
						$import_data_result = \Numbers\Backend\Db\Common\Schemas::importData('default', $code_result['data'], []);
						if (!$import_data_result['success']) {
							$result['error'] = array_merge($result['error'], $import_data_result['error']);
							goto error;
						}
						$result['hint'][] = "   -> Import data: {$import_data_result['count']};";
						// building hint
						if (!empty($verbose)) {
							$result['hint'] = array_merge($result['hint'], $import_data_result['legend']);
						}
					}
					// commit
					\Helper\Cmd::progressBar(100, 100, 'Commit changes');
					$db_object->commit();
				}
				$result['success'] = true;
			} else {
				$result = $settings;
			}
			// todo: we need to reset all caches
			break;
		// caches - mode: drop
		case 'cache':
			if ($mode == 'drop') {
reset_all_caches:
				// initialize caches
				$cache = \Application::get('cache');
				if (!empty($cache)) {
					foreach ($cache as $cache_link => $cache_settings) {
						if (empty($cache_settings['submodule']) || empty($cache_settings['autoconnect'])) continue;
						$cache_result = \Cache::connectToServers($cache_link, $cache_settings);
						if (!$cache_result['success']) {
							Throw new Exception(implode(', ', $cache_result['error']));
						}
					}
				}
				// reset opened caches
				$cache = \Factory::get(['cache']);
				if (!empty($cache)) {
					foreach ($cache as $k => $v) {
						$object = $v['object'];
						$object->gc(2);
					}
				}
				$result['success'] = true;
			}
			break;
		// import
		case 'data_import':
			$class = \Helper\Cmd::ask('Enter model class name: ', ['mandatory' => true]);
			// get settings for default db_link
			$settings = \Numbers\Backend\Db\Common\Schemas::getSettings([
				'db_link' => 'default'
			]);
			if ($settings['success']) {
				// go through each database
				foreach ($settings['db_list'] as $v) {
					\Helper\Cmd::message('Processing database: ' . $v, 'blue');
					$schema_temp = $settings['db_settings'];
					// for multi database we need to store original database name
					if ($schema_temp['dbname'] != $v) {
						$schema_temp['__original_dbname'] = $schema_temp['dbname'];
					}
					$schema_temp['dbname'] = $v;
					$db_object = new \Db('default', $schema_temp['submodule']);
					$db_status = $db_object->connect($schema_temp);
					if (!($db_status['success'] && $db_status['status'])) {
						Throw new Exception('Unable to open database connection!');
					}
					// start transaction
					$db_object->begin();
					$model = new $class();
					if (method_exists($model, 'activate')) {
						$temp = $model->activate();
					} else {
						$temp = $model->process();
					}
					if (!$temp['success']) {
						$result['error'] = array_merge($result['error'], $temp['error']);
						goto error;
					}
					\Helper\Cmd::message('Completed!', 'green');
					// commit
					$db_object->commit();
				}
				$result['success'] = true;
			} else {
				$result = $settings;
			}
			break;
		// dependencies - mode: test, commit
		case 'dependency':
		default:
			$result = \System\Dependencies::processDepsAll(['mode' => $mode, 'skip_confirmation' => $skip_confirmation, 'show_warnings' => true]);
			if ($result['success']) {
				echo "\n" . \Helper\Cmd::colorString('Dependency is OK', 'green', null, true) . "\n\n";
			}
	}
// error label
error:
	// hint
	if (!empty($result['hint'])) {
		echo "\n" . \Helper\Cmd::colorString(implode("\n", $result['hint']), null, null, false) . "\n\n";
	}
	// if we did not succeed
	if (!empty($result['error'])) {
		echo "\n" . \Helper\Cmd::colorString(implode("\n", $result['error']), 'red', null, true) . "\n\n";
		exit;
	}
} catch(Exception $e) {
	echo "\n" . \Helper\Cmd::colorString($e->getMessage(), 'red', null, true) . "\n\n" . $e->getTraceAsString() . "\n\n";
	exit;
}

// success message
$seconds = Format::timeSeconds(microtime(true) - Application::get('application.system.request_time'));
echo "\nOperation \"$type\" with mode \"$mode\" completed in {$seconds} seconds!\n\n";
