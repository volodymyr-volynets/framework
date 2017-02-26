<?php

/**
 * Tenant
 */
class tenant {

	/**
	 * Tenant #
	 *
	 * @return int
	 */
	public static function tenant_id() {
		return application::get('application.structure.settings.tenant.id');
	}
}
