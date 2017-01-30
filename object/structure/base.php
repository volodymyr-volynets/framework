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
			$temp = preg_replace('/[^a-zA-Z0-9_]+/', '', ($structure['db_prefix'] ?? '') . $host_parts[$structure['db_domain_level']]);
			// default settings are for default db and cache links
			$result['db']['default']['dbname'] = $temp;
			$result['cache']['default']['cache_key'] = $temp;
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