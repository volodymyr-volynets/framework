<?php

namespace Object\Structure;
class Base {

	/**
	 * Get settings
	 *	- database
	 *  - cache
	 *
	 * @return array
	 */
	public function settings() {
		$structure = \Application::get('application.structure') ?? [];
		$result = [];
		$host_parts = \Request::hostParts();
		$validator = new \Object\Validator\Domain\Part();
		// see if we are in multi db environment
		if (!empty($structure['db_multiple'])) {
			// validate host part
			$validated = [];
			if (!empty($host_parts[$structure['db_domain_level']])) {
				$validated = $validator->validate($host_parts[$structure['db_domain_level']]);
			}
			if (empty($validated['success'])) {
				if (!empty($structure['db_not_found_url'])) {
					\Request::redirect($structure['db_not_found_url']);
				} else {
					\Object\Error\Base::$flag_database_tenant_not_found = true;
					Throw new \Exception('Invalid URL!', -1);
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
					\Request::redirect($structure['tenant_not_found_url']);
				} else {
					\Object\Error\Base::$flag_database_tenant_not_found = true;
					Throw new \Exception('Invalid URL!', -1);
				}
			}
			// clenup tenant name
			$result['tenant']['code'] = strtoupper($validated['data']);
		} else if (!empty($structure['tenant_default_id'])) { // we simply use id if its a single tenant system
			$result['tenant']['id'] = (int) $structure['tenant_default_id'];
		}
		// put settings back to registry
		\Application::set('application.structure.settings', $result);
		return $result;
	}

	/**
	 * Get tenant settings
	 *
	 * @return array
	 */
	public function tenant() {
		// see if we have a tenant in a __token
		$input = \Request::input();
		if (!empty($input['__token'])) {
			$crypt_model = new \Crypt();
			$crypt_result = $crypt_model->tokenValidate($input['__token']);
		}
		$tenant_datasource_settings = \Object\ACL\Resources::getStatic('application_structure', 'tenant');
		if (!empty($tenant_datasource_settings['tenant_datasource'])) {
			// prepare to query tenant
			$tenant_input = \Application::get('application.structure.settings.tenant');
			// see if we have tenant override from __token
			if (!empty($crypt_result['id'])) {
				$tenant_input = ['id' => (int) $crypt_result['id']];
			}
			if (!empty($tenant_input)) {
				if (!empty($tenant_datasource_settings['column_prefix'])) {
					array_key_prefix_and_suffix($tenant_input, $tenant_datasource_settings['column_prefix']);
				}
				// find tenant
				$class = $tenant_datasource_settings['tenant_datasource'];
				$datasource = new $class();
				$tenant_result = $datasource->get(['where' => $tenant_input, 'single_row' => true]);
				if (empty($tenant_result)) {
					$structure = \Application::get('application.structure') ?? [];
					if (!empty($structure['tenant_not_found_url'])) {
						\Request::redirect($structure['tenant_not_found_url']);
					} else {
						Throw new \Exception('Invalid URL!', -1);
					}
				} else {
					if (!empty($tenant_datasource_settings['column_prefix'])) {
						array_key_prefix_and_suffix($tenant_result, $tenant_datasource_settings['column_prefix'], null, true);
					}
					\Application::set('application.structure.settings.tenant', $tenant_result);
				}
			}
		}
	}
}