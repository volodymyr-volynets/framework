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
	 * Module #
	 *
	 * @var int
	 */
	public $module_id;

	/**
	 * Controller data
	 *
	 * @var array
	 */
	public $controller_data = [];

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
	private static $cached_controllers_by_ids;

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
	 * Cached modules
	 *
	 * @var array
	 */
	private static $cached_modules;

	/**
	 * Constructor
	 */
	public function __construct() {
		$class = '\\' . get_called_class();
		if ($class != '\Controller\Errors') {
			// load all controllers from datasource
			if (is_null(self::$cached_controllers) && !\Object\Error\Base::$flag_database_tenant_not_found) {
				self::$cached_controllers = \Object\ACL\Resources::getStatic('controllers', 'primary');
			}
			// load all modules from datasource
			if (is_null(self::$cached_modules) && !\Object\Error\Base::$flag_database_tenant_not_found) {
				$temp = \Object\ACL\Resources::getStatic('modules', 'primary');
				self::$cached_modules = [];
				foreach ($temp as $k => $v) {
					if (!isset(self::$cached_modules[$v['module_code']])) {
						self::$cached_modules[$v['module_code']] = [
							'module_multiple' => $v['module_multiple'],
							'module_ids' => [],
							'all_features' => []
						];
					}
					self::$cached_modules[$v['module_code']]['module_ids'][$k] = [
						'name' => $v['name'],
						'features' => $v['features']
					];
					self::$cached_modules[$v['module_code']]['all_features'] = array_unique(self::$cached_modules[$v['module_code']]['all_features'] + $v['features']);
				}
			}
		}
		// find yourself
		if (!empty(self::$cached_controllers[$class])) {
			$this->title = self::$cached_controllers[$class]['name'];
			$this->description = self::$cached_controllers[$class]['description'];
			$this->icon = self::$cached_controllers[$class]['icon'];
			$this->breadcrumbs = self::$cached_controllers[$class]['breadcrumbs'];
			$this->actions = self::$cached_controllers[$class]['actions'];
			// data
			$this->controller_data = self::$cached_controllers[$class];
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
		// determine module_id
		if (!empty($this->controller_data['module_code'])) {
			if (empty(self::$cached_modules[$this->controller_data['module_code']]['module_multiple'])) {
				$this->module_id = key(self::$cached_modules[$this->controller_data['module_code']]['module_ids']);
			} else {
				$module_id = (int) \Application::get('flag.global.__module_id');
				$modules = $this->getControllersModules();
				if (!empty($module_id) && empty($modules[$module_id])) { // see if you have correct module
					$this->module_id = null;
				} else if (empty($module_id)) { // grab first module if not specified
					$this->module_id = key($modules);
				} else {
					$this->module_id = $module_id;
				}
			}
		}
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
				$action = $this->method_code == 'Edit' ? 'Record_View' : 'List_View';
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
	 * @param string $method_code
	 * @param int $module_id
	 * @param array $roles
	 * @return boolean
	 */
	public function can($action, $method_code = null, $module_id = null, $roles = null) : bool {
		if (empty($this->controller_id)) return false;
		// module id
		if (empty($module_id)) {
			$module_id = $this->module_id;
			if (empty($module_id)) {
				Throw new \Exception('You must specify correct module #');
			}
		}
		// run permission
		return $this->canExtended($this->controller_id, $method_code ?? $this->method_code, $action, $module_id, $roles);
	}

	/**
	 * Can (extended)
	 *
	 * @param int|string $resource_id
	 * @param string $method_code
	 * @param string|int $action
	 * @param int $module_id
	 * @param array $roles
	 * @return bool
	 * @throws Exception
	 */
	public function canExtended($resource_id, $method_code, $action, $module_id = null, $roles = null) : bool {
		// rearrange controllers
		if (!isset(self::$cached_controllers_by_ids)) {
			self::$cached_controllers_by_ids = [];
			foreach (self::$cached_controllers as $k => $v) {
				self::$cached_controllers_by_ids[$v['id']] = $k;
			}
		}
		// if we got a string
		if (is_string($resource_id)) {
			$resource_id = self::$cached_controllers[$resource_id]['id'] ?? null;
		}
		// if resource is not present we return false
		if (empty(self::$cached_controllers_by_ids[$resource_id])) return false;
		// missing features
		if (!empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['missing_features'])) return false;
		// super admin
		if (\User::get('super_admin')) return true;
		// load all actions from datasource
		if (is_null(self::$cached_actions) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			self::$cached_actions = \Object\ACL\Resources::getStatic('actions', 'primary');
		}
		if (is_string($action)) $action = self::$cached_actions[$action]['id'];
		// see if we have permission overrides
		$permissions = \User::get('permissions');
		if (!empty($permissions)) {
			// process permissions
			$all_actions = $permissions[$resource_id]['AllActions'][-1] ?? [];
			$actual_action = $permissions[$resource_id][$method_code][$action] ?? [];
			$temp = array_merge_hard($all_actions, $actual_action);
			if (!empty($temp)) {
				if (!empty($module_id)) {
					$temp = $temp[$module_id] ?? null;
				} else { // find any active permision
					$temp2 = $temp;
					$temp = null;
					foreach ($temp2 as $k => $v) {
						if ($v === 0) {
							$temp = 0;
							break;
						}
					}
				}
			}
			if ($temp === 0) {
				return true;
			}
		}
		// load user roles
		if (is_null($roles)) $roles = \User::roles();
		// authorized controllers have full access
		if (empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['acl_permission']) && !empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['acl_authorized'])) {
			// if user is logged in
			if (\User::authorized()) return true;
		}
		// go through roles
		foreach ($roles as $v) {
			$temp = $this->processRole($v, $resource_id, $method_code, $action, $module_id);
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
	private function processRole(string $role, int $resource_id, string $method_code, int $action_id, $module_id = null) : int {
		// load all roles from datasource
		if (is_null(self::$cached_roles) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			self::$cached_roles = \Object\ACL\Resources::getStatic('roles', 'primary');
		}
		// if role is not found
		if (empty(self::$cached_roles[$role])) return 0;
		// process permissions
		$all_actions = self::$cached_roles[$role]['permissions'][$resource_id]['AllActions'][-1] ?? [];
		$actual_action = self::$cached_roles[$role]['permissions'][$resource_id][$method_code][$action_id] ?? [];
		$temp = array_merge_hard($all_actions, $actual_action);
		if (!empty($temp)) {
			if (!empty($module_id)) {
				$temp = $temp[$module_id] ?? null;
			} else { // find any active permision
				$temp2 = $temp;
				$temp = null;
				foreach ($temp2 as $k => $v) {
					if ($v === 0) {
						$temp = 0;
						break;
					}
				}
			}
		}
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
	 * Get controllers modules
	 *
	 * @return array
	 */
	public function getControllersModules() : array {
		$result = self::$cached_modules[$this->controller_data['module_code']]['module_ids'];
		// filter
		foreach ($result as $k => $v) {
			// determine action
			$action = $this->method_code == 'Edit' ? 'Record_View' : 'List_View';
			if (!$this->can($action, $this->method_code, $k)) {
				unset($result[$k]);
			}
		}
		// sort
		return \Object\Data\Common::buildOptions($result, ['name' => 'name'], [], ['i18n' => true]);
	}

	/**
	 * Render menu
	 *
	 * @param array $options
	 *		class
	 *		brand_logo
	 *		brand_url
	 * @return string
	 */
	public static function renderMenu(array $options = []) : string {
		if (!\Object\Error\Base::$flag_database_tenant_not_found) {
			$data = \Object\ACL\Resources::getStatic('menu', 'primary');
			// get logo image
			$brand_logo = \Object\ACL\Resources::getStatic('layout', 'logo', 'method');
			if (!empty($options['brand_logo'])) {
				$brand_logo = $options['brand_logo'];
			} else {
				$brand_logo = \Object\ACL\Resources::getStatic('layout', 'logo', 'method');
				if (!empty($brand_logo)) {
					$method = \Factory::method($brand_logo, null, true);
					$brand_logo = call_user_func_array($method, []);
				}
			}
			// logo url
			return \HTML::menu([
				'brand_name' => \Application::get('application.layout.name'),
				'brand_logo' => $brand_logo,
				'brand_url' => $options['brand_url'] ?? \Object\ACL\Resources::getStatic('postlogin_brand_url', 'url', 'url'),
				'options' => $data[200] ?? [],
				'options_right' => $data[210] ?? [],
				'class' => $options['class'] ?? null
			]);
		} else {
			return '';
		}
	}

	/**
	 * Get system module by module code
	 *
	 * @param string $module_code
	 * @return array
	 */
	public static function getSystemModuleByModuleCode(string $module_code) : array {
		return self::$cached_modules[$module_code] ?? [];
	}
}