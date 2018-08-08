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
	 * @return array
	 */
	public static function ini(string $ini_file, $environment = null) : array {
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
				array_key_set($result, $k, $v);
			}
		}
		unset($data['dependencies']);
		// proccesing environment specific sectings
		foreach ($data as $section => $values) {
			$sections = explode(',', $section);
			if (empty($values) || (!in_array($environment, $sections) && !in_array('*', $sections))) continue;
			foreach ($values as $k=>$v) {
				array_key_set($result, $k, $v);
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
		$file = $ini_folder . 'environment.ini';
		if (file_exists($file)) {
			$ini_data = self::ini($file);
			$result = array_merge2($result, $ini_data);
		}
		// application.ini file second
		$file = $ini_folder . 'application.ini';
		if (file_exists($file)) {
			$ini_data = self::ini($file, $result['environment']);
			$result = array_merge2($result, $ini_data);
		}
		return $result;
	}
}