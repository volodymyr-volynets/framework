<?php

class object_controller {

	/**
	 * Controller's title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Icon
	 *
	 * @var string 
	 */
	public $icon;

	/**
	 * Acl settings
	 *
	 * @var array
	 */
	public $acl = [
		'public' => 1,
		'authorized' => 1,
		//'permission' => 1
		//'tokens' => ['token1', 'token2'],
	];

	/**
	 * Breadcrumbs
	 *
	 * @var string 
	 */
	public $breadcrumbs = [];

	/**
	 * Cached actions
	 *
	 * @var array
	 */
	public static $cache_actions = null;

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

	/**
	 * Title
	 *
	 * @return string
	 */
	public static function title() {
		return application::get(['controller', 'title']);
	}
}