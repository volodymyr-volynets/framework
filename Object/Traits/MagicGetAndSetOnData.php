<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Traits;

trait MagicGetAndSetOnData
{
    /**
     * Get (magic)
     *
     * @param mixed $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->data[$key];
    }

    /**
     * Set (magic)
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->values[ $key ] = $value;
    }
}
