<?php

class helper_acl {

	/**
	 * Get controller data with database
	 *
	 * @param object $controller_object
	 * @param string $controller_class
	 */
	public static function merge_data_with_db(& $controller_object, $controller_class) {
		$temp = application::get('dep.submodule.numbers.backend.system.controller');
		if ($temp) {
			$object = new numbers_backend_system_controller_model_datasource_controllers();
			$data = $object->get();
			// put all controllers to storage in application for future reuse
			application::set(['storage', 'controllers'], $data);
			// find controller and change settings on application
			$found = null;
			foreach ($data as $k => $v) {
				if ($v['sm_controller_code'] == $controller_class) {
					$found = $v;
					break;
				}
			}
			// if we have an ovarride from database
			if (!empty($found)) {
				// controller_id & action_id
				$controller_object->controller_id = $found['sm_controller_id'];
				// title
				$controller_object->title = $found['sm_controller_name'];
				// icon
				if (!empty($found['sm_controller_icon'])) {
					$controller_object->icon = $found['sm_controller_icon'];
				}
				// acl
				if (empty($controller_object->acl) || !is_array($controller_object->acl)) {
					$controller_object->acl = [];
				}
				$controller_object->acl['public'] = $found['sm_controller_acl_public'];
				$controller_object->acl['authorized'] = $found['sm_controller_acl_authorized'];
				$controller_object->acl['permission'] = $found['sm_controller_acl_permission'];
				// breadcrumbs
				$controller_object->breadcrumbs = [];
				for ($i = 1; $i <= 3; $i++) {
					if (empty($found['g' . $i . '_name'])) {
						break;
					}
					$controller_object->breadcrumbs[] = $found['g' . $i . '_name'];
				}
				$controller_object->breadcrumbs[] = $found['sm_controller_name'];
				// add actions to the controller
				$controller_object->actions = $found['actions'];
				// put new controller object back
				application::set('controller', get_object_vars($controller_object));
			}
		}
	}

	/**
	 * Validate if controller can be executed
	 *
	 * @param object $controller_object
	 * @return boolean
	 */
	public static function can_be_executed(& $controller_object, $redirect = false) {
		$authorized = session::get(['numbers', 'authorized']);
		// authorized
		if ($authorized) {
			// see if controller is for authorized
			if (empty($controller_object->acl['authorized'])) {
				return false;
			}
			// permissions
			if (!empty($controller_object->acl['permission'])) {
				if (self::$permissions == null) {
					self::handle_permissions();
				}
				// admin account can see everything
				if (self::$flag_admin) {
					// we need to put permission into controller
					$permission_list = [];
					foreach ($controller_object->actions['by_id'] as $k => $v) {
						$permission_list[$k] = true;
					}
					application::set(['controller', 'acl', 'permissions'], $permission_list);
					return true;
				}
				// see if we have this action code registered
				if (empty($controller_object->actions['by_code'][$controller_object->action['code']])) {
					return false;
				}
				// check if we have access to the controller
				if (empty($controller_object->controller_id) || empty(self::$permissions[$controller_object->controller_id])) {
					return false;
				}
				// if we have action
				$all_actions = [];
				foreach (self::$permissions[$controller_object->controller_id] as $k => $v) {
					if ($v == true) {
						$all_actions[] = $k;
					}
				}
				$merged = array_intersect($all_actions, $controller_object->actions['by_code'][$controller_object->action['code']]);
				if (empty($merged)) {
					return false;
				}
				// we need to put permission into controller
				application::set(['controller', 'acl', 'permissions'], self::$permissions[$controller_object->controller_id]);
			}
		} else {
			// we need to redirect to login controller if not authorized
			if ($redirect && !empty($controller_object->acl['authorized']) && !application::get('flag.global.__skip_session')) {
				request::redirect(application::get('flag.global.authorization.login.controller'));
			}
			// public permission
			if (empty($controller_object->acl['public'])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Storage for controllers
	 *
	 * @var type 
	 */
	public static $controllers = null;

	/**
	 * Storage for permissions
	 *
	 * @var array
	 */
	public static $permissions = null;

	/**
	 * Admin account
	 *
	 * @var boolean
	 */
	public static $flag_admin = false;

	/**
	 * Handle permission
	 */
	public static function handle_permissions() {
		$model = new object_acl_datasources();
		self::$permissions = [];
		$data = $model->get();
		foreach ($data as $k => $v) {
			foreach ($v as $k2 => $v2) {
				// if we have administrative account we allow everything
				if ($k2 == 'admin' && !empty($v2['*'])) {
					self::$flag_admin = true;
				}
				// build permission array
				foreach ($v2 as $k3 => $v3) {
					foreach ($v3 as $k4 => $v4) {
						if (!isset(self::$permissions[$k3][$k4])) {
							self::$permissions[$k3][$k4] = $v4;
						} else if (self::$permissions[$k3][$k4] == true) {
							self::$permissions[$k3][$k4] = $v4;
						}
					}
				}
			}
		}
	}

	/**
	 * Check if user can see this controller, used in menu
	 *
	 * @param int $controller_id
	 * @param int $action_id
	 * @return boolean
	 */
	public static function can_see_this_controller($controller_id, $action_id) {
		$authorized = session::get(['numbers', 'authorized']);
		if (self::$controllers == null) {
			self::$controllers = application::get(['storage', 'controllers']);
		}
		if (self::$permissions == null) {
			self::handle_permissions();
		}
		if (!empty($controller_id)) {
			if (!isset(self::$controllers[$controller_id])) {
				return false;
			}
			// authorized
			if ($authorized) {
				if (empty(self::$controllers[$controller_id]['sm_controller_acl_authorized'])) {
					return false;
				}
				// check permission
				if (!empty(self::$controllers[$controller_id]['sm_controller_acl_permission'])) {
					// admin account can see everything
					if (self::$flag_admin) {
						return true;
					}
					// if we have permission to see the controller
					if (empty(self::$permissions[$controller_id])) {
						return false;
					}
					// if we have action
					if (!empty($action_id)) {
						if (empty(self::$permissions[$controller_id][$action_id])) {
							return false;
						}
					}
				}
			} else {
				if (empty(self::$controllers[$controller_id]['sm_controller_acl_public'])) {
					return false;
				}
			}
			// if we got here means we are ok
			return true;
		}
	}
}