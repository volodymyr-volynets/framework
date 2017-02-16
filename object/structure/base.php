<?php

class object_structure_base {

	/**
	 * Get settings
	 *	- database
	 *  - cache
	 *
	 * @return array
	 */
	public function settings() {
		$structure = application::get('application.structure') ?? [];
		$result = [];
		// see if we are in multi db environment
		if (!empty($structure['db_multiple'])) {
			$host_parts = request::host_parts();
			// if db name cannot be found
			if (empty($host_parts[$structure['db_domain_level']])) {
				if (!empty($structure['db_not_found_url'])) {
					request::redirect($structure['db_not_found_url']);
				} else {
					Throw new Exception('Invalid URL!');
				}
			}
			// clean up database name
			$temp = preg_replace('/[^a-zA-Z0-9_]+/', '', ($structure['db_prefix'] ?? '') . $host_parts[$structure['db_domain_level']]);
			// default settings are for default db and cache links
			$result['db']['default']['dbname'] = strtolower($temp);
			$result['cache']['default']['cache_key'] = strtolower($temp);
		}
		// put settings back to registry
		application::set('application.structure.settings', $result);
		return $result;
	}

	/**
	 * Get tenant settings
	 *
	 * @return array
	 */
	public function tenant() {
		return [];
	}
}