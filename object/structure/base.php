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
		$host_parts = request::host_parts();
		// see if we are in multi db environment
		if (!empty($structure['db_multiple'])) {
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
		// multi tenant environment
		if (!empty($structure['tenant_miltiple'])) {
			// if tenant name cannot be found in url
			if (empty($host_parts[$structure['tenant_domain_level']])) {
				if (!empty($structure['tenant_not_found_url'])) {
					request::redirect($structure['tenant_not_found_url']);
				} else {
					Throw new Exception('Invalid URL!');
				}
			}
			// clenup tenant name
			$result['tenant']['code'] = preg_replace('/[^a-zA-Z0-9_]+/', '', $host_parts[$structure['tenant_domain_level']]);
		} else { // we simply use id if its a single tenant system
			$result['tenant']['id'] = (int) $structure['tenant_default_id'];
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
		$structure = application::get('application.structure') ?? [];
		if (!empty($structure['tenant_model'])) {
			$tenant = call_user_func_array([$structure['tenant_model'], 'tenant'], [application::get('application.structure.settings.tenant')]);
			if (empty($tenant)) {
				if (!empty($structure['tenant_not_found_url'])) {
					request::redirect($structure['tenant_not_found_url']);
				} else {
					Throw new Exception('Invalid URL!');
				}
			}
			application::set('application.structure.settings.tenant', $tenant);
		}
	}
}