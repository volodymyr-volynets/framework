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
		$structure = Application::get('application.structure') ?? [];
		$result = [];
		$host_parts = request::host_parts();
		$validator = new object_validator_domain_part();
		// see if we are in multi db environment
		if (!empty($structure['db_multiple'])) {
			// validate host part
			$validated = [];
			if (!empty($host_parts[$structure['db_domain_level']])) {
				$validated = $validator->validate($host_parts[$structure['db_domain_level']]);
			}
			if (empty($validated['success'])) {
				if (!empty($structure['db_not_found_url'])) {
					request::redirect($structure['db_not_found_url']);
				} else {
					Throw new Exception('Invalid URL!');
				}
			}
			// default settings are for default db and cache links
			$result['cache']['default']['cache_key'] = $result['db']['default']['dbname'] = ($structure['db_prefix'] ?? '') . $validated['data'];
		}
		// multi tenant environment
		if (!empty($structure['tenant_multiple'])) {
			// validate host part
			$validated = [];
			if (!empty($host_parts[$structure['tenant_domain_level']])) {
				$validated = $validator->validate($host_parts[$structure['tenant_domain_level']]);
			}
			if (empty($validated['success'])) {
				if (!empty($structure['tenant_not_found_url'])) {
					request::redirect($structure['tenant_not_found_url']);
				} else {
					Throw new Exception('Invalid URL!');
				}
			}
			// clenup tenant name
			$result['tenant']['code'] = strtoupper($validated['data']);
		} else { // we simply use id if its a single tenant system
			$result['tenant']['id'] = (int) $structure['tenant_default_id'];
		}
		// put settings back to registry
		Application::set('application.structure.settings', $result);
		return $result;
	}

	/**
	 * Get tenant settings
	 *
	 * @return array
	 */
	public function tenant() {
		$tenant_datasource_settings = object_acl_resources::get_static('application_structure', 'tenant');
		if (!empty($tenant_datasource_settings['tenant_datasource'])) {
			// prepare to query tenant
			$tenant_input = Application::get('application.structure.settings.tenant');
			$tenant_where = [];
			if (!empty($tenant_datasource_settings['column_prefix'])) {
				array_key_prefix_and_suffix($tenant_input, $tenant_datasource_settings['column_prefix']);
			}
			// find tenant
			$class = $tenant_datasource_settings['tenant_datasource'];
			$datasource = new $class();
			$tenant_result = $datasource->get(['where' => $tenant_input, 'single_row' => true]);
			if (empty($tenant_result)) {
				$structure = Application::get('application.structure') ?? [];
				if (!empty($structure['tenant_not_found_url'])) {
					request::redirect($structure['tenant_not_found_url']);
				} else {
					Throw new Exception('Invalid URL!');
				}
			} else {
				if (!empty($tenant_datasource_settings['column_prefix'])) {
					array_key_prefix_and_suffix($tenant_result, $tenant_datasource_settings['column_prefix'], null, true);
				}
				Application::set('application.structure.settings.tenant', $tenant_result);
			}
		}
	}
}