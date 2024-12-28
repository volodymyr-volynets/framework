<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Event;

class EventBase
{
    /**
     * Code
     *
     * @var string
     */
    public $code;

    /**
     * Name
     *
     * @var string
     */
    public $name;

    public function __construct()
    {

    }

    /**
     * Validate
     *
     * @param mixed $data
     * @param array $options
     * @return array
     */
    public function validate(mixed $data, array $options = []): array
    {
        return ['success' => true, 'error' => []];
    }

    /**
     * Dispatch
     *
     * @param mixed $data
     * @param array $options
     * @return array
     */
    public function dispatch(mixed $data, array $options = []): array
    {
        $result = $this->validate($data, $options);
        if (!$result['success']) {
            return $result;
        }
        return \Event::dispatch($this->code, $data, $options);
    }
}
