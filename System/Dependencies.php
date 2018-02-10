<?php

namespace System;
class Dependencies {

	/**
	 * Process dependencies
	 *
	 * @param array $options
	 *		mode
	 * @return array
	 */
	public static function processDepsAll(array $options = []) : array {
		$result = [
			'success' => false,
			'error' => [],
			'data' => []
		];
		do {
			$options['mode'] = $options['mode'] ?? 'test';
			// processing main dependency file
			$main_dep_filename = 'Config/application.ini';
			if (!file_exists($main_dep_filename)) {
				$result['error'][] = "Main dep. file not found!";
				break;
			}
			// some array arrangements
			$data = \System\Config::ini($main_dep_filename, 'dependencies');
			$data = $data['dep'] ?? [];
			$data['composer'] = $data['composer'] ?? [];
			$data['submodule'] = $data['submodule'] ?? [];
			$data['submodule_dirs'] = [];
			$data['apache'] = $data['apache'] ?? [];
			$data['php'] = $data['php'] ?? [];
			$data['model'] = $data['model'] ?? [];
			$data['__model_dependencies'] = [];
			$data['model_import'] = [];
			$data['override'] = $data['override'] ?? [];
			$data['acl'] = $data['acl'] ?? [];
			$data['media'] = $data['media'] ?? [];
			$data['model_processed'] = [];
			$data['unit_tests'] = [];
			$data['form'] = $data['form'] ?? [];
			$data['__submodule_dependencies'] = [];
			$dummy = [];
			// we have small chicken and egg problem with composer
			$composer_data = [];
			$composer_dirs = [];
			$composer_dirs[] = 'Config/';
			if (file_exists('../libraries/composer.json')) {
				$composer_data = json_decode(file_get_contents('../libraries/composer.json'), true);
			}
			// if we have composer or submodules from main dep file
			if (!empty($data['composer']) || !empty($data['submodules'])) {
				$composer_data['require'] = [];
				if (!empty($data['composer'])) {
					self::processDepsArray($data['composer'], $composer_data['require'], $composer_dirs, 'dummy', $dummy);
				}
				if (!empty($data['submodule'])) {
					self::processDepsArray($data['submodule'], $composer_data['require'], $composer_dirs, 'dummy', $dummy);
				}
			}
			// processing submodules
			$mutex = [];
			$__any = [];
			if (!empty($composer_dirs)) {
				for ($i = 0; $i < 12; $i++) { // twelve runs to get all dependencies
					foreach ($composer_dirs as $k => $v) {
						if (isset($mutex[$k])) {
							continue;
						} else {
							$mutex[$k] = 1;
						}
						if (file_exists($v . 'module.ini')) {
							$data['submodule_dirs'][$v] = $v;
							$sub_data = \System\Config::ini($v . 'module.ini', 'dependencies');
							$sub_data = isset($sub_data['dep']) ? $sub_data['dep'] : [];
							if (!empty($sub_data['composer'])) {
								self::processDepsArray($sub_data['composer'], $composer_data['require'], $composer_dirs, $k, $dummy);
								$data['composer'] = array_merge_hard($data['composer'], $sub_data['composer']);
							}
							if (!empty($sub_data['submodule'])) {
								self::processDepsArray($sub_data['submodule'], $composer_data['require'], $composer_dirs, $k, $data['__submodule_dependencies']);
								$data['submodule'] = array_merge_hard($data['submodule'], $sub_data['submodule']);
							}
							if (!empty($sub_data['apache'])) {
								$data['apache'] = array_merge_hard($data['apache'], $sub_data['apache']);
							}
							if (!empty($sub_data['php'])) {
								$data['php'] = array_merge_hard($data['php'], $sub_data['php']);
							}
							if (!empty($sub_data['model'])) {
								$data['model'] = array_merge_hard($data['model'], $sub_data['model']);
								$temp = [];
								array_keys_to_string($sub_data['model'], $temp);
								foreach ($temp as $k0 => $v0) {
									$data['__model_dependencies'][$k][$k0] = $k0;
								}
							}
							if (!empty($sub_data['override'])) {
								$data['override'] = array_merge_hard($data['override'], $sub_data['override']);
							}
							if (!empty($sub_data['acl'])) {
								$data['acl'] = array_merge_hard($data['acl'], $sub_data['acl']);
							}
							if (!empty($sub_data['media'])) {
								foreach ($sub_data['media'] as $k78 => $v78) {
									if (!isset($data['media'][$k78])) $data['media'][$k78] = [];
									foreach ($v78 as $v79) {
										$data['media'][$k78][] = $v79;
									}
								}
							}
							if (!empty($sub_data['form'])) {
								$data['form'] = array_merge_hard($data['form'], $sub_data['form']);
							}
							// processing unit tests
							if (file_exists($v . 'UnitTests')) {
								// we have to reload the module.ini file to get module name
								$sub_data_temp = \System\Config::ini($v . 'module.ini', 'module');
								$data['unit_tests'][$sub_data_temp['module']['name']] = $v . 'UnitTests/';
							}
						} else {
							$keys = explode('/', $k);
							$last = end($keys);
							if ($last == '__any') {
								$temp2 = [];
								foreach ($keys as $v2) {
									if ($v2 != '__any') {
										$temp2[] = $v2;
									}
								}
								$__any[$k] = $temp2;
							} else if ($keys[0] == 'numbers') {
								$result['error'][] = " - Submodule not found in {$v}module.ini";
							}
						}
					}
				}
			}
			// processing any dependencies
			if (!empty($__any)) {
				foreach ($__any as $k => $v) {
					$temp = array_key_get($data['submodule'], $v);
					unset($temp['__any']);
					if (empty($temp)) {
						$result['error'][] = " - Any dependency required $k!";
					}
				}
			}
			// processing composer
			if (empty($options['skip_confirmation'])) {
				if (!empty($composer_data['require'])) {
					foreach ($composer_data['require'] as $k => $v) {
						if (!file_exists('../libraries/vendor/' . $k)) {
							$result['error'][] = " - Composer library \"$k\" is not loaded!";
						}
					}
				}
			}
			// sometimes we need to make sure we have functions available
			$func_per_extension = [
				'pgsql' => 'pg_connect'
			];
			// proceccing php extensions
			if (!empty($data['php']['extension'])) {
				foreach ($data['php']['extension'] as $k => $v) {
					if ((isset($func_per_extension[$k]) && function_exists($func_per_extension[$k]) == false) || !extension_loaded($k)) {
						$result['error'][] = " - PHP extension \"$k\" is not loaded!";
					}
				}
			}
			// processing php ini settings
			if (!empty($data['php']['ini'])) {
				foreach ($data['php']['ini'] as $k => $v) {
					foreach ($v as $k2 => $v2) {
						$temp = ini_get($k . '.' . $k2);
						if (ini_get($k . '.' . $k2) != $v2) {
							$result['error'][] = " - PHP ini setting $k.$k2 is \"$temp\", should be $v2!";
						}
					}
				}
			}
			// processing apache modules
			if (!empty($data['apache']['module'])) {
				if (function_exists('apache_get_modules')) {
					$ext_have = array_map('strtolower', apache_get_modules());
					foreach ($data['apache']['module'] as $k => $v) {
						if (!in_array($k, $ext_have)) {
							$result['error'][] = " - Apache module \"$k\" is not loaded!";
						}
					}
				} else {
					echo \Helper\Cmd::colorString('Make sure following Apache modules are enabled:', 'red') . "\n";
					foreach ($data['apache']['module'] as $k => $v) {
						echo $k . " ";
					}
					echo "\n";
				}
			}
			// processing models
			if (!empty($data['model'])) {
				$data['model_processed'] = $data['model'];
			}
			// need to sort models in order of dependencies
			$imports = [];
			foreach ($data['model_processed'] as $k => $v) {
				// find submodule
				foreach ($data['__model_dependencies'] as $k2 => $v2) {
					if (!empty($v2[$k])) {
						$imports[$k2][$k] = $k;
						break;
					}
				}
			}
			// clean up unused dependencies
			foreach ($data['__submodule_dependencies'] as $k2 => $v2) {
				if (empty($imports[$k2])) {
					$data['__submodule_dependencies'][$k2] = [];
				} else {
					foreach ($v2 as $k3 => $v3) {
						if (empty($imports[$k3])) {
							unset($data['__submodule_dependencies'][$k2][$k3]);
						}
					}
				}
			}
			// we need to go though an array few times to fix dependency issues
			for ($i = 0; $i < 12; $i++) {
				foreach ($imports as $k => $v) {
					if (empty($data['__submodule_dependencies'][$k])) {
						$data['model_import'][$k] = $v;
						unset($imports[$k]);
						// we need to remove file from dependency
						foreach ($data['__submodule_dependencies'] as $k2 => $v2) {
							unset($data['__submodule_dependencies'][$k2][$k]);
						}
					}
				}
			}
			// undependent import object go last
			if (!empty($imports)) {
				foreach ($imports as $k => $v) {
					$data['model_import'][$k] = $v;
				}
			}
			foreach ($data['model_import'] as $k => $v) {
				foreach ($v as $k2 => $v2) {
					$temp = $data['model_processed'][$k2];
					unset($data['model_processed'][$k2]);
					$data['model_processed'][$k2] = $temp;
				}
			}
			if (!empty($data['model_processed']['\Numbers\Backend\Db\Extension\PostgreSQL\PostGIS\Model\Geo\PostGIS'])) {
				echo \Helper\Cmd::colorString('Make sure following Postgres configuration exists:', 'red') . "\n";
				echo 'search_path = \'"$user",public,extensions\'';
				echo "\n";
			}
			unset($data['__submodule_dependencies'], $data['__model_dependencies'], $data['model_import']);
			// handling overrides, cleanup directory first
			\Helper\File::delete('./Overrides/Class', ['only_contents' => true, 'skip_files' => ['.gitkeep']]);
			if (!empty($data['override'])) {
				array_keys_to_string($data['override'], $data['override_processed']);
				$override_classes = [];
				$override_found = false;
				foreach ($data['override_processed'] as $k => $v) {
					if (!isset($override_classes[$v])) {
						$override_classes[$v] = [
							'object' => new \Object\Override\Blank(),
							'found' => false
						];
					}
					$override_class = str_replace('.', '_', $k);
					$override_object = new $override_class();
					$vars = get_object_vars($override_object);
					if (!empty($vars)) {
						$override_classes[$v]['found'] = true;
						$override_found = true;
						object_merge_values($override_classes[$v]['object'], $vars);
					}
				}
				// we need to write overrides to disk
				if ($override_found) {
					foreach ($override_classes as $k => $v) {
						if ($v['found']) {
							$class_code = "<?php\n\n" . '$object_override_blank_object = ' . var_export($v['object'], true) . ';';
							$temp_name = str_replace('\\', '_', trim($k, '\\'));
							\Helper\File::write('./Overrides/Class/Override_' . $temp_name . '.php', $class_code);
						}
					}
				}
			}
			// acls
			if (!empty($data['acl'])) {
				$temp_models = [];
				foreach ($data['acl'] as $k => $v) {
					$object = new $k();
					$models = $object->models;
					foreach ($models as $k2 => $v2) {
						$temp_models[$k2][$k] = $v2;
					}
				}
				$class_code = "<?php\n\n" . '$object_override_blank_object = ' . var_export($temp_models, true) . ';';
				\Helper\File::write('./Overrides/Class/Override_Object_ACL_Registered.php', $class_code);
			}
			// unit tests
			\Helper\File::delete('./Overrides/UnitTests', ['only_contents' => true, 'skip_files' => ['.gitkeep']]);
			// submodule tests first
			if (!empty($data['unit_tests'])) {
				$xml = '';
				$xml.= '<phpunit bootstrap="../../../libraries/vendor/numbers/framework/System/Managers/UnitTests.php">';
					$xml.= '<testsuites>';
						foreach ($data['unit_tests'] as $k => $v) {
							$xml.= '<testsuite name="' . $k . '">';
								foreach (\Helper\File::iterate($v, ['recursive' => true, 'only_extensions' => ['php']]) as $v2) {
									$xml.= '<file>../../' . $v2 . '</file>';
								}
							$xml.= '</testsuite>';
						}
					$xml.= '</testsuites>';
				$xml.= '</phpunit>';
				\Helper\File::write('./Overrides/UnitTests/submodules.xml', $xml);
			}
			// application test last
			$application_tests = \Helper\File::iterate('Miscellaneous/UnitTests', ['recursive' => true, 'only_extensions' => ['php']]);
			if (!empty($application_tests)) {
				$xml = '';
				$xml.= '<phpunit bootstrap="../../../libraries/vendor/numbers/framework/System/Managers/UnitTests.php">';
					$xml.= '<testsuites>';
							$xml.= '<testsuite name="application/unit/tests">';
								foreach ($application_tests as $v) {
									$xml.= '<file>../../' . $v . '</file>';
								}
							$xml.= '</testsuite>';
					$xml.= '</testsuites>';
				$xml.= '</phpunit>';
				\Helper\File::write('./Overrides/UnitTests/application.xml', $xml);
			}
			// updating composer.json file
			if ($options['mode'] == 'commit') {
				$composer_data['extra'] = [
					'installer-types' => ['library'],
					'installer-paths' => []
				];
				foreach ($composer_data['require'] as $k => $v) {
					if (strpos($k, 'Numbers') !== false) {
						$composer_data['extra']['installer-paths']["vendor/{$k}/"] = [strtolower($k)];
						unset($composer_data['require'][$k]);
						$composer_data['require'][strtolower($k)] = $v;
					}
				}
				$composer_data['require']['oomphinc/composer-installers-extender'] = '~1.0';
				\Helper\File::write('../libraries/composer.json', json_encode($composer_data, JSON_PRETTY_PRINT));
			}
			// assinging variables to return to the caller
			$result['data'] = $data;
			if (empty($result['error'])) {
				$result['success'] = true;
			}
		} while (0);
		return $result;
	}

	/**
	 * Process models
	 *
	 * @param array $options
	 * @return array
	 */
	public static function processModels(array $options = []) : array {
		$result = [
			'success' => false,
			'error' => [],
			'hint' => [],
			'data' => [],
			'changes' => 0
		];
		do {
			// we need to process all dependencies first
			$dep = self::processDepsAll($options);
			if (!$dep['success']) {
				$result = $dep;
				$result['error'][] = 'You must fix all dependency related errors first before processing models.';
				break;
			}
			// proccesing models
			if (empty($dep['data']['model_processed'])) {
				$result['error'][] = 'You do not have models to process!';
				break;
			}
			$object_attributes = [];
			$object_relations = [];
			$object_forms = [];
			$flag_relation = true;
			$object_documentation = [];
			$object_import = [];
			$ddl = new numbers_backend_db_class_ddl();
			// run 1 to deterine virtual tables
			$first = true;
			$virtual_models = $dep['data']['model_processed'];
run_again:
			foreach ($virtual_models as $k => $v) {
				$k2 = str_replace('.', '_', $k);
				if ($v == '\Object\Table') {
					$model = Factory::model($k2, true);
					foreach (\Object\Widgets::widget_models as $v0) {
						if (!empty($model->{$v0})) {
							$v01 = $v0 . '_model';
							$virtual_models[str_replace('_', '.', $model->{$v01})] = '\Object\Table';
						}
					}
				}
			}
			if ($first) {
				$first = false;
				goto run_again; // some widgets have attributes
			}
			$dep['data']['model_processed'] = array_merge_hard($dep['data']['model_processed'], $virtual_models);
			$domains = \Object\Data\Domains::getStatic();
			// run 2
			foreach ($dep['data']['model_processed'] as $k => $v) {
				$k2 = str_replace('.', '_', $k);
				if ($v == '\Object\Table') {
					$model = Factory::model($k2, true);
					// todo: disable non default db links
					$temp_result = $ddl->process_table_model($model);
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
					$object_documentation[$v][$k2] = $k2;
					// relation
					if ($flag_relation) {
						if (!empty($model->relation)) {
							$domain = $model->columns[$model->relation['field']]['domain'] ?? null;
							if (!empty($domain)) {
								$domain = str_replace('_sequence', '', $domain);
								$type = $domains[$domain]['type'];
							} else {
								$type = $model->columns[$model->relation['field']]['type'];
							}
							$object_relations[$k2] = [
								'rn_relattr_code' => $model->relation['field'],
								'rn_relattr_name' => $model->title,
								'rn_relattr_model' => $k2,
								'rn_relattr_domain' => $domain,
								'rn_relattr_type' => $type,
								'rn_relattr_inactive' => !empty($model->relation['inactive']) ? 1 : 0
							];
						}
						if (!empty($model->attributes)) {
							$object_attributes[$k2] = [
								'rn_attrmdl_code' => $k2,
								'rn_attrmdl_name' => $model->title,
								'rn_attrmdl_inactive' => 0
							];
						}
					}
				} else if ($v == '\Object\Sequence') {
					$temp_result = $ddl->process_sequence_model($k2);
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
					$object_documentation[$v][$k2] = $k2;
				} else if ($v == '\Object\Function2') {
					$temp_result = $ddl->process_function_model($k2);
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
					$object_documentation[$v][$k2] = $k2;
				} else if ($v == '\Object\Extension') {
					$temp_result = $ddl->process_function_extension($k2);
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
					$object_documentation[$v][$k2] = $k2;
				} else if ($v == '\Object\Import') {
					$object_import[$k2] = [
						'model' => $k2
					];
				}
			}

			// if we have erros
			if (!empty($result['error'])) {
				break;
			}

			// db factory
			$db_factory = Factory::get('db');

			// we load objects from database
			$loaded_objects = [];
			foreach ($ddl->db_links as $k => $v) {
				$ddl_object = $db_factory[$k]['ddl_object'];
				$temp_result = $ddl_object->load_schema($k);
				if (!$temp_result['success']) {
					array_merge3($result['error'], $temp_result['error']);
				} else {
					$loaded_objects[$k] = $temp_result['data'];
				}
			}

			// if we have erros
			if (!empty($result['error'])) {
				break;
			}

			// get a list of all db links
			$db_link_list = array_unique(array_merge(array_keys($ddl->objects), array_keys($loaded_objects)));

			// if we are dropping schema
			if ($options['mode'] == 'drop') {
				$ddl->objects = [];
			}

			// compare schemas per db link
			$schema_diff = [];
			$total_per_db_link = [];
			$total = 0;
			foreach ($db_link_list as $k) {
				// we need to have a back end for comparison
				$compare_options['backend'] = $db_factory[$k]['backend'];
				// comparing
				$temp_result = $ddl->compare_schemas(isset($ddl->objects[$k]) ? $ddl->objects[$k] : [], isset($loaded_objects[$k]) ? $loaded_objects[$k] : [], $compare_options);
				if (!$temp_result['success']) {
					array_merge3($result['hint'], $temp_result['error']);
				} else {
					$schema_diff[$k] = $temp_result['data'];
					if (!isset($total_per_db_link[$k])) {
						$total_per_db_link[$k] = 0;
					}
					$total_per_db_link[$k]+= $temp_result['count'];
					$total+= $temp_result['count'];
				}
			}

			// if there's no schema changes
			if ($total == 0) {
				if ($options['mode'] == 'commit') {
					goto import_data;
				} else {
					$result['success'] = true;
				}
				break;
			}

			// we need to provide a list of changes
			foreach ($total_per_db_link as $k => $v) {
				// total changes
				$result['changes']+= $v;
				// printing summary
				$result['hint'][] = '    * Link ' . $k . ': ';
				foreach ($schema_diff[$k] as $k2 => $v2) {
					$result['hint'][] = '       * ' . $k2 . ': ';
					foreach ($v2 as $k3 => $v3) {
						$result['hint'][] = '        * ' . $k3 . ' - ' . $v3['type'];
					}
				}
			}

			// if we are in no commit mode we exit
			if (!in_array($options['mode'], ['commit', 'drop'])) {
				break;
			}

			// generating sql
			foreach ($total_per_db_link as $k => $v) {
				if ($v == 0) continue;
				$ddl_object = $db_factory[$k]['ddl_object'];
				foreach ($schema_diff[$k] as $k2 => $v2) {
					foreach ($v2 as $k3 => $v3) {
						// we need to make fk constraints last to sort MySQL issues
						if ($k2 == 'new_constraints' && $v3['type'] == 'constraint_new' && $v3['data']['type'] == 'fk') {
							$schema_diff[$k][$k2 . '_fks'][$k3]['sql'] = $ddl_object->renderSql($v3['type'], $v3);
						} else {
							$schema_diff[$k][$k2][$k3]['sql'] = $ddl_object->renderSql($v3['type'], $v3, ['mode' => $options['mode']]);
						}
					}
				}
			}
//			print_r($schema_diff);
//			exit;

			// executing sql
			foreach ($total_per_db_link as $k => $v) {
				if ($v == 0) continue;
				$db_object = new \Db($k);
				// if we are dropping we need to disable foregn key checks
				if ($options['mode'] == 'drop') {
					if ($db_object->backend == 'mysqli') {
						$db_object->query('SET foreign_key_checks = 0;');
						// we also need to unset sequences
						unset($schema_diff[$k]['delete_sequences']);
					}
				}
				foreach ($schema_diff[$k] as $k2 => $v2) {
					foreach ($v2 as $k3 => $v3) {
						if (empty($v3['sql'])) {
							continue;
						}
						if (is_array($v3['sql'])) {
							$temp = $v3['sql'];
						} else {
							$temp = [$v3['sql']];
						}
						foreach ($temp as $v4) {
							$temp_result = $db_object->query($v4);
							if (!$temp_result['success']) {
								array_merge3($result['error'], $temp_result['error']);
								goto error;
							}
						}
					}
				}
			}

			// if we got here - we are ok
			$result['success'] = true;
		} while(0);
error:
		return $result;
		// import data
import_data:
		// we need to import data
		if (!empty($object_import) && $options['mode'] == 'commit') {
			$result['hint'][] = '';
			foreach ($object_import as $k => $v) {
				$data_object = new $k();
				$data_result = $data_object->process();
				if (!$data_result['success']) {
					Throw new Exception(implode("\n", $data_result['error']));
				}
				$result['hint'] = array_merge($result['hint'], $data_result['hint']);
			}
		}
		// relation
		if ($flag_relation && $options['mode'] == 'commit') {
			$result['hint'][] = '';
			$model2 = Factory::model('numbers_data_relations_model_relation_attributes');
			// insert new models
			if (!empty($object_relations)) {
				foreach ($object_relations as $k => $v) {
					$result_insert = $model2->save($v, ['pk' => ['rn_relattr_code'], 'ignore_not_set_fields' => true]);
				}
				$result['hint'][] = ' * Imported relation models!';
			}
			// we need to process forms
			foreach ($dep['data']['submodule_dirs'] as $v) {
				$dir = $v . 'model/form/';
				if (!file_exists($dir)) {
					continue;
				}
				$files = \Helper\File::iterate($dir, ['only_extensions' => ['php']]);
				foreach ($files as $v2) {
					$model_name = str_replace(['../libraries/vendor/', '.php'], '', $v2);
					$model_name = str_replace('/', '_', $model_name);
					$model = new $model_name(['skip_processing' => true]);
					if (empty($model->form_object->misc_settings['option_models'])) {
						continue;
					}
					// loop though fields
					foreach ($model->form_object->misc_settings['option_models'] as $k3 => $v3) {
						$object_forms[$model_name . '::' . $k3] = [
							'rn_relfrmfld_form_code' => $model_name,
							'rn_relfrmfld_form_name' => $model->title,
							'rn_relfrmfld_field_code' => $k3,
							'rn_relfrmfld_field_name' => $v3['field_name'],
							'rn_relfrmfld_relattr_id' => $v3['model'],
							'rn_relfrmfld_inactive' => 0
						];
					}
				}
			}
			if (!empty($object_forms)) {
				// load all relation models
				$data = $model2->get(['pk' => ['rn_relattr_model']]);
				$model = Factory::model('numbers_data_relations_model_relation_formfields');
				foreach ($object_forms as $k => $v) {
					if (empty($data[$v['rn_relfrmfld_relattr_id']])) {
						continue;
					}
					$v['rn_relfrmfld_relattr_id'] = $data[$v['rn_relfrmfld_relattr_id']]['rn_relattr_id'];
					$result_insert = $model->save($v, ['pk' => ['rn_relfrmfld_form_code', 'rn_relfrmfld_field_code'], 'ignore_not_set_fields' => true]);
				}
				$result['hint'][] = ' * Imported relation form fields!';
			}
			// todo: import models   
			//print_r2($object_attributes);
			if (!empty($object_attributes)) {
				$model = Factory::model('numbers_data_relations_model_attribute_models');
				foreach ($object_attributes as $k => $v) {
					$result_insert = $model->save($v, ['pk' => ['rn_attrmdl_code'], 'ignore_not_set_fields' => true]);
				}
				$result['hint'][] = ' * Imported attribute models!';
			}
		}
		// we need to generate documentation
		/*
		$system_documentation = Application::get('system_documentation');
		if (!empty($system_documentation) && $options['mode'] == 'commit') {
			$model = Factory::model($system_documentation['model']);
		}
		*/
		return $result;
	}

	/**
	 * Special function for data processing
	 *
	 * @param array $data
	 * @param array $composer_data
	 * @param array $composer_dirs
	 */
	public static function processDepsArray($data, & $composer_data, & $composer_dirs, $origin_submodule, & $origin_dependencies) {
		if (empty($data)) return;
		foreach ($data as $k => $v) {
			foreach ($v as $k2 => $v2) {
				if (!is_array($v2) && !empty($v2)) {
					$name = $k . '/' . $k2;
					$composer_data[$name] = $v2;
					$composer_dirs[$name] = '../libraries/vendor/' . $k . '/' . $k2 . '/';
					if ($k2 != '__any') {
						$origin_dependencies[$origin_submodule][$name] = $name;
					}
				} else {
					foreach ($v2 as $k3 => $v3) {
						if (!is_array($v3) && !empty($v3)) {
							$name = $k . '/' . $k2 . '/' . $k3;
							$composer_dirs[$name] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/';
							if ($k3 != '__any') {
								$origin_dependencies[$origin_submodule][$name] = $name;
							}
						} else {
							foreach ($v3 as $k4 => $v4) {
								if (!is_array($v4) && !empty($v4)) {
									$name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4;
									$composer_dirs[$name] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/';
									if ($k4 != '__any') {
										$origin_dependencies[$origin_submodule][$name] = $name;
									}
								} else {
									foreach ($v4 as $k5 => $v5) {
										if (!is_array($v5) && !empty($v5)) {
											$name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5;
											$composer_dirs[$name] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/';
											if ($k5 != '__any') {
												$origin_dependencies[$origin_submodule][$name] = $name;
											}
										} else {
											foreach ($v5 as $k6 => $v6) {
												if (!is_array($v6) && !empty($v6)) {
													$name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6;
													$composer_dirs[$name] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/';
													if ($k6 != '__any') {
														$origin_dependencies[$origin_submodule][$name] = $name;
													}
												} else {
													foreach ($v6 as $k7 => $v7) {
														if (!is_array($v7) && !empty($v7)) {
															$name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/' . $k7;
															$composer_dirs[$name] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/' . $k7 . '/';
															if ($k7 != '__any') {
																$origin_dependencies[$origin_submodule][$name] = $name;
															}
														} else {
															// we skip more than 7 part keys for now
															Throw new \Exception('we skip more than 7 part keys for now');
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
				}
			}
		}
	}
}