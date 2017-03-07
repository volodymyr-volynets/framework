<?php

/**
 * Tenant
 */
class tenant {

	/**
	 * Override tenant #
	 *
	 * @var int
	 */
	private static $override_tenant_id;

	/**
	 * Tenant #
	 *
	 * @return int
	 */
	public static function tenant_id() {
		return self::$override_tenant_id ?? application::get('application.structure.settings.tenant.id');
	}

	/**
	 * Set override tenant #
	 *
	 * @param int $tenant_id
	 */
	public static function set_override_tenant_id($tenant_id) {
		self::$override_tenant_id = $tenant_id;
	}
}
