<?php

class system_dependencies {

	/**
	 * Process dependencies
	 *
	 * @param array $options
	 * @return array
	 */
	public static function process_deps_all($options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => []
		];
		do {
			// processing main dependency file
			$main_dep_filename = 'config/application.ini';
			if (!file_exists($main_dep_filename)) {
				$result['error'][] = "Main dep. file not found!";
				break;
			}

			// some array arrangements
			$data = system_config::ini($main_dep_filename, 'dependencies');
			$data = $data['dep'] ?? [];
			$data['composer'] = $data['composer'] ?? [];
			$data['submodule'] = $data['submodule'] ?? [];
			$data['apache'] = $data['apache'] ?? [];
			$data['php'] = $data['php'] ?? [];
			$data['model'] = $data['model'] ?? [];
			$data['override'] = $data['override'] ?? [];
			$data['media'] = $data['media'] ?? [];
			$data['model_processed'] = [];
			$data['unit_tests'] = [];

			// we have small chicken and egg problem with composer
			$composer_data = [];
			$composer_dirs = [];
			$composer_dirs[] = 'config/';
			if (file_exists('../libraries/composer.json')) {
				$composer_data = json_decode(file_get_contents('../libraries/composer.json'), true);
			}

			// if we have composer or submodules from main dep file
			if (!empty($data['composer']) || !empty($data['submodules'])) {
				$composer_data['require'] = [];
				if (!empty($data['composer'])) {
					self::process_deps_array($data['composer'], $composer_data['require'], $composer_dirs);
				}
				if (!empty($data['submodule'])) {
					self::process_deps_array($data['submodule'], $composer_data['require'], $composer_dirs);
				}
			}

			// processing submodules
			$mutex = [];
			$__any = [];
			if (!empty($composer_dirs)) {
				for ($i = 0; $i < 3; $i++) {
					foreach ($composer_dirs as $k => $v) {
						if (isset($mutex[$k])) {
							continue;
						} else {
							$mutex[$k] = 1;
						}
						if (file_exists($v . 'module.ini')) {
							$sub_data = system_config::ini($v . 'module.ini', 'dependencies');
							$sub_data = isset($sub_data['dep']) ? $sub_data['dep'] : [];
							if (!empty($sub_data['composer'])) {
								self::process_deps_array($sub_data['composer'], $composer_data['require'], $composer_dirs);
								$data['composer'] = array_merge2($data['composer'], $sub_data['composer']);
							}
							if (!empty($sub_data['submodule'])) {
								self::process_deps_array($sub_data['submodule'], $composer_data['require'], $composer_dirs);
								$data['submodule'] = array_merge2($data['submodule'], $sub_data['submodule']);
							}
							if (!empty($sub_data['apache'])) {
								$data['apache'] = array_merge2($data['apache'], $sub_data['apache']);
							}
							if (!empty($sub_data['php'])) {
								$data['php'] = array_merge2($data['php'], $sub_data['php']);
							}
							if (!empty($sub_data['model'])) {
								$data['model'] = array_merge2($data['model'], $sub_data['model']);
							}
							if (!empty($sub_data['override'])) {
								$data['override'] = array_merge2($data['override'], $sub_data['override']);
							}
							if (!empty($sub_data['media'])) {
								$data['media'] = array_merge2($data['media'], $sub_data['media']);
							}
							// processing unit tests
							if (file_exists($v . 'unit_tests')) {
								// we have to reload the module.ini file to get module name
								$sub_data_temp = system_config::ini($v . 'module.ini', 'module');
								$data['unit_tests'][$sub_data_temp['module']['name']] = $v . 'unit_tests/';
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
			if (!empty($composer_data['require'])) {
				foreach ($composer_data['require'] as $k => $v) {
					if (!file_exists('../libraries/vendor/' . $k)) {
						$result['error'][] = " - Composer library \"$k\" is not loaded!";
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
				} else {
					$temp = `apachectl -t -D DUMP_MODULES`;
					$ext_have = array_map('strtolower', explode("\n", $temp));
					$temp = array();
					foreach ($ext_have as $k => $v) {
						$temp[] = trim(str_replace(array('(shared)', '(static)'), '', $v));
					}
					$ext_have = $temp;
				}
				foreach ($data['apache']['module'] as $k => $v) {
					if (!in_array($k, $ext_have)) {
						$result['error'][] = " - Apache module \"$k\" is not loaded!";
					}
				}
			}

			// processing models
			if (!empty($data['model'])) {
				array_keys_to_string($data['model'], $data['model_processed']);
			}

			// handling overrides, cleanup directory first
			helper_file::rmdir('./overrides/class', ['only_contents' => true, 'skip_files' => ['.gitkeep']]);
			if (!empty($data['override'])) {
				array_keys_to_string($data['override'], $data['override_processed']);
				$override_classes = [];
				$override_found = false;
				foreach ($data['override_processed'] as $k => $v) {
					if (!isset($override_classes[$v])) {
						$override_classes[$v] = [
							'object' => new object_override_blank(),
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
							helper_file::write('./overrides/class/override_' . $k . '.php', $class_code);
						}
					}
				}
			}

			// unit tests
			helper_file::rmdir('./overrides/unit_tests', ['only_contents' => true, 'skip_files' => ['.gitkeep']]);
			// submodule tests first
			if (!empty($data['unit_tests'])) {
				$xml = '';
				$xml.= '<phpunit bootstrap="../../../libraries/vendor/numbers/framework/system/managers/unit_tests.php">';
					$xml.= '<testsuites>';
						foreach ($data['unit_tests'] as $k => $v) {
							$xml.= '<testsuite name="' . $k . '">';
								foreach (helper_file::iterate($v, ['recursive' => true, 'only_extensions' => ['php']]) as $v2) {
									$xml.= '<file>../../' . $v2 . '</file>';
								}
							$xml.= '</testsuite>';
						}
					$xml.= '</testsuites>';
				$xml.= '</phpunit>';
				helper_file::write('./overrides/unit_tests/submodules.xml', $xml);
			}
			// application test last
			$application_tests = helper_file::iterate('misc/unit_tests', ['recursive' => true, 'only_extensions' => ['php']]);
			if (!empty($application_tests)) {
				$xml = '';
				$xml.= '<phpunit bootstrap="../../../libraries/vendor/numbers/framework/system/managers/unit_tests.php">';
					$xml.= '<testsuites>';
							$xml.= '<testsuite name="application/unit/tests">';
								foreach ($application_tests as $v) {
									$xml.= '<file>../../' . $v . '</file>';
								}
							$xml.= '</testsuite>';
					$xml.= '</testsuites>';
				$xml.= '</phpunit>';
				helper_file::write('./overrides/unit_tests/application.xml', $xml);
			}

			// updating composer.json file
			if ($options['mode'] == 'commit') {
				helper_file::write('../libraries/composer.json', json_encode($composer_data, JSON_PRETTY_PRINT));
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
	public static function process_models($options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'hint' => [],
			'data' => []
		];
		do {
			// we need to process all dependencies first
			$dep = self::process_deps_all($options);
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

			$object_import = [];
			$ddl = new numbers_backend_db_class_ddl();
			foreach ($dep['data']['model_processed'] as $k => $v) {
				if ($v == 'object_table') {
					$temp_result = $ddl->process_table_model(str_replace('.', '_', $k));
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
				} else if ($v == 'object_sequence') {
					$temp_result = $ddl->process_sequence_model(str_replace('.', '_', $k));
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
				} else if ($v == 'object_function') {
					$temp_result = $ddl->process_function_model(str_replace('.', '_', $k));
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
				} else if ($v == 'object_extension') {
					$temp_result = $ddl->process_function_extension(str_replace('.', '_', $k));
					if (!$temp_result['success']) {
						array_merge3($result['error'], $temp_result['error']);
					}
				} else if ($v == 'object_import') {
					$object_import[str_replace('.', '_', $k)] = [
						'model' => str_replace('.', '_', $k)
					];
				}
			}
			//print_r($ddl->objects['default']['extension']);

			// if we have erros
			if (!empty($result['error'])) {
				break;
			}

			// db factory
			$db_factory = factory::get('db');

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

			// compare schems per db link
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

			// if there's no schame changes
			if ($total == 0) {
				$result['success'] = true;
				break;
			}

			// we need to provide a list of changes
			foreach ($total_per_db_link as $k => $v) {
				$result['hint'][] = '';
				$result['hint'][] = "Db link $k requires $v changes!";
				// printing summary
				$result['hint'][] = ' * Link ' . $k . ': ';
				foreach ($schema_diff[$k] as $k2 => $v2) {
					$result['hint'][] = '   * ' . $k2 . ': ';
					foreach ($v2 as $k3 => $v3) {
						$result['hint'][] = '    * ' . $k3 . ' - ' . $v3['type'];
					}
				}
			}

			// if we are in no commit mode we exit
			if ($options['mode'] != 'commit') {
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
							$schema_diff[$k][$k2 . '_fks'][$k3]['sql'] = $ddl_object->render_sql($v3['type'], $v3);
						} else {
							$schema_diff[$k][$k2][$k3]['sql'] = $ddl_object->render_sql($v3['type'], $v3);
						}
					}
				}
			}
			//print_r($schema_diff);
			//exit;

			// executing sql
			foreach ($total_per_db_link as $k => $v) {
				if ($v == 0) continue;
				$db_object = new db($k);
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
error:
		return $result;
	}

	/**
	 * Special function for data processing
	 *
	 * @param array $data
	 * @param array $composer_data
	 * @param array $composer_dirs
	 */
	public static function process_deps_array($data, & $composer_data, & $composer_dirs) {
		if (empty($data)) return;
		foreach ($data as $k => $v) {
			foreach ($v as $k2 => $v2) {
				if (!is_array($v2) && !empty($v2)) {
					$composer_data[$k . '/' . $k2] = $v2;
					$composer_dirs[$k . '/' . $k2] = '../libraries/vendor/' . $k . '/' . $k2 . '/';
				} else {
					foreach ($v2 as $k3 => $v3) {
						if (!is_array($v3) && !empty($v3)) {
							$composer_dirs[$k . '/' . $k2 . '/' . $k3] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/';
						} else {
							foreach ($v3 as $k4 => $v4) {
								if (!is_array($v4) && !empty($v4)) {
									$composer_dirs[$k . '/' . $k2 . '/' . $k3 . '/' . $k4] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/';
								} else {
									foreach ($v4 as $k5 => $v5) {
										if (!is_array($v5) && !empty($v5)) {
											$composer_dirs[$k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/';
										} else {
											foreach ($v5 as $k6 => $v6) {
												if (!is_array($v6) && !empty($v6)) {
													$composer_dirs[$k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6] = '../libraries/vendor/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/';
												} else {
													// we skip more than 5 part keys for now
													Throw new Exception('we skip more than 6 part keys for now');
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