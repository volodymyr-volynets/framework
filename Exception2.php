<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Exception2 extends Exception
{
    /**
     * Constructor
     *
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct(string|array $message, int $code = 0, ?Exception $previous = null)
    {
        // convert array to string
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        // call parent constructor
        parent::__construct($message, $code, $previous);
    }
}
