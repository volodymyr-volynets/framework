<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Miscellaneous;

class IP
{
    /**
     * Database object
     *
     * @var object
     */
    public $object;

    /**
     * Options
     *	cache_link
     *	crypt_link
     *
     * @var array
     */
    public $options = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $class = \Application::get('flag.global.ip.submodule', ['class' => true]);
        // check if backend has been enabled
        if (!\Application::get($class, ['submodule_exists' => true])) {
            throw new \Exception('You must enable ' . $class . ' first!');
        }
        // creating new class
        $this->object = new $class(\Application::get('flag.global.ip.options') ?? []);
    }

    /**
     * Get
     *
     * @param array $options
     * @return array
     */
    public function get(string $ip): array
    {
        return $this->object->get($ip);
    }
}
