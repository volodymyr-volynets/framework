<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Cmd;
use Object\ACL\Resources;
use Object\Controller;
use Object\Error\Base;

class Can
{
    /**
     * Submodule exists
     *
     * @param string $submodule
     * @return bool
     */
    public static function submoduleExists(string $submodule): bool
    {
        return Application::get(str_replace(['/', '\\', '.'], '\\', $submodule . '\\' . 'Base'), ['submodule_exists' => true]);
    }

    /**
     * System module exists
     *
     * @param string $submodule
     * @return bool
     */
    public static function systemModuleExists(string $module_code): bool
    {
        $result = Controller::getSystemModuleByModuleCode($module_code);
        return !empty($result);
    }

    /**
     * System feature exists
     *
     * @param string $feature_code
     * @param int|null $module_id
     * @return bool
     */
    public static function systemFeatureExists(string $feature_code, $module_id = null): bool
    {
        if (!isset($module_id)) {
            $module_id = Application::$controller->module_id;
        }
        $temp = explode('::', $feature_code);
        $result = Controller::getSystemModuleByModuleCode($temp[0]);
        if (isset($result['module_ids'][$module_id]['features'])) {
            return in_array($feature_code, $result['module_ids'][$module_id]['features']);
        }
        if (empty($result['module_multiple'])) {
            return in_array($feature_code, $result['all_features']);
        }
        return false;
    }

    /**
     * System features exist
     *
     * @param array $feature_codes
     * @param int|null $module_id
     * @return bool
     */
    public static function systemFeaturesExist(array $feature_codes, $module_id = null): bool
    {
        $not_found = false;
        foreach ($feature_codes as $v) {
            if (!self::systemFeatureExists($v, $module_id)) {
                $not_found = true;
                break;
            }
        }
        return !$not_found;
    }

    /**
     * User feature exists
     *
     * @param string $feature_code
     * @param int|null $module_id
     * @return bool
     */
    public static function userFeatureExists(string $feature_code, $module_id = null): bool
    {
        if (!isset($module_id)) {
            $result = Controller::getSystemModuleByModuleCode($feature_code[0] . $feature_code[1]);
            $module_id = key($result['module_ids']);
        }
        // fetures third
        if (is_null(Controller::$cached_features) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_features = Resources::getStatic('features', 'primary');
        }
        // super admin
        if (User::get('super_admin')) {
            if (!empty(Controller::$cached_features[$feature_code]) && empty(Controller::$cached_features[$feature_code]['prohibitive'])) {
                return true;
            }
        }
        // user first
        $features = User::get('features');
        if (!empty($features)) {
            $result = self::userFeatureExistsOne($features, $feature_code, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // roles second
        if (is_null(Controller::$cached_roles) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_roles = Resources::getStatic('roles', 'primary');
        }
        foreach (User::roles() as $v) {
            $result = self::userFeatureExistsRole($v, $feature_code, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // teams last
        if (is_null(Controller::$cached_teams) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_teams = Resources::getStatic('roles', 'teams');
        }
        foreach (User::teams() as $v) {
            $features = Controller::$cached_teams[$v]['features'] ?? null;
            if (!empty($features)) {
                $result = self::userFeatureExistsOne($features, $feature_code, $module_id);
                if ($result == 1) {
                    return true;
                } elseif ($result == 2) { // disabled feature
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * User feature exists one
     *
     * @param array $features
     * @param string $feature_code
     * @param int $module_id
     * @return int
     */
    private static function userFeatureExistsOne(array $features, string $feature_code, $module_id = null): int
    {
        $result = $features[$feature_code][$module_id] ?? null;
        if ($result === 0) {
            return 1;
        } elseif ($result === 1) {
            return 2;
        }
        return 0;
    }

    /**
     * User feature exists one role
     *
     * @param string $role
     * @param string $feature_code
     * @param int $module_id
     * @return int
     */
    private static function userFeatureExistsRole(string $role, string $feature_code, $module_id = null): int
    {
        if (!empty(Controller::$cached_roles[$role]['features'])) {
            $result = self::userFeatureExistsOne(Controller::$cached_roles[$role]['features'], $feature_code, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // super admin
        if (!empty(Controller::$cached_roles[$role]['super_admin'])) {
            if (empty(Controller::$cached_features[$feature_code]['prohibitive'])) {
                return 1;
            }
        }
        // if permission is not found we need to check parents
        if (empty(Controller::$cached_roles[$role]['parents'])) {
            return 0;
        }
        // go though parents
        foreach (Controller::$cached_roles[$role]['parents'] as $k => $v) {
            if (!empty($v)) {
                continue;
            }
            $result = self::userFeatureExistsRole($k, $feature_code, $module_id);
            if ($result === 1) {
                return 1;
            } elseif ($result === 2) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * File exist in path
     *
     * @param string $filename
     * @return mixed
     */
    public static function fileExistsInPath(string $filename)
    {
        $paths = explode(';', str_replace(':', ';', get_include_path()));
        foreach ($paths as $v) {
            if (file_exists($v . DIRECTORY_SEPARATOR . $filename)) {
                return $v . DIRECTORY_SEPARATOR . $filename;
            }
        }
        return false;
    }

    /**
     * User feature exists
     *
     * @param string|int $flag
     * @param string|int $action
     * @param int|null $module_id
     * @return bool
     */
    public static function userFlagExists($flag, $action, $module_id = null): bool
    {
        if (Cmd::isCli()) {
            return false;
        }
        if (!isset($module_id)) {
            $module_id = Application::$controller->module_id;
        }
        // load all actions from datasource
        if (is_null(Controller::$cached_actions) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_actions = Resources::getStatic('actions', 'primary');
        }
        // load all flags
        if (is_null(Controller::$cached_flags) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_flags = Resources::getStatic('flags', 'primary');
        }
        if (is_string($flag)) {
            $flag = Controller::$cached_flags[$flag]['id'];
        }
        if (is_string($action)) {
            $action = Controller::$cached_actions[$action]['id'];
        }
        // user first
        $flags = User::get('flags');
        if (!empty($flags)) {
            $result = self::userFlagExistsOne($flags, $flag, $action, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // roles second
        if (is_null(Controller::$cached_roles) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_roles = Resources::getStatic('roles', 'primary');
        }
        foreach (User::roles() as $v) {
            $result = self::userFlagExistsRole($v, $flag, $action, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // teams last
        if (is_null(Controller::$cached_teams) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_teams = Resources::getStatic('roles', 'teams');
        }
        foreach (User::teams() as $v) {
            $flags = Controller::$cached_teams[$v]['flags'] ?? null;
            if (!empty($features)) {
                $result = self::userFeatureExistsOne($flags, $flag, $action, $module_id);
                if ($result == 1) {
                    return true;
                } elseif ($result == 2) { // disabled feature
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * User flag exists one
     *
     * @param array $flags
     * @param int $flag_id
     * @param int $action_id
     * @param int $module_id
     * @return int
     */
    private static function userFlagExistsOne(array $flags, int $flag_id, int $action_id, $module_id = null): int
    {
        $result = $flags[$flag_id][$action_id][$module_id] ?? null;
        if ($result === 0) {
            return 1;
        } elseif ($result === 1) {
            return 2;
        }
        return 0;
    }

    /**
     * User flag exists one role
     *
     * @param string $role
     * @param string $feature_code
     * @param int $module_id
     * @return int
     */
    private static function userFlagExistsRole(string $role, int $flag_id, int $action_id, $module_id = null): int
    {
        if (!empty(Controller::$cached_roles[$role]['flags'])) {
            $result = self::userFlagExistsOne(Controller::$cached_roles[$role]['flags'], $flag_id, $action_id, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // if permission is not found we need to check parents
        if (empty(Controller::$cached_roles[$role]['parents'])) {
            return 0;
        }
        // go though parents
        foreach (Controller::$cached_roles[$role]['parents'] as $k => $v) {
            if (!empty($v)) {
                continue;
            }
            $result = self::userFlagExistsRole($k, $flag_id, $action_id, $module_id);
            if ($result === 1) {
                return 1;
            } elseif ($result === 2) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * User notification exists
     *
     * @param string $notification_code
     * @param int|null $module_id
     * @return bool
     */
    public static function userNotificationExists(string $notification_code, $module_id = null): bool
    {
        if (!isset($module_id)) {
            $module_id = Application::$controller->module_id;
        }
        // fetures third
        if (is_null(Controller::$cached_features) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_features = Resources::getStatic('features', 'primary');
        }
        // user first
        $features = User::get('notifications');
        if (!empty($features)) {
            $result = self::userNotificationExistsOne($features, $notification_code, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // roles second
        if (is_null(Controller::$cached_roles) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_roles = Resources::getStatic('roles', 'primary');
        }
        foreach (User::roles() as $v) {
            $result = self::userNotificationExistsRole($v, $notification_code, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // teams last
        if (is_null(Controller::$cached_teams) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_teams = Resources::getStatic('roles', 'teams');
        }
        foreach (User::teams() as $v) {
            $features = Controller::$cached_teams[$v]['notifications'] ?? null;
            if (!empty($features)) {
                $result = self::userNotificationExistsOne($features, $notification_code, $module_id);
                if ($result == 1) {
                    return true;
                } elseif ($result == 2) { // disabled feature
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * User notification exists one
     *
     * @param array $features
     * @param string $feature_code
     * @param int $module_id
     * @return int
     */
    private static function userNotificationExistsOne(array $features, string $feature_code, $module_id = null): int
    {
        $result = $features[$feature_code][$module_id] ?? null;
        if ($result === 0) {
            return 1;
        } elseif ($result === 1) {
            return 2;
        }
        return 0;
    }

    /**
     * User feature exists one role
     *
     * @param string $role
     * @param string $feature_code
     * @param int $module_id
     * @return int
     */
    private static function userNotificationExistsRole(string $role, string $feature_code, $module_id = null): int
    {
        if (!empty(Controller::$cached_roles[$role]['notifications'])) {
            $result = self::userFeatureExistsOne(Controller::$cached_roles[$role]['notifications'], $feature_code, $module_id);
            if ($result == 1) {
                return true;
            } elseif ($result == 2) { // disabled feature
                return false;
            }
        }
        // if permission is not found we need to check parents
        if (empty(Controller::$cached_roles[$role]['parents'])) {
            return 0;
        }
        // go though parents
        foreach (Controller::$cached_roles[$role]['parents'] as $k => $v) {
            if (!empty($v)) {
                continue;
            }
            $result = self::userNotificationExistsRole($k, $feature_code, $module_id);
            if ($result === 1) {
                return 1;
            } elseif ($result === 2) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * User is owner
     *
     * @param string $owner_code
     * @return bool
     */
    public static function userIsOwner(string $owner_code): bool
    {
        if (is_null(User::$cached_owners) && !Base::$flag_database_tenant_not_found) {
            User::$cached_owners = Resources::getStatic('owners', 'primary');
        }
        if (empty(User::$cached_owners[$owner_code])) {
            return false;
        }
        $organizations = User::get('organizations');
        $roles = User::get('role_ids');
        foreach (User::$cached_owners[$owner_code] as $k => $v) {
            foreach ($v as $k2 => $v2) {
                if (in_array($v2['role_id'], $roles ?? []) && in_array($v2['organization_id'], $organizations ?? [])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if action is authorized in controller
     *
     * @param string $controller
     * @param string $method_code
     * @param string $action
     * @param int|null $module_id
     * @return boolean
     */
    public static function controllerActionPermitted(string $controller, string $method_code, string $action, $module_id = null)
    {
        $controller = str_replace('/', '\\', $controller);
        if (empty($module_id)) {
            $module_code = Controller::$cached_controllers[$controller]['module_code'] ?? null;
            $module_id = key(Controller::$cached_modules[$module_code]['module_ids'] ?? []);
        }
        $controller_id = Controller::$cached_controllers[$controller]['id'] ?? null;
        return Application::$controller->canExtended($controller_id, $method_code, $action, $module_id);
    }

    /**
     * Check if action is authorized in API
     *
     * @param string $controller
     * @param string $method_code
     * @param int|null $module_id
     * @return boolean
     */
    public static function apiActionPermitted(string $controller, string $method_code, $module_id = null)
    {
        $controller = str_replace('/', '\\', $controller);
        if (empty($module_id)) {
            $module_code = Controller::$cached_controllers[$controller]['module_code'] ?? null;
            $module_id = key(Controller::$cached_modules[$module_code]['module_ids'] ?? []);
        }
        $controller_id = Controller::$cached_controllers[$controller]['id'] ?? null;
        return Controller::canAPIExtended($controller_id, $method_code, $module_id);
    }
}
