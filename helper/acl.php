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
	public static function can_be_executed(& $controller_object) {
		$authorized = session::get(['numbers', 'authorized']);
		// authorized
		if ($authorized) {
			if (empty($controller_object->acl['authorized'])) {
				return false;
			}
		} else {
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
	 * Check if user can see this controller, used in menu
	 *
	 * @param int $controller_id
	 * @return boolean
	 */
	public static function can_see_this_controller($controller_id) {
		$authorized = session::get(['numbers', 'authorized']);
		if (self::$controllers == null) {
			self::$controllers = application::get(['storage', 'controllers']);
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