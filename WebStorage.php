<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

#[AllowDynamicProperties]
class WebStorage
{
    public const LOCAL_STORAGE = 'local';
    public const SESSION_STORAGE = 'session';

    /**
     * @var string
     */
    public string $type = self::LOCAL_STORAGE;

    /**
     * Constructor
     */
    public function __construct(string $type = self::LOCAL_STORAGE)
    {
        $this->type = $type;
    }

    /**
     * Magic get
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return self::getStatic($name, $this->type);
    }

    /**
     * Magic set
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value): void
    {
        self::setStatic($name, $value, $this->type);
    }

    /**
     * Get (static)
     *
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public static function getStatic(string $name, string $type = self::LOCAL_STORAGE): mixed
    {
        self::checkForVaildKey($name);
        return Application::get('storages.web.' . $type . '.' . $name);
    }

    /**
     * Set (static)
     *
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public static function setStatic(string $name, mixed $value, string $type = self::LOCAL_STORAGE): void
    {
        self::checkForVaildKey($name);
        Application::set('storages.web.' . $type . '.' . $name, $value);
    }

    /**
     * Remove (static)
     *
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public static function removeStatic(string $name, string $type = self::LOCAL_STORAGE): mixed
    {
        self::checkForVaildKey($name);
        Application::set('storages.web.' . $type . '_remove.' . $name, true);
        return Application::get('storages.web.' . $type . '.' . $name, ['unset' => true]);
    }

    /**
     * Clear (static)
     *
     * @param string $type
     * @return mixed
     */
    public static function clearStatic(string $type = self::LOCAL_STORAGE): mixed
    {
        Application::set('storages.web.' . $type . '_clear', true);
        return Application::get('storages.web.' . $type, ['unset' => true]);
    }

    /**
     * Render Javascript (static)
     *
     * @param string $type
     * @return void
     */
    public static function renderJavascriptStatic(string $type = self::LOCAL_STORAGE): string
    {
        $result = [];
        // clear first
        if (Application::get('storages.web.' . $type . '_clear')) {
            $result[] = $type . 'Storage.clear();';
        }
        // remove specified keys
        foreach (Application::get('storages.web.' . $type . '_remove') ?? [] as $k => $v) {
            $result[] = $type . 'Storage.removeItem("' . $k . '");';
        }
        // dump all keys
        foreach (Application::get('storages.web.' . $type) ?? [] as $k => $v) {
            $result[] = $type . 'Storage.setItem("' . $k . '", ' . json_encode($v) . ');';
        }
        return implode(PHP_EOL, $result);
    }

    /**
     * Check for valid key
     *
     * @param string $key
     * @throws Exception
     * @return bool
     */
    public static function checkForVaildKey(string $key): bool
    {
        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $key)) {
            return true;
        }
        throw new Exception('WebStorage: invalid key provided: ' . $key . '!');
    }
}
