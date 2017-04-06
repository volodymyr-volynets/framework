<?php

namespace Object;
class Controller {

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Icon
	 *
	 * @var string 
	 */
	public $icon;

	/**
	 * Description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Acl
	 *
	 * By default we allow public/authorized access
	 *
	 * @var array
	 */
	public $acl = [
		'public' => true,
		'authorized' => true,
		'permission' => false
	];

	/**
	 * Actions
	 *
	 * @var array
	 */
	public $actions = [];

	/**
	 * Bread crumbs
	 *
	 * @var array
	 */
	public $breadcrumbs = [];

	/**
	 * Controller #
	 *
	 * @var int
	 */
	public $controller_id;

	/**
	 * Method code
	 *
	 * @var string
	 */
	public $method_code;

	/**
	 * Singleton
	 *
	 * @var boolean
	 */
	public $singleton_flag;

	/**
	 * Data
	 *
	 * @var \Object
	 */
	public $data;

	/**
	 * Cached controllers
	 *
	 * @var array
	 */
	private static $cached_controllers;

	/**
	 * Cached actions
	 *
	 * @var array
	 */
	private static $cached_actions;

	/**
	 * Cached roles
	 *
	 * @var array
	 */
	private static $cached_roles;

	/**
	 * Constructor
	 */
	public function __construct() {
		// load all controllers from datasource
		if (is_null(self::$cached_controllers) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			self::$cached_controllers = \Object\ACL\Resources::getStatic('controllers', 'primary');
		}
		// find yourself
		$class = '\\' . get_called_class();
		if (!empty(self::$cached_controllers[$class])) {
			$this->title = self::$cached_controllers[$class]['name'];
			$this->description = self::$cached_controllers[$class]['description'];
			$this->icon = self::$cached_controllers[$class]['icon'];
			$this->breadcrumbs = self::$cached_controllers[$class]['breadcrumbs'];
			$this->actions = self::$cached_controllers[$class]['actions'];
			// ids
			$this->controller_id = self::$cached_controllers[$class]['id'];
			$this->method_code = \Application::get('mvc.controller_action_code');
			// acl
			foreach (['public', 'authorized', 'permission'] as $v) {
				$this->acl[$v] = self::$cached_controllers[$class]['acl_' . $v] ?? false;
			}
		}
		// view
		$this->data = new \stdClass();
	}

	/**
	 * Permitted
	 *
	 * @param array $options
	 * @return boolean
	 */
	public function permitted($options = []) : bool {
		// authorized
		if (\User::authorized()) {
			// see if controller is for authorized
			if (empty($this->acl['authorized'])) return false;
			// permissions
			if (!empty($this->acl['permission'])) {
				// determine action
				$action = $this->method_code == 'Edit' ? 'Record_Edit' : 'List_View';
				return $this->can($action);
			}
		} else {
			// we need to redirect to login controller if not authorized
			if (($options['redirect'] ?? false) && !empty($this->acl['authorized']) && empty($this->acl['public']) && !\Application::get('flag.global.__skip_session')) {
				\Request::redirect(\Object\ACL\Resources::getStatic('authorization', 'login', 'url'));
			}
			// public permission
			if (empty($this->acl['public'])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Can
	 *
	 * @param string|int $action
	 * @param array $roles
	 * @return boolean
	 */
	public function can($action, $method_code = null, $roles = null) : bool {
		if (empty($this->controller_id)) return false;
		return $this->canExtended($this->controller_id, $method_code ?? $this->method_code, $action, $roles);
	}

	/**
	 * Can (extended)
	 *
	 * @param int $resource_id
	 * @param string $method_code
	 * @param string|int $action
	 * @param array $roles
	 * @return bool
	 * @throws Exception
	 */
	public function canExtended($resource_id, $method_code, $action, $roles = null) : bool {
		// load user roles
		if (is_null($roles)) $roles = \User::roles();
		// load all actions from datasource
		if (is_null(self::$cached_actions) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			self::$cached_actions = \Object\ACL\Resources::getStatic('actions', 'primary');
		}
		if (is_string($action)) $action = self::$cached_actions[$action]['id'];
		// go though roles
		foreach ($roles as $v) {
			$temp = $this->processRole($v, $resource_id, $method_code, $action);
			if ($temp === 1) return true;
		}
		return false;
	}

	/**
	 * Process role
	 *
	 * @param string $role
	 * @param int $resource_id
	 * @param string $method_code
	 * @param int $action_id
	 * @return int
	 */
	private function processRole(string $role, int $resource_id, string $method_code, int $action_id) : int {
		// load all roles from datasource
		if (is_null(self::$cached_roles) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			self::$cached_roles = \Object\ACL\Resources::getStatic('roles', 'primary');
		}
		// if role is not found
		if (empty(self::$cached_roles[$role])) return 0;
		// see if we have permissions
		$temp = self::$cached_roles[$role]['permissions'][$resource_id][$method_code][$action_id] ?? null;
		if ($temp === 0) {
			return 1;
		} else if ($temp === 1) {
			return 2;
		}
		// super admin
		if (!empty(self::$cached_roles[$role]['super_admin'])) return 1;
		// if permission is not found we need to check parents
		if (empty(self::$cached_roles[$role]['parents'])) return 0;
		// go though parents
		foreach (self::$cached_roles[$role]['parents'] as $k => $v) {
			if (!empty($v)) continue;
			$temp = $this->processRole($k, $resource_id, $method_code, $action_id);
			if ($temp === 1) return 1;
		}
		return 0;
	}

	/**
	 * Render menu
	 *
	 * @return string
	 */
	public static function renderMenu() : string {
		if (!\Object\Error\Base::$flag_database_tenant_not_found) {
			$data = \Object\ACL\Resources::getStatic('menu', 'primary');
			return \HTML::menu([
				'brand' => \Application::get('application.layout.name'),
				'options' => $data[200] ?? [],
				'options_right' => $data[210] ?? []
			]);
		} else {
			return '';
		}
	}
}