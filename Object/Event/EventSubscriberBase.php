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

class EventSubscriberBase
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

    /**
     * Execute
     *
     * @param string $request_id
     * @param string $event_code
     * @param mixed $data
     * @param array $options
     * @return array
     */
    protected function execute(string $request_id, string $event_code, mixed $data, array $options = []): array
    {
        return ['success' => true, 'error' => []];
    }
}
