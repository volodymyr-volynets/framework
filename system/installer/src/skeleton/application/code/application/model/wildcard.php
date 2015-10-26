<?php

class model_wildcard {

	/**
	 * Use this to determine database name, cache folder, etc
	 * This is required for multi system application with the same
	 * codebase and multiple databases
	 */
	public static function get() {
		// example when key is third level domain
		//$host = request::host_parts();
		//$key = $host[3];

		// example when key is 3rd and up
		//$host = request::host_parts();
		//unset($host[1], $host[2]);
		//$key = implode('.', $host);

		// testing data
		if (!empty($_REQUEST)) {
			$host = request::host_parts();
			$key = $host[2];
		} else {
			$key = 'playground';
		}

		// we need to retrun data in this format
		return ['dbname' => $key, 'cache_key' => $key];
	}
}