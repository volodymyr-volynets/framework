<?php

class system_dependencies {

	/**
	 * Process dependencies
	 *
	 * @param array $options
	 * @return array
	 */
	public static function process_deps_all($options = array()) {
		$result = array(
			'success' => false,
			'error' => array(),
			'data' => array()
		);
		do {
			// processing main dependency file
			$main_dep_filename = 'application.ini';
			if (!file_exists($main_dep_filename)) {
				$result['error'][] = "Main dep. file not found!";
				break;
			}

			// some array arrangements
			$data = application::ini($main_dep_filename, 'dependencies');
			$data = isset($data['dep']) ? $data['dep'] : array();
			$data['composer'] = isset($data['composer']) ? $data['composer'] : array();
			$data['submodule'] = isset($data['submodule']) ? $data['submodule'] : array();
			$data['apache'] = isset($data['apache']) ? $data['apache'] : array();
			$data['php'] = isset($data['php']) ? $data['php'] : array();

			// we have small chicken and egg problem with composer
			$composer_data = array();
			$composer_dirs = array();
			if (file_exists('../libraries/composer.json')) {
				$composer_data = json_decode(file_get_contents('../libraries/composer.json'), true);
			}

			// if we have composer or submodules from main dep file
			if (!empty($data['composer']) || !empty($data['submodules'])) {
				$composer_data['require'] = array();
				if (!empty($data['composer'])) {
					self::process_deps_array($data['composer'], $composer_data['require'], $composer_dirs);
				}
				if (!empty($data['submodule'])) {
					self::process_deps_array($data['submodule'], $composer_data['require'], $composer_dirs);
				}
			}

			// processing submodules
			$temp = array();
			if (!empty($composer_dirs)) {
				for ($i = 0; $i < 3; $i++) {
					foreach ($composer_dirs as $k => $v) {
						if (isset($temp[$k])) {
							continue;
						} else {
							$temp[$k] = 1;
						}
						if (file_exists($v . 'module.ini')) {
							$sub_data = application::ini($v . 'module.ini', 'dependencies');
							$sub_data = isset($sub_data['dep']) ? $sub_data['dep'] : array();
							if (!empty($sub_data['composer'])) {
								self::process_deps_array($sub_data['composer'], $composer_data['require'], $composer_dirs);
								$data['composer'] = array_merge_recursive($data['composer'], $sub_data['composer']);
							}
							if (!empty($sub_data['submodule'])) {
								self::process_deps_array($sub_data['submodule'], $composer_data['require'], $composer_dirs);
								$data['submodule'] = array_merge_recursive($data['submodule'], $sub_data['submodule']);
							}
							if (!empty($sub_data['apache'])) {
								$data['apache'] = array_merge_recursive($data['apache'], $sub_data['apache']);
							}
							if (!empty($sub_data['php'])) {
								$data['php'] = array_merge_recursive($data['php'], $sub_data['php']);
							}
						} else {
							$keys = explode('/', $k);
							if ($keys[0] == 'numbers') {
								$result['error'][] = " - Submodule not found in {$v}module.ini";
							}
						}
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