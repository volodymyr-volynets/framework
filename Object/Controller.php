<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object;

use Object\ACL\Resources;
use Object\Controller\Model\UsageCodes;
use Object\Data\Common;
use Object\Error\Base;
use Controller\Errors;
use Object\Enum\PolicyReturnTypes;
use Object\Enum\PolicyProcessingTypes;

#[\AllowDynamicProperties]
class Controller
{
    /**
     * Title
     *
     * @var string
     */
    public $title;

    /**
     * Route
     *
     * @var \Route|null
     */
    public ?\Route $route = null;

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
     * Controller Override
     *
     * @var int
     */
    public $override_controller_id;

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
     * @var Object
     */
    public $data;

    /**
     * Cached controllers
     *
     * @var array
     */
    public static $cached_controllers;
    public static $cached_controllers_by_ids;
    public static $cached_controllers_by_names;

    /**
     * Cached actions
     *
     * @var array
     */
    public static $cached_actions;

    /**
     * Cached flags
     *
     * @var array
     */
    public static $cached_flags;

    /**
     * Cached roles
     *
     * @var array
     */
    public static $cached_roles;

    /**
     * Cached teams
     *
     * @var array
     */
    public static $cached_teams;

    /**
     * Cached policies
     *
     * @var array
     */
    public static $cached_policies;

    /**
     * Cached policy groups
     *
     * @var array
     */
    public static $cached_policy_groups;

    /**
     * Cached settings
     *
     * @var array
     */
    public static $cached_settings;

    /**
     * Cached combined policies
     *
     * @var array
     */
    public static $cached_combined_policies;

    /**
     * Cached groupped policies
     *
     * @var array
     */
    public static $cached_groupped_policies;

    /**
     * Cached modules
     *
     * @var array
     */
    public static $cached_modules;

    /**
     * Cached features
     *
     * @var array
     */
    public static $cached_features;

    /**
     * Usage actions
     *
     * @var array
     */
    private static $usage_actions = [];

    /**
     * Cached can requests
     *
     * @var array
     */
    private $cached_can_requests = [];
    private $cached_can_subresource_requests = [];

    /**
     * Cached sub-resources
     *
     * @var array
     */
    private static $cached_subresources;

    /**
     * Main content class
     *
     * @var string
     */
    public static $main_content_class = 'container';

    /**
     * Middleware
     *
     * @var array
     */
    protected array $middleware = [];

    /**
     * Constructor
     *
     * @param array $options
     *      string class
     */
    public function __construct(array $options = [])
    {
        $class = $options['class'] ?? ('\\' . ltrim(get_called_class(), '\\'));
        if ($class != Errors::class) {
            // load all controllers from datasource
            if (is_null(self::$cached_controllers) && !Base::$flag_database_tenant_not_found) {
                self::$cached_controllers = Resources::getStatic('controllers', 'primary');
            }
            // load all modules from datasource
            if (is_null(self::$cached_modules) && !Base::$flag_database_tenant_not_found) {
                $temp = Resources::getStatic('modules', 'primary');
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
            // controller data
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
        // fix module_code
        if (empty($this->controller_data['module_code'])) {
            if (!empty(self::$cached_modules['AN'])) {
                $this->controller_data['module_code'] = 'AN';
            } else {
                $this->controller_data['module_code'] = 'SM';
            }
        }
        // determine module_id
        if (!empty(self::$cached_modules)) {
            if (empty(self::$cached_modules[$this->controller_data['module_code']]['module_multiple'])) {
                $this->module_id = key(self::$cached_modules[$this->controller_data['module_code']]['module_ids']);
            } else {
                $module_id = (int) \Application::get('flag.global.__module_id');
                $modules = $this->getControllersModules();
                if (!empty($module_id) && empty($modules[$module_id])) { // see if you have correct module
                    $this->module_id = null;
                } elseif (empty($module_id)) { // grab first module if not specified
                    $this->module_id = key($modules);
                } else {
                    $this->module_id = $module_id;
                }
            }
        }
        // add usages
        if (!empty(self::$cached_modules) && !empty(self::$cached_modules[$this->controller_data['module_code']])) {
            $this->controller_data['module_name'] = $module_name = self::$cached_modules[$this->controller_data['module_code']]['module_ids'][$this->module_id]['name'] ?? 'Unknown';
            $__menu_id = \Application::get('flag.global.__menu_id');
            if (!empty($__menu_id)) {
                $__menu_id = (int) $__menu_id;
                $data = Resources::getStatic('menu', 'usage');
                if (!empty($data) && !empty($data[$__menu_id])) {
                    $this->addUsageAction('menu_item_click', [
                        'replace' => [
                            '[menu_name]' => $data[$__menu_id]['name'],
                            '[module_name]' => $module_name,
                        ],
                    ]);
                }
            }
            $this->addUsageAction('controller_opened', [
                'replace' => [
                    '[page_name]' => $this->title ?? $class,
                    '[module_name]' => $module_name,
                ],
            ]);
        } else {
            $this->controller_data['module_name'] = 'A/N Application';
        }
    }

    /**
     * Middleware
     *
     * @param string $name
     * @param array $options
     * @return Controller
     */
    public function middleware(string $name, array $options = []): Controller
    {
        $middleware = Middleware::getMiddlewareStatic($name);
        $this->middleware[$name] = $middleware;
        $this->middleware[$name]['name'] = $middleware['name'];
        $this->middleware[$name]['check'] = $middleware['check'];
        $this->middleware[$name]['options'] = $options;
        return $this;
    }

    /**
     * Check ACL
     *
     * @param string $name
     * @param string $check - Before or After
     * @return bool
     */
    public function checkMiddleware(string $check = 'Before'): bool
    {
        $middleware = new \Array2($this->middleware);
        $middleware->filter(function ($value) use ($check) {
            if (in_array($check, $value['check'])) {
                return true;
            }
        });
        return Middleware::runMiddlewareStatic($middleware->toArray(), $check);
    }

    /**
     * Permitted
     *
     * @param array $options
     * @return boolean
     */
    public function permitted($options = []): bool
    {
        // authorized
        if (\User::authorized()) {
            // see if controller is for authorized
            if (empty($this->acl['authorized'])) {
                return false;
            }
            // permissions
            if (!empty($this->acl['permission'])) {
                // API controllers
                if (is_subclass_of($this, 'Object\Controller\API')) {
                    return $this->canAPI($this->method_code);
                } else {
                    // determine action
                    $action2 = '';
                    $method_code = null;
                    switch ($this->method_code) {
                        case 'Edit': $action = 'Record_View';
                            break;
                        case 'PDF': $action = 'Record_View';
                            $method_code = 'Edit';
                            break;
                        case 'Index': $action = 'List_View';
                            $action2 = 'Report_View';
                            break;
                        case 'Activate': $action = 'Activate_Data';
                            break;
                        case 'Import': $action = 'Import_Records';
                            break;
                            // if we need to alter menu name
                        case 'JsonMenuName':
                        case 'JsonMenuName2':
                        case 'JsonMenuName3':
                        case 'JsonMenuName4':
                        case 'JsonMenuName5':
                            foreach (['Edit' => 'Record_View', 'Index' => 'List_View', 'Activate' => 'Activate_Data'] as $k => $v) {
                                if ($this->can($v, $k)) {
                                    return true;
                                }
                            }
                            return false;
                            break;
                    }
                    if (!empty($action)) {
                        $result = $this->can($action, $method_code);
                        if ($result) {
                            return $result;
                        } elseif (!empty($action2)) {
                            return $this->can($action2, $method_code);
                        }
                        return false;
                    } else {
                        return false;
                    }
                }
            }
        } else {
            // we need to redirect to login controller if not authorized
            if (($options['redirect'] ?? false) && !empty($this->acl['authorized']) && empty($this->acl['public']) && !\Application::get('flag.global.__skip_session')) {
                // API as not authorized
                if (is_subclass_of($this, 'Object\Controller\API')) {
                    $this->handleOutput(['success' => false, 'error' => ['Unauthorized']]);
                }
                \Request::redirect(Resources::getStatic('authorization', 'login', 'url'));
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
     * @return boolean
     */
    public function can($action, $method_code = null, $module_id = null): bool
    {
        if (empty($this->controller_id) && empty(\Application::$controller->override_controller_id)) {
            return false;
        }
        // module id
        if (empty($module_id)) {
            $module_id = $this->module_id;
            if (empty($module_id)) {
                throw new \Exception('You must specify correct module #');
            }
        }
        // run permission
        return $this->canExtended(\Application::$controller->override_controller_id ?? $this->controller_id, $method_code ?? $this->method_code, $action, $module_id);
    }

    /**
     * Can (API)
     *
     * @param string|int $action
     * @param string $method_code
     * @param int $module_id
     * @return boolean
     */
    public function canAPI($method_code, $module_id = null): bool
    {
        if (empty($this->controller_id) && empty(\Application::$controller->override_controller_id)) {
            return false;
        }
        // module id
        if (empty($module_id)) {
            $module_id = $this->module_id;
            if (empty($module_id)) {
                throw new \Exception('You must specify correct module #');
            }
        }
        // run permission
        return $this->canAPIExtended(\Application::$controller->override_controller_id ?? $this->controller_id, $method_code ?? $this->method_code, $module_id);
    }

    /**
     * Can (Cached)
     *
     * @param int|string $action
     * @param string|null $method_code
     * @return boolean
     */
    public function canCached($action, $method_code = null): bool
    {
        $method_code = $method_code ?? $this->method_code;
        if (!isset($this->cached_can_requests[$method_code][$action])) {
            $this->cached_can_requests[$method_code][$action] = $this->can($action, $method_code);
        }
        return $this->cached_can_requests[$method_code][$action];
    }

    /**
     * Can (Multiple)
     *
     * @param array|string $subresources
     * @param string|int $action
     * @return bool
     */
    public function canMultiple($actions): bool
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }
        foreach ($actions as $v) {
            if (!$this->canCached($v[0], $v[1])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Can sub-resource
     *
     * @param int|string $subresource
     * @param int|string $action
     * @param int $module_id
     * @return bool
     * @throws \Exception
     */
    public function canSubresource($subresource, $action, $module_id = null): bool
    {
        if (empty($this->controller_id) && empty(\Application::$controller->override_controller_id)) {
            return false;
        }
        // module id
        if (empty($module_id)) {
            $module_id = $this->module_id;
            if (empty($module_id)) {
                throw new \Exception('You must specify correct module #');
            }
        }
        // run permission
        $controller_id = \Application::$controller->override_controller_id ?? $this->controller_id;
        return $this->canSubresourceExtended($controller_id, $subresource, $action, $module_id);
    }

    /**
     * Can sub-resource (Cached)
     *
     * @param int|string $action
     * @param string|null $method_code
     * @return boolean
     */
    public function canSubresourceCached($subresource, $action): bool
    {
        $user_id = \User::getUser() ?? \User::id() ?? null;
        if (!isset($this->cached_can_subresource_requests[$user_id][$subresource][$action])) {
            $this->cached_can_subresource_requests[$user_id][$subresource][$action] = $this->canSubresource($subresource, $action);
        }
        return $this->cached_can_subresource_requests[$user_id][$subresource][$action];
    }

    /**
     * Can sub-resource (Multiple)
     *
     * @param array|string $subresources
     * @param string|int $action
     * @return bool
     */
    public function canSubresourceMultiple($subresources, $action): bool
    {
        if (!is_array($subresources)) {
            $subresources = [$subresources];
        }
        foreach ($subresources as $v) {
            if (!$this->canSubresourceCached($v, $action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Can sub-resource (extended)
     *
     * @param int|string $resource_id
     * @param int|string $subresource
     * @param string|int $action
     * @param int $module_id
     * @return bool
     * @throws Exception
     */
    public function canSubresourceExtended($resource_id, $subresource, $action, $module_id = null): bool
    {
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
        if (empty(self::$cached_controllers_by_ids[$resource_id])) {
            return false;
        }
        // missing features
        if (!empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['missing_features'])) {
            return false;
        }
        // load all actions from datasource
        if (is_null(self::$cached_actions) && !Base::$flag_database_tenant_not_found) {
            self::$cached_actions = Resources::getStatic('actions', 'primary');
        }
        // super admin
        if (\User::get('super_admin')) {
            // prohibitive actions
            if (empty(self::$cached_actions[$action]['prohibitive'])) {
                return true;
            }
        }
        if (is_string($action)) {
            $action = self::$cached_actions[$action]['id'];
        }
        // load all sub-resources from datasource
        if (is_null(self::$cached_subresources) && !Base::$flag_database_tenant_not_found) {
            self::$cached_subresources = Resources::getStatic('subresources', 'primary');
        }
        if (is_string($subresource)) {
            $subresource = self::$cached_subresources[$subresource]['id'];
        }
        // see if we have subresources overrides
        $subresources = \User::get('subresources');
        if (!empty($subresources)) {
            // process permissions
            $all_actions = $subresources[$resource_id][$subresource][-1] ?? [];
            $actual_action = $subresources[$resource_id][$subresource][$action] ?? [];
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
            } elseif (!empty($temp)) {
                return false;
            }
        }
        // go through roles
        foreach (\User::roles() as $v) {
            $temp = $this->processSubresourceRole($v, $resource_id, $subresource, $action, $module_id);
            if ($temp === 1) {
                return true;
            } elseif ($temp === 2) {
                return false;
            }
        }
        // go through teams
        foreach (\User::teams() as $v) {
            $temp = $this->processSuresourceTeam($v, $resource_id, $subresource, $action, $module_id);
            if ($temp === 1) {
                return true;
            } elseif ($temp === 2) {
                return false;
            }
        }
        // if we have all actions enabled we need to allow through
        if ($this->can('All_Actions', 'Edit')) {
            return true;
        }
        return false;
    }

    /**
     * Can (extended)
     *
     * @param int|string $resource_id
     * @param string $method_code
     * @param string|int $action
     * @param int $module_id
     * @return bool
     * @throws Exception
     */
    public function canExtended($resource_id, $method_code, $action, $module_id = null): bool
    {
        // rearrange controllers
        if (!isset(self::$cached_controllers_by_ids)) {
            self::$cached_controllers_by_ids = [];
            foreach (self::$cached_controllers ?? [] as $k => $v) {
                self::$cached_controllers_by_ids[$v['id']] = $k;
            }
        }
        // if we got a string
        if (is_string($resource_id)) {
            $resource_id = self::$cached_controllers[$resource_id]['id'] ?? null;
        }
        // if resource is not present we return false
        if (empty(self::$cached_controllers_by_ids[$resource_id])) {
            return false;
        }
        // missing features
        if (!empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['missing_features'])) {
            return false;
        }
        // super admin
        if (\User::get('super_admin')) {
            return true;
        }
        // policies
        $this->preloadPolicies();
        $policies = array_key_column_search(self::$cached_combined_policies, 'type', 'SM::PAGE_ACCESS_GROUP');
        if (count($policies) > 0) {
            $model = \Factory::model(current($policies)['model'], true);
            $allow = $model->computeAllPolicies(PolicyProcessingTypes::AnyAllow, self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]], $policies);
            if ($allow == PolicyReturnTypes::Deny) {
                return false;
            } elseif ($allow == PolicyReturnTypes::Allow) {
                return true;
            }
        }
        // load all actions from datasource
        if (is_null(self::$cached_actions) && !Base::$flag_database_tenant_not_found) {
            self::$cached_actions = Resources::getStatic('actions', 'primary');
        }
        if (is_string($action)) {
            $action = self::$cached_actions[$action]['id'];
        }
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
            } elseif ($temp === 1) {
                return false;
            }
        }
        // load user roles
        $roles = \User::roles();
        // authorized controllers have full access
        if (empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['acl_permission']) && !empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['acl_authorized'])) {
            // if user is logged in
            if (\User::authorized()) {
                return true;
            }
        }
        // go through roles
        foreach ($roles as $v) {
            $temp = $this->processRole($v, $resource_id, $method_code, $action, $module_id);
            if ($temp === 1) {
                return true;
            } elseif ($temp === 2) {
                return false;
            }
        }
        // go through teams
        foreach (\User::teams() as $v) {
            $temp = $this->processTeam($v, $resource_id, $method_code, $action, $module_id);
            if ($temp === 1) {
                return true;
            } elseif ($temp === 2) {
                return false;
            }
        }
        return false;
    }

    /**
     * Preload policies & policy groups
     *
     * @return void
     */
    public function preloadPolicies(): void
    {
        // if this is generated it means we do not need to process policies
        if (isset(self::$cached_combined_policies)) {
            return;
        }
        if (!isset(self::$cached_policies) && !Base::$flag_database_tenant_not_found) {
            self::$cached_policies = Resources::getStatic('roles', 'policies');
        }
        if (!isset(self::$cached_policy_groups) && !Base::$flag_database_tenant_not_found) {
            self::$cached_policy_groups = Resources::getStatic('roles', 'policy_groups');
        }
        if (!isset(self::$cached_roles) && !Base::$flag_database_tenant_not_found) {
            self::$cached_roles = Resources::getStatic('roles', 'primary');
        }
        if (!isset(self::$cached_teams) && !Base::$flag_database_tenant_not_found) {
            self::$cached_teams = Resources::getStatic('roles', 'teams');
        }
        if (!isset(self::$cached_settings) && !Base::$flag_database_tenant_not_found) {
            self::$cached_settings = Resources::getStatic('roles', 'settings');
        }
        // traverse all cached presets
        self::$cached_combined_policies = [];
        self::$cached_groupped_policies = [];
        $settings = current(self::$cached_settings);
        $this->traversePolicies('settings', $settings['policies'] ?? [], self::$cached_combined_policies);
        $this->traversePolicyGroups('settings', $settings['policy_groups'] ?? [], self::$cached_combined_policies);
        $this->traversePolicies('user', \User::get('policies') ?? [], self::$cached_combined_policies);
        $this->traversePolicyGroups('user', \User::get('policy_groups') ?? [], self::$cached_combined_policies);
        foreach (\User::roles() as $v) {
            $this->traverseRolePolicies($v, 'roles', self::$cached_combined_policies);
        }
        foreach (\User::teams() as $v) {
            $this->traversePolicies('teams_' . $v, self::$cached_teams[$v]['policies'] ?? [], self::$cached_combined_policies);
            $this->traversePolicyGroups('teams_' . $v, self::$cached_teams[$v]['policy_groups'] ?? [], self::$cached_combined_policies);
        }
        //print_r2(self::$cached_combined_policies);
        // todo after testing remove self::$cached_groupped_policies
        //print_r2(self::$cached_groupped_policies);
    }

    /**
     * Traverse role policies
     *
     * @param string $role
     * @param string $source
     * @param array $result
     */
    private function traverseRolePolicies(string $role, string $source, array & $result): void
    {
        $this->traversePolicies($source . '_' . $role, self::$cached_roles[$role]['policies'] ?? [], $result);
        $this->traversePolicyGroups($source . '_' . $role, self::$cached_roles[$role]['policy_groups'] ?? [], $result);
        if (!empty(self::$cached_roles[$role]['parents'])) {
            foreach (self::$cached_roles[$role]['parents'] as $k => $v) {
                $this->traverseRolePolicies($k, $source, $result);
            }
        }
    }

    /**
     * Traverse policies
     *
     * @param string $source
     * @param mixed $policies
     * @param array $result
     * @return bool
     */
    public function traversePolicies(string $source, mixed $policies, array & $result): bool
    {
        if (empty($policies)) {
            return false;
        }
        foreach ($policies as $k => $v) {
            foreach ($v as $k2 => $v2) {
                $result[$k . '_' . $k2] = self::$cached_policies[$k][$k2];
                self::$cached_groupped_policies[$source][$k . '_' . $k2]['name'] = $result[$k . '_' . $k2]['name'];
            }
        }
        return true;
    }

    /**
     * Traverse policies
     *
     * @param string $source
     * @param mixed $policy_groups
     * @param array $result
     * @return bool
     */
    public function traversePolicyGroups(string $source, mixed $policy_groups, array & $result): bool
    {
        if (empty($policy_groups)) {
            return false;
        }
        foreach ($policy_groups as $k => $v) {
            foreach ($v as $k2 => $v2) {
                self::$cached_groupped_policies[$source][$k . '_GROUP_' . self::$cached_policy_groups[$k][$k2]['code']]['name'] = self::$cached_policy_groups[$k][$k2]['name'];
                if (!empty(self::$cached_policy_groups[$k][$k2]['policies'])) {
                    $this->traversePolicies($source, self::$cached_policy_groups[$k][$k2]['policies'], self::$cached_combined_policies);
                }
                if (!empty(self::$cached_policy_groups[$k][$k2]['policy_groups'])) {
                    $this->traversePolicyGroups($source, self::$cached_policy_groups[$k][$k2]['policy_groups'], self::$cached_combined_policies);
                }
            }
        }
        return true;
    }

    /**
     * Can (extended, API)
     *
     * @param int|string $resource_id
     * @param string $method_code
     * @param int $module_id
     * @return bool
     * @throws Exception
     */
    public static function canAPIExtended($resource_id, $method_code, $module_id = null): bool
    {
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
        if (empty(self::$cached_controllers_by_ids[$resource_id])) {
            return false;
        }
        // missing features
        if (!empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['missing_features'])) {
            return false;
        }
        // super admin
        if (\Application::get('flag.numbers.framework.api.allow_super_admin') && \User::get('super_admin')) {
            return true;
        }
        // see if we have permission overrides
        $apis = \User::get('apis');
        if (!empty($apis)) {
            // process permissions
            $all_actions = $apis[$resource_id]['AllActions'] ?? [];
            $actual_action = $apis[$resource_id][$method_code] ?? [];
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
            } elseif ($temp === 1) {
                return false;
            }
        }
        // load user roles
        $roles = \User::roles();
        // authorized controllers have full access
        if (empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['acl_permission']) && !empty(self::$cached_controllers[self::$cached_controllers_by_ids[$resource_id]]['acl_authorized'])) {
            // if user is logged in
            if (\User::authorized()) {
                return true;
            }
        }
        // go through roles
        foreach ($roles as $v) {
            $temp = self::processAPIRole($v, $resource_id, $method_code, $module_id);
            if ($temp === 1) {
                return true;
            } elseif ($temp === 2) {
                return false;
            }
        }
        // go through teams
        foreach (\User::teams() as $v) {
            $temp = self::processAPITeam($v, $resource_id, $method_code, $module_id);
            if ($temp === 1) {
                return true;
            } elseif ($temp === 2) {
                return false;
            }
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
    private function processRole(string $role, int $resource_id, string $method_code, int $action_id, $module_id = null): int
    {
        // load all roles from datasource
        if (is_null(self::$cached_roles) && !Base::$flag_database_tenant_not_found) {
            self::$cached_roles = Resources::getStatic('roles', 'primary');
        }
        // if role is not found
        if (empty(self::$cached_roles[$role])) {
            return 0;
        }
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
        } elseif ($temp === 1) {
            return 2;
        }
        // super admin
        if (!empty(self::$cached_roles[$role]['super_admin'])) {
            return 1;
        }
        // if permission is not found we need to check parents
        if (empty(self::$cached_roles[$role]['parents'])) {
            return 0;
        }
        // go though parents
        foreach (self::$cached_roles[$role]['parents'] as $k => $v) {
            if (!empty($v)) {
                continue;
            }
            $temp = $this->processRole($k, $resource_id, $method_code, $action_id);
            if ($temp === 1) {
                return 1;
            } elseif ($temp === 2) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * Process role (API)
     *
     * @param string $role
     * @param int $resource_id
     * @param string $method_code
     * @return int
     */
    private static function processAPIRole(string $role, int $resource_id, string $method_code, $module_id = null): int
    {
        // load all roles from datasource
        if (is_null(self::$cached_roles) && !Base::$flag_database_tenant_not_found) {
            self::$cached_roles = Resources::getStatic('roles', 'primary');
        }
        // if role is not found
        if (empty(self::$cached_roles[$role])) {
            return 0;
        }
        // process permissions
        $all_actions = self::$cached_roles[$role]['apis'][$resource_id]['AllActions'] ?? [];
        $actual_action = self::$cached_roles[$role]['apis'][$resource_id][$method_code] ?? [];
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
        } elseif ($temp === 1) {
            return 2;
        }
        // super admin
        if (\Application::get('flag.numbers.framework.api.allow_super_admin') && !empty(self::$cached_roles[$role]['super_admin'])) {
            return 1;
        }
        // if permission is not found we need to check parents
        if (empty(self::$cached_roles[$role]['parents'])) {
            return 0;
        }
        // go though parents
        foreach (self::$cached_roles[$role]['parents'] as $k => $v) {
            if (!empty($v)) {
                continue;
            }
            $temp = self::processAPIRole($k, $resource_id, $method_code);
            if ($temp === 1) {
                return 1;
            } elseif ($temp === 2) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * Process role (sub-resource)
     *
     * @param string $role
     * @param int $resource_id
     * @param int $subresource
     * @param int $action_id
     * @return int
     */
    private function processSubresourceRole(string $role, int $resource_id, int $subresource, int $action_id, $module_id = null): int
    {
        // load all roles from datasource
        if (is_null(self::$cached_roles) && !Base::$flag_database_tenant_not_found) {
            self::$cached_roles = Resources::getStatic('roles', 'primary');
        }
        // if role is not found
        if (empty(self::$cached_roles[$role])) {
            return 0;
        }
        // process permissions
        $all_actions = self::$cached_roles[$role]['subresources'][$resource_id][$subresource][-1] ?? [];
        $actual_action = self::$cached_roles[$role]['subresources'][$resource_id][$subresource][$action_id] ?? [];
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
            if ($temp === 0) {
                return 1;
            } elseif (!empty($temp)) {
                return 2;
            }
        }
        // if permission is not found we need to check parents
        if (empty(self::$cached_roles[$role]['parents'])) {
            return 0;
        }
        // go though parents
        foreach (self::$cached_roles[$role]['parents'] as $k => $v) {
            if (!empty($v)) {
                continue;
            }
            $temp = $this->processSubresourceRole($k, $resource_id, $subresource, $action_id, $module_id);
            if ($temp === 1) {
                return 1;
            } elseif ($temp === 2) {
                return 2;
            }
        }
        return 0;
    }

    /**
     * Process team
     *
     * @param int $team_id
     * @param int $resource_id
     * @param string $method_code
     * @param int $action_id
     * @return int
     */
    private function processTeam(int $team_id, int $resource_id, string $method_code, int $action_id, $module_id = null): int
    {
        // load all roles from datasource
        if (is_null(self::$cached_teams) && !Base::$flag_database_tenant_not_found) {
            self::$cached_teams = Resources::getStatic('roles', 'teams');
        }
        // if role is not found
        if (empty(self::$cached_teams[$team_id])) {
            return 0;
        }
        // process permissions
        $all_actions = self::$cached_teams[$team_id]['permissions'][$resource_id]['AllActions'][-1] ?? [];
        $actual_action = self::$cached_teams[$team_id]['permissions'][$resource_id][$method_code][$action_id] ?? [];
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
        } elseif ($temp === 1) {
            return 2;
        }
        return 0;
    }

    /**
     * Process team (API)
     *
     * @param int $team_id
     * @param int $resource_id
     * @param string $method_code
     * @return int
     */
    private static function processAPITeam(int $team_id, int $resource_id, string $method_code, $module_id = null): int
    {
        // load all roles from datasource
        if (is_null(self::$cached_teams) && !Base::$flag_database_tenant_not_found) {
            self::$cached_teams = Resources::getStatic('roles', 'teams');
        }
        // if role is not found
        if (empty(self::$cached_teams[$team_id])) {
            return 0;
        }
        // process permissions
        $all_actions = self::$cached_teams[$team_id]['apis'][$resource_id]['AllActions'] ?? [];
        $actual_action = self::$cached_teams[$team_id]['apis'][$resource_id][$method_code] ?? [];
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
        } elseif ($temp === 1) {
            return 2;
        }
        return 0;
    }

    /**
     * Process team (sub-resource)
     *
     * @param int $team_id
     * @param int $resource_id
     * @param int $subresource
     * @param int $action_id
     * @return int
     */
    private function processSuresourceTeam(int $team_id, int $resource_id, int $subresource, int $action_id, $module_id = null): int
    {
        // load all roles from datasource
        if (is_null(self::$cached_teams) && !Base::$flag_database_tenant_not_found) {
            self::$cached_teams = Resources::getStatic('roles', 'teams');
        }
        // if role is not found
        if (empty(self::$cached_teams[$team_id])) {
            return 0;
        }
        // process permissions
        $all_actions = self::$cached_teams[$team_id]['subresources'][$resource_id][$subresource][-1] ?? [];
        $actual_action = self::$cached_teams[$team_id]['subresources'][$resource_id][$subresource][$action_id] ?? [];
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
        } elseif ($temp === 1) {
            return 2;
        }
        return 0;
    }

    /**
     * Get controllers modules
     *
     * @return array
     */
    public function getControllersModules(): array
    {
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
        return Common::buildOptions($result, ['name' => 'name'], [], ['i18n' => true]);
    }

    /**
     * Render menu
     *
     * @param array $options
     *		class
     *		brand_logo
     *		brand_url
     * @return string
     * @deprecated 8.2
     */
    public static function renderMenu(array $options = []): string
    {
        if (!Base::$flag_database_tenant_not_found) {
            $data = Resources::getStatic('menu', 'primary');
            // get logo image
            $brand_logo = Resources::getStatic('layout', 'logo', 'method');
            if (!empty($options['brand_logo'])) {
                $brand_logo = $options['brand_logo'];
            } else {
                $brand_logo = Resources::getStatic('layout', 'logo', 'method');
                if (!empty($brand_logo)) {
                    $method = \Factory::method($brand_logo, null, true);
                    $brand_logo = call_user_func_array($method, []);
                }
            }
            // logo url
            return \HTML::menu([
                'brand_name' => \Application::get('application.layout.name'),
                'brand_logo' => $brand_logo,
                'brand_url' => $options['brand_url'] ?? Resources::getStatic('postlogin_brand_url', 'url', 'url'),
                'options' => $data[200] ?? [],
                'options_right' => $data[210] ?? [],
                'class' => $options['class'] ?? null
            ]);
        } else {
            return '';
        }
    }

    /**
     * Render footer
     *
     * @param array $options
     * @return string
     */
    public static function renderFooter(array $options = []): string
    {
        $data = [];
        foreach (\Route::$footer['grouped'] as $k => $v) {
            if (isset($v['options'])) {
                foreach ($v['options'] as $k2 => $v2) {
                    $v2['order'] = \Route::$routes[$v2['name']]->options['order'] ?? 0;
                    if (\Route::checkAcl($v2['name'], false)) {
                        if (!isset($data[$k])) {
                            $data[$k] = [
                                'label' => $v['label'],
                                'order' => $v['order'],
                                'options' => []
                            ];
                        }
                        $data[$k]['options'][$k2] = $v2;
                    }
                }
            }
        }
        if (empty($data)) {
            $result = '';
            goto localization;
        }
        array_key_sort($data, ['order' => SORT_ASC]);
        $result = '<table class="numbers_footer_table">';
        $result .= '<tr>';
        foreach ($data as $k => $v) {
            $result .= '<th>';
            $result .= i18n(null, $k);
            $result .= '</th>';
        }
        $result .= '</tr>';
        $result .= '<tr>';
        foreach ($data as $k => $v) {
            $result .= '<td valign="top">';
            if (!empty($v['options'])) {
                $result .= '<table>';
                array_key_sort($v['options'], ['order' => SORT_ASC]);
                foreach ($v['options'] as $k2 => $v2) {
                    $result .= '<tr>';
                    $result .= '<td>';
                    $icon = '';
                    if ($v2['icon']) {
                        $icon = \HTML::icon(['type' => $v2['icon']]) . ' ';
                    }
                    $result .= \HTML::a(['href' => \Route::link($k2), 'value' => $icon . i18n(null, $v2['label'])]);
                    $result .= '</td>';
                    $result .= '</tr>';
                }
                $result .= '</table>';
            }
            $result .= '</td>';
        }
        $result .= '</tr>';
        $result .= '</table>';
        // language
        $result .= \HTML::hr();
        localization:
                $groups = \I18n::optionsGroups();
        $result .= loc('NF.Form.I18nGroup', 'Internalization') . ':' . \HTML::select(['id' => '__in_group_id', 'name' => '__in_group_id', 'options' => $groups, 'value' => \Application::get('flag.global.__in_group_id') ?? \Application::get('flag.global.i18n.group_id'), 'no_choose' => true, 'onchange' => 'numbers_i18n_set_cookie(this.value);', 'style' => 'width: 250px;']);
        $result .= \HTML::script(['value' => <<<TTT
			function numbers_i18n_set_cookie(group) {
				document.cookie = "__in_group_id=" + group + "; expires=Fri, 31 Dec 9999 23:59:59 GMT; SameSite=Strict; Secure; path=/";
				location.reload();
			}
TTT]);
        return $result;
    }

    /**
     * Get system module by module code
     *
     * @param string $module_code
     * @return array
     */
    public static function getSystemModuleByModuleCode(string $module_code): array
    {
        return self::$cached_modules[$module_code] ?? [];
    }

    /**
     * Add usage action
     *
     * @param string $usage_code
     * @param array $options
     *		array replace
     *		string message
     *		integer affected_rows
     *		integer error_rows
     *		string url
     *		boolean history
     */
    public function addUsageAction(string $usage_code, array $options = [])
    {
        $codes = UsageCodes::getStatic();
        if (empty($codes[$usage_code])) {
            throw new \Exception('You must register usage code in overrides: ' . $usage_code);
        }
        // generate url
        if (empty($options['url'])) {
            if (in_array('*', $codes[$usage_code]['methods']) || in_array(\Request::method(), $codes[$usage_code]['methods'])) {
                $options['url'] = \Application::get('mvc.full') . '?' . http_build_query2(\Request::input());
            }
        }
        self::$usage_actions[] = [
            'usage_code' => $usage_code,
            'message' => $options['message'] ?? $codes[$usage_code]['message'],
            'replace' => $options['replace'] ?? [],
            'affected_rows' => $options['affected_rows'] ?? 0,
            'error_rows' => $options['error_rows'] ?? 0,
            'url' => $options['url'] ?? null,
            'history' => ($options['history'] ?? $codes[$usage_code]['history']) ? 1 : 0
        ];
    }

    /**
     * Get usage actions
     *
     * @return array
     */
    public function getUsageActions(): array
    {
        return self::$usage_actions;
    }
}
