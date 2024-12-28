<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Error;

class PermissionException extends \Exception
{
    /**
     * Constructor
     *
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct(string $message, int $code = 0, ?\Exception $previous = null)
    {
        // call parent constructor
        parent::__construct($message, $code, $previous);
    }
}
