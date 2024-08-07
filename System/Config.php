<?php

/**
 * Configuration file helper
 */
namespace System;
class Config {

	/**
	 * Process ini file
	 *
	 * @param string $ini_file
	 * @param string $environment
	 * @param array $options
	 *	boolean simple_keys
	 * @return array
	 */
	public static function ini(string $ini_file, $environment = null, array $options = []) : array {
		$result = [];
		$data = parse_ini_file($ini_file, true);
		// processing environment
		if (!empty($data['environment'])) {
			foreach ($data['environment'] as $k => $v) {
				array_key_set($result, explode('.', $k), $v);
			}
		}
		unset($data['environment']);
		// small chicken and egg problem for environment variable
		if ($environment == null && !empty($result['environment'])) {
			$environment = $result['environment'];
		}
		// processing dependencies first
		if (!empty($data['dependencies'])) {
			foreach ($data['dependencies'] as $k => $v) {
				if (empty($options['simple_keys'])) {
					array_key_set($result, $k, $v);
				} else {
					$result[$k] = $v;
				}
			}
		}
		unset($data['dependencies']);
		// proccesing environment specific sectings
		foreach ($data as $section => $values) {
			$sections = explode(',', $section);
			if (empty($values) || (!in_array($environment, $sections) && !in_array('*', $sections))) {
				continue;
			}
			foreach ($values as $k => $v) {
				if (empty($options['simple_keys'])) {
					array_key_set($result, $k, $v);
				} else {
					$result[$k] = $v;
				}
			}
		}
		return $result;
	}

	/**
	 * Load configuration files
	 *
	 * @param string $ini_folder
	 * @return array
	 */
	public static function load(string $ini_folder) : array {
		$result = [
			'environment' => 'production'
		];
		// environment ini file first
		$environment_file = $ini_folder . 'environment.ini';
		if (file_exists($environment_file)) {
			$ini_data = self::ini($environment_file);
			$result = array_merge2($result, $ini_data);
		}
		// application.ini file second
		$application_file = $ini_folder . 'application.ini';
		if (file_exists($application_file)) {
			$ini_data = self::ini($application_file, $result['environment']);
			$result = array_merge2($result, $ini_data);
		}
		// add dev environment last to override settings from applicaiton.ini
		if ($result['environment'] == 'development') {
			$ini_data = self::ini($environment_file, $result['environment']);
			$result = array_merge_hard($result, $ini_data);
		}
		return $result;
	}
}