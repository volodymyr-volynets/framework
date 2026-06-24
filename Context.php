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
use Object\Mask\Name;

class Context
{
    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * @var array
     */
    protected static array $chat_context = [];

    /**
     * Get (static)
     *
     * @param array|string $keys
     * @param mixed $default
     * @return mixed
     */
    public static function getStatic(array|string|null $keys = null, mixed $default = null): mixed
    {
        if (is_null($keys)) {
            return self::$data;
        }
        return array_key_get(self::$data, $keys) ?? $default;
    }

    /**
     * Set (static)
     *
     * @param array|string $keys
     * @param mixed $value
     * @param bool $override
     * @return void
     */
    public static function setStatic(array|string $keys, mixed $value = null, bool $override = true): void
    {
        if (is_array($keys)) {
            if ($override) {
                self::$data = array_merge_hard(self::$data, $keys);
            } else {
                self::$data = array_merge_hard($keys, self::$data);
            }
        } elseif (is_string($keys)) {
            if ($override || !array_key_exists($keys, self::$data)) {
                self::$data[$keys] = $value;
            }
        }
    }

    /**
     * When (static)
     *
     * @param mixed $condition
     * @param array $true
     * @param array $false
     * @return void
     */
    public static function when($condition, array $true = [], array $false = []): void
    {
        if ($condition) {
            if (count($true) > 0) {
                self::setStatic($true);
            }
        } else {
            if (count($false) > 0) {
                self::setStatic($false);
            }
        }
    }

    /**
     * Init default values
     *
     * @return void
     */
    public static function initDefaultValues(): void
    {
        // if run is from console
        if (Cmd::isCli()) {
            Context::setStatic('cli', 'console');
        } else {
            Context::setStatic('host', Request::host());
            Context::setStatic('request_uri', $_SERVER['REQUEST_URI'] ?? '');
        }
        // tenant information
        Context::setStatic('tenant_id', Tenant::id());
        // user
        if (User::authorized()) {
            $mask_name = new Name();
            Context::setStatic([
                'user_id' => User::id(),
                'user_name' => $mask_name->mask(User::get('name')),
            ]);
        }
    }

    /**
     * Get chat (static)
     *
     * @param array|string|null $keys
     * @param mixed $default
     * @return mixed
     */
    public static function getChatStatic(array|string|null $keys = null, mixed $default = null): mixed
    {
        return array_key_get(self::$chat_context, $keys) ?? $default;
    }

    /**
     * Set chat (static)
     *
     * @param array|string $keys
     * @param mixed $value
     * @return void
     */
    public static function setChatStatic(array|string $keys, mixed $value = null): void
    {
        array_key_set(self::$chat_context, $keys, $value);
    }
}
