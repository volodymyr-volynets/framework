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
use Object\Traits\ObjectableAndStaticable;

class Log
{
    use ObjectableAndStaticable;

    /**
     * Log link
     *
     * @var string
     */
    public $log_link;

    /**
     * Database object
     *
     * @var object
     */
    public $object;

    /**
     * Logs
     *
     * @var array
     */
    protected static array $logs = [];

    /**
     * Group id
     *
     * @var string
     */
    protected static ?string $group_id = null;

    /**
     * Originated id
     *
     * @var string
     */
    protected static ?string $originated_id = null;

    /**
     * Options
     *
     * @var array
     */
    public $options = [];

    /**
     * Constructing log object
     *
     * @param string $log_link
     * @param string $class
     * @param array $options
     */
    public function __construct($log_link = null, $class = null, $options = [])
    {
        // if we need to use default link from application
        if (empty($log_link)) {
            $log_link = Application::get('flag.global.default_log_link');
            if (empty($log_link)) {
                throw new Exception('You must specify log link and/or class!');
            }
        }
        $this->log_link = $log_link;
        // get object from factory
        $temp = Factory::get(['log', $log_link]);
        // if we have class
        if (!empty($class) && !empty($log_link)) {
            // check if backend has been enabled
            if (!Application::get($class, ['submodule_exists' => true])) {
                throw new Exception('You must enable ' . $class . ' first!');
            }
            // if we are replacing database connection with the same link we
            // need to manually close database connection
            if (!empty($temp['object'])) {
                $object = $temp['object'];
                $object->close();
                unset($this->object);
            }
            // creating new class
            $this->object = new $class($log_link, $options);
            // putting every thing into factory
            Factory::set(['log', $log_link], [
                'object' => $this->object,
                'class' => $class,
            ]);
            // set options without credentials
            $this->options = $options;
            // set postponed execution
            //\Factory::postponedExecution(['Log', 'deliver'], []);
        } elseif (!empty($temp['object'])) {
            $this->object = & $temp['object'];
        } else {
            throw new Exception('You must specify log link and/or class!');
        }
    }

    /**
     * Get group #
     *
     * @return string
     */
    public static function getGroupId(): string
    {
        if (empty(self::$group_id)) {
            self::$group_id = Db::uuidTenanted(Tenant::id(), Request::ip());
        }
        return self::$group_id;
    }

    /**
     * Set originated id
     *
     * @param string|null $originated_id
     */
    public static function setOriginatedId(?string $originated_id)
    {
        self::$originated_id = $originated_id;
    }

    /**
     * Info logs
     *
     * @param string $message
     * @param array $options
     */
    public static function info(string $message, array $options = []): bool
    {
        return Log::add([
            'type' => 'Debug (Info)',
            'message' => (new String2($message))->replaceParametersOptions($options)->toString(),
            'only_channel' => ['default'],
        ] + $options);
    }

    /**
     * Warning logs
     *
     * @param string $message
     * @param array $options
     */
    public static function warning(string $message, array $options = []): bool
    {
        return Log::add([
            'type' => 'Debug (Warning)',
            'message' => (new String2($message))->replaceParametersOptions($options)->toString(),
            'only_channel' => ['default'],
        ] + $options);
    }

    /**
     * Error logs
     *
     * @param string $message
     * @param array $options
     */
    public static function error(string $message, array $options = []): bool
    {
        return Log::add([
            'type' => 'Debug (Error)',
            'message' => (new String2($message))->replaceParametersOptions($options)->toString(),
            'only_channel' => ['default'],
        ] + $options);
    }

    /**
     * Add
     *
     * @param array $data
     * @return bool
     */
    public static function add(array $data): bool
    {
        self::$group_id = self::getGroupId();
        // channels
        if (!empty($data['only_chanel'])) {
            if (is_string($data['only_chanel'])) {
                $data['only_chanel'] = explode(',', $data['only_chanel']);
            }
        } else {
            $data['only_chanel'][] = 'default';
        }
        // if cli
        if (Cmd::isCli()) {
            $data['tenant_id'] = 0;
            $data['user_ip'] = '127.0.0.0';
            $data['user_id'] = null;
            $data['host'] = 'CLI';
        } else {
            $data['tenant_id'] = $data['tenant_id'] ?? Tenant::id();
            $data['user_ip'] = $data['user_ip'] ?? Request::ip();
            $data['user_id'] = $data['user_id'] ?? User::id();
            $data['host'] = Request::host();
        }
        $data['inserted_timestamp'] = Format::now('timestamp');
        $data['type'] = $data['type'] ?? 'General';
        $data['id'] = Db::uuidTenanted($data['tenant_id'], $data['user_ip']);
        $data['group_id'] = self::$group_id;
        $data['originated_id'] = $data['originated_id'] ?? self::$originated_id ?? null;
        $data['level'] = $data['level'] ?? 'ALL';
        $data['status'] = $data['status'] ?? 'Information';
        $data['message'] = $data['message'] ?? '';
        $data['trace'] = $data['trace'] ?? null;
        $controller_name = Application::get(['mvc', 'controller_path']);
        if ($controller_name) {
            $controller_name .= '/_' . Application::get(['mvc', 'controller_action_code']);
        }
        $data['controller_name'] = $data['controller_name'] ?? $controller_name;
        $data['form_name'] = $data['form_name'] ?? null;
        $data['notifications'] = $data['notifications'] ?? null;
        $data['affected_users'] = $data['affected_users'] ?? null;
        $data['affected_rows'] = $data['affected_rows'] ?? 0;
        $data['error_rows'] = $data['error_rows'] ?? 0;
        $data['form_statistics'] = $data['form_statistics'] ?? null;
        $data['sql'] = $data['sql'] ?? null;
        $data['content_type'] = $data['content_type'] ?? 'text/html';
        $data['operation'] = $data['operation'] ?? 'NONE';
        $data['request_url'] = $data['request_url'] ?? $_SERVER['REQUEST_URI'] ?? '';
        if (!empty($data['duration'])) {
            // change type to slow
            $settings = [
                'Db Query' => 'log.settings.slow_sql_seconds',
                'Cache' => 'log.settings.slow_cache_seconds',
                'Request' => 'log.settings.slow_request_seconds',
            ];
            if (isset($settings[$data['type']])) {
                $setting = (float) Application::get($settings[$data['type']]);
                if ($setting > 0 && $data['duration'] > $setting) {
                    $data['type'] = $data['type'] . ' (Slow)';
                }
            }
            // multiply the duration
            $data['duration'] = round($data['duration'], 8);
        } else {
            $data['duration'] = round(microtime(true) - Application::get('application.system.request_time'), 8);
        }
        $data['other'] = $data['other'] ?? null;
        $data['inactive'] = !empty($data['inactive']) ? 1 : 0;
        $data['ajax'] = $data['ajax'] ?? Application::get('flag.global.__ajax') ?? Request::input('__ajax') ?? 0;
        if ($data['ajax'] === 'true') {
            $data['ajax'] = 1;
        } elseif ($data['ajax'] === 'false') {
            $data['ajax'] = 0;
        }
        $data['ajax'] = (int) $data['ajax'];
        $data['options'] = [
            'only_chanel' => $data['only_chanel'],
        ];
        // these are driver specific
        unset($data['chanel'], $data['only_chanel']);
        foreach ($data['options']['only_chanel'] as $v) {
            if (!isset(self::$logs[$v])) {
                self::$logs[$v] = [];
            }
            self::$logs[$v][] = $data;
            // cc all messages
            foreach (Application::get('log.' . $v . '.cc') ?? [] as $v2) {
                // skip if we sent messages there already
                if (in_array($v2, $data['options']['only_chanel'])) {
                    continue;
                }
                if (!isset(self::$logs[$v2])) {
                    self::$logs[$v2] = [];
                }
                self::$logs[$v2][] = $data;
            }
        }
        return true;
    }

    /**
     * Deliver
     *
     * @return array
     */
    public static function deliver(): array
    {
        $result = RESULT_BLANK;
        $all_log_objects = Factory::get(['log']) ?? [];
        foreach ($all_log_objects as $log_link => $log_settings) {
            if (empty(self::$logs[$log_link])) {
                continue;
            }
            $save_result = $log_settings['object']->save($log_link, self::$logs[$log_link]);
            if (!$save_result['success']) {
                trigger_error('Could not save to logs.');
            } else {
                unset(self::$logs[$log_link]);
            }
        }
        $result['success'] = true;
        return $result;
    }
}
