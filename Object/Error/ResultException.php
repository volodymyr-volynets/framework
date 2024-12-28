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

class ResultException extends \Exception
{
    /**
     * Constructor
     *
     * @param string|array $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(string|array $message, int $code = 0, ?\Exception $previous = null)
    {
        // array messages
        if (is_array($message)) {
            // we can pass entiry result object in here
            if (array_key_exists('error', $message)) {
                $message = implode("\n", $message['error']);
            } else {
                $message = implode("\n", $message);
            }
        }
        // call parent constructor
        parent::__construct($message, $code, $previous);
    }
}
