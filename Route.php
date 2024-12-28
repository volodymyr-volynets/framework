<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\ACL\Resources;
use Object\Content\Messages;
use Object\Controller;
use Object\Controller\Front;
use Object\Reflection;
use Object\Middleware;
use Object\Controller\API as APIController;

class Route
{
    /**
     * HTTP defines a set of request methods to indicate the desired action to be performed for a given resource.
     */
    public const HTTP_REQUEST_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'PATCH', 'TRACE', 'OPTIONS', 'CONNECT'];
    public const HTTP_REQUEST_METHOD_LOWER_CASE = ['get', 'post', 'put', 'delete', 'head', 'patch', 'trace', 'options', 'connect'];

    /**
     * All request methods
     */
    public const HTTP_REQUEST_METHOD_ALL = 'ALL';

    /**
     * API action
     */
    public const API_ACTION = 'API';

    /**
     * Route types
     */
    public const ROUTE_TYPES = ['Controller', 'API', 'Menu', 'Footer'];

    /**
     * Groups
     *
     * @var array
     */
    public static $groups = [];

    /**
     * Group that are currently executing
     *
     * @var array
     */
    private static $group_current_execution = [];

    /**
     * Routes
     *
     * @var array
     */
    public static $routes = [];

    /**
     * Footer
     *
     * @var array
     */
    public static $footer = [
        'list' => [],
        'grouped' => [],
    ];

    /**
     * Name
     *
     * @var string
     */
    public $name = '';

    /**
     * Type
     *
     * @var string
     */
    public $type = 'Controller';

    /**
     * Additional options
     *
     * @var array
     */
    public $options = [];

    /**
     * Group
     *
     * @var string
     */
    public $group = '';

    /**
     * URI
     *
     * @var string
     */
    public $uri = '';

    /**
     * Actions
     *
     * @var string
     */
    public $action = 'Index';

    /**
     * Method
     *
     * @var string
     */
    public $methods = [];

    /**
     * Action method code
     *
     * @var string|null
     */
    public $action_method_code = null;

    /**
     * Resource
     *
     * @var array
     */
    public $resource = [];

    /**
     * Callable
     *
     * @var callable
     */
    public $callable = null;

    /**
     * URI parameters
     *
     * @var array
     */
    public $parameters = [
        'uri_clean' => [],
        'uri_full' => [],
        'from_request' => []
    ];

    /**
     * ACL
     *
     * @var array
     */
    public $acl = [
        'as_controller' => false,
        'as_controller_name' => null,
        'as_api' => null,
        // roles and teams
        'roles' => [],
        'role_ids' => [],
        'role_names' => [],
        // teams
        'teams' => [],
        'team_ids' => [],
        'team_names' => [],
        // features
        'owners' => [],
        'features' => [],
        // access
        'authorized' => null,
        'public' => null,
        'permission' => null,
        // middleware
        'middleware' => [],
    ];

    /**
     * URI
     *
     * @param string $name
     * @param string $uri
     * @param string|array $action
     * @param string|array $methods
     * @param string|array|callable|null $resource
     * @param array $options
     * @return Route
     */
    public static function uri(string $name, string $uri, string $action = 'Index', string|array $methods = self::HTTP_REQUEST_METHOD_ALL, string|array|callable|null $resource = null, array $options = []): Route
    {
        $route = new self();
        // name
        if (isset(self::$routes[$name])) {
            Messages::message('ROUTE_NAME_EXISTS', ['[name]' => $name], true, true);
        }
        $route->name = $name;
        // type
        if (isset($options['type'])) {
            $route->type = $options['type'];
        }
        if (!in_array($route->type, self::ROUTE_TYPES)) {
            Messages::message('ROUTE_INVALID_TYPE', ['[type]' => $route->type], true, true);
        }
        // options
        $route->options = $options;
        // groups
        if (!empty(self::$group_current_execution)) {
            $last = self::$group_current_execution[count(self::$group_current_execution) - 1];
            $route->group = $last->name;
            $last->last = $route;
            $last_uri = '';
            foreach (self::$group_current_execution as $k => $v) {
                self::$groups[$v->name][$name] = true;
                if (!isset($v->last)) {
                    self::$group_current_execution[$k]->last = $route;
                }
                $last_uri .= rtrim($v->uri ?? '', '/');
            }
            $uri = rtrim($last_uri, '/') . '/' . ltrim($uri, '/');
        }
        // process uri
        if (strpos($uri, '{') !== false) {
            $matches = [];
            if (preg_match_all('/{(.*?)}/', $uri, $matches, PREG_PATTERN_ORDER)) {
                $route->parameters['uri_full'] = $matches[0];
                $route->parameters['uri_clean'] = $matches[1];
            }
        }
        $route->uri = $uri;
        // process actions
        $route->action = $action;
        // process method
        if (is_string($methods)) {
            $methods = explode(',', $methods);
        }
        $unknown_methods = array_filter($methods, function ($v) {
            return !in_array($v, Route::HTTP_REQUEST_METHODS);
        });
        if (!empty($unknown_methods)) {
            Messages::message('ROUTE_INVALID_METHODS', ['[methods]' => implode(', ', $unknown_methods)], true, true);
        }
        $route->methods = $methods;
        // for API we need proper function name that starts with method
        $route->action_method_code = strtolower($methods[0]) . str_replace('_', '', $action);
        // process resource
        if (is_string($resource)) {
            $route->resource = explode('::', $resource);
        } elseif (is_array($resource)) {
            $route->resource = $resource;
        } elseif (is_callable($resource)) {
            $route->callable = $resource;
            $route->resource = 'callable';
        }
        // adding new route to the list of all routes
        if ($route->type == 'Footer') {
            self::$routes[$name] = $route;
            self::$footer['list'][$name] = $route;
            $keys = [];
            foreach ($route->options['groups'] as $v) {
                $keys[] = $v;
                if (!array_key_get(self::$footer['grouped'], $keys)) {
                    array_key_set(self::$footer['grouped'], $keys, [
                        'name' => $name,
                        'label' => str_replace('Footer: ', '', $v),
                        'order' => $route->options['group_order'] ?? 0,
                    ]);
                }
                $keys[] = 'options';
            }
            $keys[] = $name;
            array_key_set(self::$footer['grouped'], $keys, [
                'name' => $name,
                'label' => $route->options['label'],
                'icon' => $route->options['icon'],
                'order' => null,
            ]);
        } else {
            self::$routes[$name] = $route;
        }
        // returning self object
        return $route;
    }


    /**
     * URI
     *
     * @param string $name
     * @param string $uri
     * @param string|array $action
     * @param string|array $methods
     * @param string|array|callable|null $resource
     * @param array $options
     * @return Route
     */
    public static function api(string $name, string $uri, string $api_class, array $options = []): Route
    {
        return self::group($name, null, function () use ($name, $uri, $api_class, $options) {
            $methods = Reflection::getMethods($api_class, ReflectionMethod::IS_PUBLIC, Route::HTTP_REQUEST_METHOD_LOWER_CASE);
            foreach ($methods as $k => $v) {
                foreach ($v as $k2 => $v2) {
                    $uri_new = $uri;
                    // for certain routes we need to set pk
                    if (str_starts_with($v2['name_underscore'], 'Record_') && isset($options['pk'])) {
                        foreach ($options['pk'] as $v3) {
                            $uri_new .= '/{' . $v3 . '}';
                        }
                    }
                    $options2 = $options;
                    $options2['type'] = 'API';
                    self::uri($name . ' [' . strtoupper($k) . ',' . $v2['name_nice'] . ']', $uri_new, $v2['name_underscore'], strtoupper($k), [$api_class, $k2], $options2);
                }
            }
        });
    }

    /**
     * Group
     *
     * @param string $name
     * @param string|null $uri
     * @param callable|null $callable
     * @return Route
     */
    public static function group(string $name, string|null $uri = null, callable|null $callable = null): Route
    {
        $group = new stdClass();
        $group->name = $name;
        $group->uri = $uri;
        $group->last = null;
        self::$group_current_execution[] = $group;
        call_user_func($callable, $group);
        $last = array_pop(self::$group_current_execution);
        return self::$routes[$last->last->name];
    }

    /**
     * Options
     *
     * @param array $options
     * @return Route
     */
    public function options(array $options): Route
    {
        foreach ($options as $k => $v) {
            $this->setOptionsValue([$k], $v);
        }
        return $this;
    }

    /**
     * Set Options values
     *
     * @param string|array $keys
     * @param mixed $value
     * @return Route
     */
    private function setOptionsValue(string|array $keys, mixed $value): Route
    {
        if (is_string($keys)) {
            $keys = explode(',', $keys);
        }
        if (!empty($this->group)) {
            foreach (self::$groups[$this->group] as $k => $v) {
                array_key_set(self::$routes[$k]->options, $keys, $value);
            }
        } else {
            array_key_set($this->options, $keys, $value);
        }
        return $this;
    }

    /**
     * Menu
     *
     * @param string $name
     * @param string $icon
     * @param array $groups
     * @param string $uri
     * @param string $action
     * @param string|array|callable|null $resource
     * @return Route
     */
    public static function menu(string $name, string $icon, array $groups, string $uri, string $action = 'Index', string|array|callable|null $resource = null): Route
    {
        $module_code = array_shift($groups);
        return self::uri($name, $uri, $action, 'GET', $resource, [
            'type' => 'Menu',
            'icon' => $icon,
            'module_code' => $module_code,
            'groups' => $groups
        ]);
    }

    /**
     * Footer
     *
     * @param string $name
     * @param string $icon
     * @param array $groups
     * @param string $uri
     * @param string $action
     * @param string|array|callable|null $resource
     * @return Route
     */
    public static function footer(string $name, string $icon, array $groups, string $uri, string $action = 'Index', string|array|callable|null $resource = null): Route
    {
        $module_code = array_shift($groups);
        return self::uri('Footer: ' . $name, $uri, $action, 'GET', $resource, [
            'type' => 'Footer',
            'icon' => $icon,
            'module_code' => $module_code,
            'groups' => $groups,
            'label' => $name,
        ]);
    }

    /**
     * ACL
     *
     * @param string|array $acl
     *      Role:[ROLE CODE]
     *      Role ID:[ID]
     *      Team Name:[TEAM NAME]
     *      Team ID:[ID]
     *      Feature:[FEATURE CODE]
     */
    public function acl(string|array $acl): Route
    {
        if (is_string($acl)) {
            $acl = explode(',', $acl);
        }
        foreach ($acl as $v) {
            $found = false;
            // roles
            if (strpos($v, 'Role:') !== false) {
                $v = str_replace('Role:', '', $v);
                $this->setAclValue(['roles', $v], $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // role ids
            if (!$found && strpos($v, 'Role ID:') !== false) {
                $v = str_replace('Role ID:', '', $v);
                $this->setAclValue(['role_ids', $v], (int) $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // role names
            if (!$found && strpos($v, 'Role Name:') !== false) {
                $v = str_replace('Role Name:', '', $v);
                $this->setAclValue(['role_names', $v], $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // teams
            if (strpos($v, 'Team:') !== false) {
                $v = str_replace('Team:', '', $v);
                $this->setAclValue(['teams', $v], $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // team name
            if (!$found && strpos($v, 'Team Name:') !== false) {
                $v = str_replace('Team Name:', '', $v);
                $this->setAclValue(['team_names', $v], $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // team ids
            if (!$found && strpos($v, 'Team ID:') !== false) {
                $v = str_replace('Team ID:', '', $v);
                $this->setAclValue(['team_ids', $v], (int) $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // features
            if (!$found && strpos($v, 'Feature:') !== false) {
                $v = str_replace('Feature:', '', $v);
                $this->setAclValue(['features', $v], $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // owners
            if (!$found && strpos($v, 'Owner:') !== false) {
                $v = str_replace('Owner:', '', $v);
                $this->setAclValue(['owners', $v], $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // controller
            if ($v == 'As Controller') {
                $this->setAclValue(['as_controller'], true);
                $this->setAclValue(['authorized'], null);
                $this->setAclValue(['public'], null);
                $this->setAclValue(['permission'], null);
                $found = true;
            }
            if (!$found && strpos($v, 'As Controller:') !== false) {
                $v = str_replace('As Controller:', '', trim($v));
                $this->setAclValue(['as_controller_name'], $v);
                $this->setAclValue(['as_controller'], false);
                // these needs reset
                $this->setAclValue(['authorized'], null);
                $this->setAclValue(['public'], null);
                $this->setAclValue(['permission'], null);
                $found = true;
            }
            if (!$found && strpos($v, 'As API:') !== false) {
                $v = str_replace('As API:', '', trim($v));
                $this->setAclValue(['as_api'], $v);
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            // authorized
            if ($v == 'Authorized') {
                $this->setAclValue(['authorized'], true);
                $found = true;
            }
            if ($v == 'Not Authorized') {
                $this->setAclValue(['authorized'], false);
                $found = true;
            }
            // public
            if ($v == 'Public') {
                $this->setAclValue(['public'], true);
                $found = true;
            }
            if ($v == 'Not Public') {
                $this->setAclValue(['public'], false);
                $found = true;
            }
            // permission
            if ($v == 'Permission') {
                $this->setAclValue(['permission'], true);
                $found = true;
            }
            if ($v == 'Not Permission') {
                $this->setAclValue(['permission'], false);
                $found = true;
            }
            // if we got here
            if (!$found) {
                Messages::message('ROUTE_UNKNOWN_ACL_PARAMETER', ['[parameter]' => $v], true, true);
            }
        }
        // returning self object
        return $this;
    }

    /**
     * Middleware
     *
     * @param string $name
     * @param array $options
     * @return Route
     */
    public function middleware(string $name, array $options = []): Route
    {
        $middleware = Middleware::getMiddlewareStatic($name);
        $this->setAclValue(['middleware', $name], $middleware);
        $this->setAclValue(['middleware', $name, 'name'], $middleware['name']);
        $this->setAclValue(['middleware', $name, 'check'], $middleware['check']);
        $this->setAclValue(['middleware', $name, 'channel'], $middleware['channel']);
        $this->setAclValue(['middleware', $name, 'options'], $options);
        return $this;
    }

    /**
     * Set ACL values
     *
     * @param string|array $keys
     * @param mixed $value
     * @return Route
     */
    private function setAclValue(string|array $keys, mixed $value): Route
    {
        if (is_string($keys)) {
            $keys = explode(',', $keys);
        }
        if (!empty($this->group)) {
            foreach (self::$groups[$this->group] as $k => $v) {
                array_key_set(self::$routes[$k]->acl, $keys, $value);
            }
        } else {
            array_key_set($this->acl, $keys, $value);
        }
        return $this;
    }

    /**
     * Link generator
     *
     * @param string $name
     * @param array|null $parameters
     * @return string
     */
    public static function link(string $name, ?array $parameters = null, string|bool $host = false): string
    {
        if (!isset(self::$routes[$name])) {
            Messages::message('ROUTE_NAME_NOT_FOUND', ['[name]' => $name], true, true);
        }
        $result = self::$routes[$name]->uri;
        // add parameters
        if (!empty(self::$routes[$name]->parameters)) {
            foreach (self::$routes[$name]->parameters['uri_clean'] as $v) {
                $result = str_replace('{' . $v . '}', $parameters[$v], $result);
            }
        }
        // actions
        if (!empty(self::$routes[$name]->action)) {
            if (empty($result) || $result === '/') {
                $result = '/Index';
            }
            $result = rtrim($result, '/') . '/_' . ltrim(self::$routes[$name]->action, '/');
        }
        // prepend host
        if ($host === true) {
            $host = Request::host();
        }
        if (is_string($host)) {
            $result = rtrim($host, '/') . '/' . ltrim($result, '/');
        }
        return $result;
    }

    /**
     * Redirect
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public static function redirect(string $name, array|null $parameters = null, string|bool $host = false): string
    {
        $link = self::link($name, $parameters, $host);
        Request::redirect($link);
    }

    /**
     * Match URI
     *
     * @param string|null $request_uri
     * @return array
     */
    public static function match(string|null $request_uri = null, string|null $method = null): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => [],
            'name' => null,
            'route' => null,
            'parameters' => [],
            'request_uri' => $request_uri,
            'type' => null,
            'wrong_method' => null,
        ];
        $request_uri = $request_uri ?? $_SERVER['REQUEST_URI'] ?? '/';
        if (empty($request_uri)) {
            $request_uri = '/';
        }
        $route = false;
        $sanitized = rtrim($request_uri, '/') . '/';
        foreach (self::$routes as $k => $v) {
            // we only process controllers and APIs
            if (!in_array($v->type, ['Controller', 'API'])) {
                continue;
            }

            if (empty($v->parameters['uri_full'])) {
                if (rtrim($v->uri, '/') == rtrim($request_uri, '/')) {
                    $route = $v;
                    break;
                } else {
                    // action with underscore
                    if (rtrim($request_uri, '/') == rtrim($v->uri, '/') . '/_' . $v->action) {
                        $route = $v;
                        break;
                    } else {
                        // if we have action without undercore
                        if (rtrim($request_uri, '/') == rtrim($v->uri, '/') . '/_' . str_replace('_', '', $v->action)) {
                            $route = $v;
                            break;
                        }
                    }
                }
            } else {
                $uri = rtrim($v->uri, '/') . '/';
                foreach ($v->parameters['uri_full'] as $v2) {
                    $uri = str_replace($v2, '([a-zA-Z0-9_\-]+)', $uri);
                }
                if (preg_match("#^" . $uri . "$#i", $sanitized, $matches)) {
                    $route = $v;
                    unset($matches[0]);
                    foreach (array_values($matches) as $k2 => $v2) {
                        $route->parameters['from_request'][$v->parameters['uri_clean'][$k2]] = $v2;
                    }
                    break;
                }
                // match with action
                $uri = rtrim($uri, '/') . '/_' . ltrim($v->action, '/') . '/';
                if (preg_match("#^" . $uri . "$#i", $sanitized, $matches)) {
                    $route = $v;
                    unset($matches[0]);
                    foreach (array_values($matches) as $k2 => $v2) {
                        $route->parameters['from_request'][$v->parameters['uri_clean'][$k2]] = $v2;
                    }
                    break;
                }
            }
        }
        // if we found matching route we generate mvc
        if ($route) {
            // final check for method
            if (!in_array($method, $route->methods)) {
                $result['wrong_method'] = true;
            }
            $result['success'] = true;
            if ($route->type == 'API') {
                $uri = str_replace('\\', '/', '/' . ltrim($route->resource[0], '/')) . '/_' . str_replace(self::HTTP_REQUEST_METHOD_LOWER_CASE, '', $route->resource[1]);
            } elseif ($route->callable) {
                $uri = '/Object/Controller/Callable2/_Callable';
            } elseif (!empty($route->resource)) {
                $uri = str_replace('\\', '/', '/' . ltrim($route->resource[0], '/')) . '/_' . $route->resource[1];
            } else {
                $uri = rtrim($request_uri, '/') . '/_' . ltrim($route->action, '/');
            }
            $result['data'] = Front::mvc($uri);
            $result['parameters'] = $route->parameters['from_request'];
            $result['request_uri'] = $uri;
            $result['route'] = $route;
            $result['name'] = $route->name;
            $result['type'] = $route->type;
        } else {
            $result['error'][] = Messages::ROUTE_NOT_FOUND;
        }
        return $result;
    }

    /**
     * Check middleware
     *
     * @param string $name
     * @param string $check - Before or After
     * @return bool
     */
    public static function checkMiddleware(string $name, string $check = 'Before'): bool
    {
        /** @var Route $route */
        $route = self::$routes[$name];
        $middlewares = new Array2($route->acl['middleware'] ?? []);
        $middlewares->filter(function ($value) use ($check) {
            if (!in_array('Route', $value['channel'])) {
                return false;
            }
            if (in_array($check, $value['check'])) {
                return true;
            }
        });
        // combine and sort
        $combined = array_merge_hard($middlewares->toArray(), Middleware::getAlwaysMiddlewareStatic('Route'));
        array_key_sort($combined, ['priority' => SORT_DESC]);
        return Middleware::runMiddlewareStatic($combined, $check, ['route' => $route]);
    }

    /**
     * Check ACL
     *
     * @param string $name
     * @return bool
     */
    public static function checkAcl(string $name, bool $throw = true): bool
    {
        /** @var Route $route */
        $route = self::$routes[$name];
        if ($route->acl['public'] === true) {
            return true;
        }
        if ($route->acl['as_api']) {
            // preload cached controllers
            if (is_null(Controller::$cached_controllers)) {
                Controller::$cached_controllers = Resources::getStatic('controllers', 'primary');
            }
            $controller = false;
            foreach (Controller::$cached_controllers as $k => $v) {
                if ($v['name'] == $route->acl['as_api']) {
                    $controller = $v;
                    $controller['model'] = $k;
                    break;
                }
            }
            // todo add validation
            if ($controller) {
                if (Can::apiActionPermitted('\\' . ltrim($controller['model'], '\\'), $route->action_method_code)) {
                    return true;
                }
            }
            return false;
        }
        if ($route->acl['as_controller_name']) {
            // preload cached controllers
            if (is_null(Controller::$cached_controllers)) {
                Controller::$cached_controllers = Resources::getStatic('controllers', 'primary');
            }
            // find by name
            $temp_name = explode(',', $route->acl['as_controller_name']);
            //U/M Groups,Index,List_View
            $controller = false;
            foreach (Controller::$cached_controllers as $k => $v) {
                if ($v['name'] == $temp_name[0]) {
                    $controller = $v;
                    $controller['model'] = $k;
                    break;
                }
            }
            if ($controller) {
                if (Can::controllerActionPermitted('\\' . ltrim($controller['model'], '\\'), $temp_name[1] ?? 'Index', $temp_name[2] ?? 'List_View')) {
                    return true;
                }
            }
            return false;
        }
        if ($route->acl['as_controller']) {
            if (Can::controllerActionPermitted('\\' . ltrim($route->resource[0], '\\'), $route->resource[1], $route->resource[2])) {
                return true;
            } else {
                return false;
            }
        }
        if ($route->acl['authorized'] === true && !User::authorized()) {
            if ($throw) {
                Messages::message('ROUTE_ACL_UNAUTHORIZED', null, true, true);
            } else {
                return false;
            }
        }
        if ($route->acl['authorized'] === false && User::authorized()) {
            if ($throw) {
                Messages::message('ROUTE_ACL_NOT_AUTHORIZED', null, true, true);
            } else {
                return false;
            }
        }
        if ($route->acl['permission']) {
            $common = '__no_role';
            // we need to check roles and teams first
            if ($route->acl['roles']) {
                $common = array_intersect($route->acl['roles'], User::get('roles'));
            }
            if ($route->acl['role_ids']) {
                $common = array_intersect($route->acl['role_ids'], User::get('role_ids'));
            }
            if ($route->acl['role_names']) {
                $common = array_intersect($route->acl['role_names'], User::get('role_names'));
            }
            if ($route->acl['teams']) {
                $common = array_intersect($route->acl['teams'], User::get('team_codes'));
            }
            if ($route->acl['team_ids']) {
                $common = array_intersect($route->acl['team_ids'], User::get('teams'));
            }
            if ($route->acl['team_names']) {
                $common = array_intersect($route->acl['team_names'], User::get('team_names'));
            }
            if ($route->acl['owners']) {
                foreach ($route->acl['owners'] as $v) {
                    if (Can::userIsOwner($v)) {
                        $common[] = $v;
                        break;
                    }
                }
            }
            if ((is_array($common) && !empty($common)) || $common === '__no_role') {
                // check if feature exists
                $found = '__no_feature';
                if ($route->acl['features']) {
                    foreach ($route->acl['features'] as $v) {
                        if (Can::userFeatureExists($v)) {
                            $found = true;
                            break;
                        }
                    }
                }
                if ($found === true || $found === '__no_feature' || !empty($common)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get endpoint
     *
     * @param string $class
     * @param string $method
     * @param array $options
     * @throws Exception
     * @return string
     */
    public static function getEndpoint(string $class, string $method, array $options = []): string
    {
        /** @var APIController $object */
        $object = new $class([
            'skip_constructor_loading' => false,
        ]);
        // determine method
        $http_method = null;
        foreach (self::HTTP_REQUEST_METHOD_LOWER_CASE as $v) {
            if (str_starts_with($method, $v)) {
                $http_method = $v;
                break;
            }
        }
        $method = str_replace(self::HTTP_REQUEST_METHOD_LOWER_CASE, '', $method);
        $name = $object->name . ' [' . strtoupper($http_method) . ',' . trim(preg_replace("([A-Z])", " $0", $method)) . ']';
        // get endpoint from routes
        if (!isset(self::$routes[$name])) {
            throw new Exception('Could not find URI in Routes.');
        }
        $endpoint = self::$routes[$name]->uri . '/_' . self::$routes[$name]->action;
        if (!empty($options['include_host'])) {
            $endpoint = Request::host() . ltrim($endpoint, '/');
        }
        return $endpoint;
    }
}
