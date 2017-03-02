<?php

class object_controller {

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
	 * Action code
	 *
	 * @var string
	 */
	public $action_code;

	/**
	 * Action method
	 *
	 * @var string
	 */
	public $action_method;

	/**
	 * Bread crumbs
	 *
	 * @var array
	 */
	public $breadcrumbs = [];

	/**
	 * Singleton
	 *
	 * @var boolean
	 */
	public $singleton_flag;

	/**
	 * Controllers
	 *
	 * @var array
	 */
	private static $controllers;

	/**
	 * Constructor
	 */
	public function __construct() {
		// load all controllers from datasource
		if (is_null(self::$controllers)) {
			self::$controllers = object_acl_resources::get_static('controllers', 'primary');
		}
		// find yourself
		$class = get_called_class();
		if (!empty(self::$controllers[$class])) {
			$this->title = self::$controllers[$class]['name'];
			$this->description = self::$controllers[$class]['description'];
			$this->icon = self::$controllers[$class]['icon'];
			$this->breadcrumbs = self::$controllers[$class]['breadcrumbs'];
			$this->actions = self::$controllers[$class]['actions'];
			// acl
			foreach (['public', 'authorized', 'permission'] as $v) {
				$this->acl[$v] = self::$controllers[$class]['acl_' . $v] ?? false;
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
		if (user::authorized()) {
			// see if controller is for authorized
			if (empty($this->acl['authorized'])) {
				return false;
			}
			// permissions
			if (!empty($this->acl['permission'])) {
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
			if (($options['redirect'] ?? false) && !empty($this->acl['authorized']) && empty($this->acl['public']) && !application::get('flag.global.__skip_session')) {
				$url = object_acl_resources::get_static('authorization', 'login');
				request::redirect($url);
			}
			// public permission
			if (empty($this->acl['public'])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Whether we can perform certain action
	 *
	 * @param mixed $action_code_or_id
	 * @return boolean
	 */
	public static function can($action_code_or_id) {
		return true;
		
		// todo		
		
		if (self::$cache_actions === null) {
			self::$cache_actions = factory::model('numbers_backend_system_model_controller_actions')->get();
		}
		if (is_string($action_code_or_id)) {
			foreach (self::$cache_actions as $k => $v) {
				if ($v['sm_cntractn_code'] == $action_code_or_id) {
					$action_code_or_id = $k;
					break;
				}
			}
		}
		if (!isset(self::$cache_actions[$action_code_or_id])) {
			Throw new Exception('Unknown action!');
		}
		// public controllers have access to all actions
		$temp = application::get(['controller', 'acl']);
		if (!empty($temp['public'])) {
			return true;
		}
		// process permissions
		$permissions = application::get(['controller', 'acl', 'permissions']);
		$start = $action_code_or_id;
		do {
			// see if we have permission
			if (empty($permissions[$start])) {
				break;
			}
			// we need to check permission on a parent
			if (!empty(self::$cache_actions[$start]['sm_cntractn_parent_id'])) {
				$start = self::$cache_actions[$start]['sm_cntractn_parent_id'];
			} else {
				// exit if there's no parent
				return true;
			}
		} while(1);
		return false;
	}
}