<?php

class Tenant {

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
	public static function id() {
		if (!empty(self::$override_tenant_id)) {
			return (int) self::$override_tenant_id;
		} else {
			$default_tenant_id = \Application::get('application.structure.tenant_default_id');
			if (!empty($default_tenant_id)) {
				return (int) $default_tenant_id;
			}
		}
		return (int) \Application::get('application.structure.settings.tenant.id');
	}

	/**
	 * Set override tenant #
	 *
	 * @param int $tenant_id
	 */
	public static function setOverrideTenantId($tenant_id) {
		self::$override_tenant_id = $tenant_id;
	}
}
