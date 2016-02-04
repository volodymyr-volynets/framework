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
			$data = isset($data['dep']) ? $data['dep'] : [];
			$data['composer'] = isset($data['composer']) ? $data['composer'] : [];
			$data['submodule'] = isset($data['submodule']) ? $data['submodule'] : [];
			$data['apache'] = isset($data['apache']) ? $data['apache'] : [];
			$data['php'] = isset($data['php']) ? $data['php'] : [];
			$data['model'] = isset($data['model']) ? $data['model'] : [];
			$data['media'] = isset($data['media']) ? $data['media'] : [];
			$data['model_processed'] = [];

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
			$temp = [];
			$__any = [];
			if (!empty($composer_dirs)) {
				for ($i = 0; $i < 3; $i++) {
					foreach ($composer_dirs as $k => $v) {
						if (isset($temp[$k])) {
							continue;
						} else {
							$temp[$k] = 1;
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
							if (!empty($sub_data['media'])) {
								$data['media'] = array_merge2($data['media'], $sub_data['media']);
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

			// updating composer.json file
			if ($options['mode'] == 'commit') {
				file_put_contents('../libraries/composer.json', json_encode($composer_data, JSON_PRETTY_PRINT));
				chmod('../libraries/composer.json', 0777);
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
				}
			}
			//print_r($ddl->objects['default']['function']);

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
					array_merge3($result['error'], $temp_result['error']);
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

			// if we are in no commit mode
			if ($options['mode'] != 'commit') {
				foreach ($total_per_db_link as $k => $v) {
					$result['error'][] = "Db link $k requires $v changes!";
					// printing summary
					$result['error'][] = ' * Link ' . $k . ': ';
					foreach ($schema_diff[$k] as $k2 => $v2) {
						$result['error'][] = '   * ' . $k2 . ': ';
						foreach ($v2 as $k3 => $v3) {
							$result['error'][] = '    * ' . $k3 . ' - ' . $v3['type'];
						}
					}
				}
				break;
			}

			// generating sql
			foreach ($total_per_db_link as $k => $v) {
				if ($v == 0) continue;
				$ddl_object = $db_factory[$k]['ddl_object'];
				foreach ($schema_diff[$k] as $k2 => $v2) {
					foreach ($v2 as $k3 => $v3) {
						$schema_diff[$k][$k2][$k3]['sql'] = $ddl_object->render_sql($v3['type'], $v3);
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
											// we skip more than 5 part keys for now
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