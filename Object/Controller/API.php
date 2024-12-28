<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Controller;

use Helper\Constant\HTTPConstants;
use Helper\HTTPResponse;
use Object\Table;

abstract class API
{
    /**
     * ACL settings
     *
     * Permissions only
     *
     * @var array
     */
    public $acl = [
        'public' => true,
        'authorized' => true,
        'permission' => false
    ];

    /**
     * API Content Type
     *
     * @var string
     */
    public $content_type;

    /**
     * API Input
     *
     * @var array
     */
    public $input = [];

    /**
     * API values (processsed input)
     *
     * @var array
     */
    public $values = [];

    /**
     * API columns
     *
     * @var array
     */
    public $columns = [];

    /**
     * Validator
     *
     * @var \Validator
     */
    public $validator;

    /**
     * API Route options
     *
     * @var array
     */
    public $route_options = [];

    /**
     * API group
     *
     * @var array
     */
    public $group = [];

    /**
     * API name
     *
     * @var string
     */
    public $name;

    /**
     * Version
     *
     * @var string
     */
    public $version = 'V1';

    /**
     * Localizations
     *
     * @var array
     */
    public $loc = [];

    /**
     * Content type
     *
     * @var array
     */
    public static $content_types = [
        'json' => 'application/json',
        'xml' => 'application/xml'
    ];

    /**
     * Result
     *
     * @var array
     */
    protected $result = \Validator::RESULT_DANGER;

    /**
     * Remote URL
     *
     * @var string|null
     */
    public ?string $remote_url = null;

    /**
     * Base URL
     *
     * @var string|null
     */
    public $base_url;

    /**
     * Constructor
     *
     * @param array $options
     * 		skip_constructor_loading
     */
    public function __construct(array $options = [])
    {
        // load localization
        if (!empty($options['load_localization'])) {
            $temp = get_object_vars($this);
            foreach ($temp as $k => $v) {
                if (!str_ends_with($k, '_columns')) {
                    continue;
                }
                foreach ($v as $k2 => $v2) {
                    if (isset($v2['loc'])) {
                        $this->loc[$v2['loc']] = $v2['name'];
                    }
                }
            }
        }
        // skip constructor loading
        if (!empty($options['skip_constructor_loading'])) {
            return;
        }
        // detect input type
        $this->input = \Request::input();
        // content type
        $this->content_type = \Application::get('flag.global.__content_type');
        if (!in_array($this->content_type, self::$content_types)) {
            $this->content_type = 'application/json';
        }
    }

    /**
     * Handle output
     *
     * @param mixed $result
     */
    public function handleOutput($result)
    {
        // We allow CORS by refferer.
        if (!empty($this->input['cors'])) {
            header('Access-Control-Allow-Origin: ' . rtrim($_SERVER['HTTP_REFERER'], '/'));
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Accept, Content-Type, Authorization');
        }
        // status
        $status = HTTPConstants::Status200OK;
        if (isset($result['http_status_code'])) {
            $status = $result['http_status_code'];
        }
        // xml or json for now
        switch ($this->content_type) {
            case 'application/xml':
                HTTPResponse::xml($status, $result);
                break;
            case 'application/json':
            default:
                HTTPResponse::json($status, $result);
        }
    }

    /**
     * Error
     *
     * @param string|null $key
     * @param string|null $text
     * @param string|null $field
     * @param array $options
     * @return array
     */
    public function error(?string $key, ?string $text, ?string $field = null, array $options = []): array
    {
        $text = loc($key, $text, $options);
        if (!empty($field)) {
            $this->result['error'][] = '[' . (isset($this->columns[$field]['loc']) ? loc($this->columns[$field]['loc'], $this->columns[$field]['name']) : $this->columns[$field]['name'] ?? $field) . ']: ' . $text;
            array_key_set($this->result['error_in_fields'], $field, $text, ['append_unique' => true]);
        } else {
            $this->result['error'][] = $text;
        }
        return $this->result;
    }

    /**
     * Warning
     *
     * @param string|null $key
     * @param string|null $text
     * @param string|null $field
     * @param array $options
     * @return array
     */
    public function warning(?string $key, ?string $text, ?string $field = null, array $options = []): array
    {
        $text = loc($key, $text, $options);
        if (!empty($field)) {
            $this->result['warning'][] = '[' . (isset($this->columns[$field]['loc']) ? loc($this->columns[$field]['loc'], $this->columns[$field]['name']) : $this->columns[$field]['name']) . ']: ' . $text;
            array_key_set($this->result['warning_in_fields'], $field, $text, ['append_unique' => true]);
        } else {
            $this->result['warning'][] = $text;
        }
        return $this->result;
    }

    /**
     * Warning
     *
     * @param string|null $key
     * @param string|null $text
     * @param string|null $field
     * @param array $options
     * @return array
     */
    public function general(?string $key, ?string $text, array $options = []): array
    {
        $text = loc($key, $text, $options);
        $this->result['error'][] = $text;
        $this->result['general'][] = $text;
        return $this->result;
    }

    /**
     * Finish
     *
     * @return array
     */
    public function finish(int $status = HTTPConstants::Status200OK, array $result = [], bool $rollback = false): array
    {
        // rollback if we are in transaction and not success
        if ($rollback) {
            $db = new \Db();
            if ($db->inTransaction()) {
                $this->rollback();
            }
        }
        // we need to put status into result
        $result['http_status_code'] = $status;
        // merge results
        return array_merge_hard($this->result, $result);
    }

    /**
     * Begin
     *
     * @return void
     */
    public function begin(): void
    {
        $db = new \Db();
        $db->begin();
    }

    /**
     * Rollback
     *
     * @return void
     */
    public function rollback(): void
    {
        $db = new \Db();
        $db->rollback();
    }

    /**
     * Commit
     *
     * @return void
     */
    public function commit(): void
    {
        $db = new \Db();
        $db->commit();
    }
}
