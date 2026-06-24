<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Bool2
 */
class Bool2
{
    /**
     * @var bool|null
     */
    protected bool|null $data = null;

    /**
     * Constructor
     *
     * @param mixed $data
     */
    public function __construct(mixed $data)
    {
        if (is_string($data)) {
            $data = strtolower($data);
            if (in_array($data, ['0', 'false', 'no', 'off', 'null', 'n'], true)) {
                $this->data = false;
            } elseif (in_array($data, ['1', 'true', 'yes', 'on', 'y'], true)) {
                $this->data = true;
            } else {
                $this->data = null;
            }
        } else {
            $this->data = boolval($data);
        }
    }

    /**
     * To bool
     *
     * @return bool
     */
    public function toBool(): bool|null
    {
        return $this->data;
    }
}
