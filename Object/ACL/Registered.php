<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\ACL;

use Numbers\Backend\Db\Common\Query\Builder;

abstract class Registered
{
    /**
     * Models
     *
     * @var array
     */
    public $models = [
        //'\Model' => []
    ];

    /**
     * Cached models
     *
     * @var array
     */
    public static $cached_models;

    /**
     * Execute
     *
     * @param Builder $query
     */
    abstract public function execute(Builder & $query, array $options = []);

    /**
     * Can
     *
     * @param string $model
     * @return boolean
     */
    public static function can(string $model): bool
    {
        // load models
        if (!isset(self::$cached_models)) {
            $file = './Overrides/Class/Override_Object_ACL_Registered.php';
            if (file_exists($file)) {
                require($file);
                self::$cached_models = $object_override_blank_object;
            } else {
                self::$cached_models = [];
            }
        }
        return !empty(self::$cached_models[$model]);
    }

    /**
     * Process
     *
     * @param string $model
     * @param Builder $query
     * @return boolean
     */
    public static function process(string $model, Builder & $query, array $options = [])
    {
        if (!self::can($model)) {
            return false;
        }
        foreach (self::$cached_models[$model] as $k => $v) {
            $object = new $k();
            $object->execute($query, $options);
        }
        return true;
    }
}
