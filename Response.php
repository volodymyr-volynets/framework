<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\HTTPResponse;

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Response
{
    /**
     * @var array
     */
    protected array $result = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * Constructor
     *
     * @param mixed $data
     * @param array $result
     */
    public function __construct(mixed $data = null, array $result = [], array $options = [])
    {
        $this->data = $data;
        $this->options = $options;
        $this->result = $result;
    }

    /**
     * JSON
     *
     * @param int $status
     * @param mixed $body
     */
    public function json(int $status, mixed $body): void
    {
        HTTPResponse::json($status, $body);
    }

    /**
     * XML
     *
     * @param int $status
     * @param mixed $body
     */
    public function xml(int $status, mixed $body): void
    {
        HTTPResponse::xml($status, $body);
    }

    /**
     * Text
     *
     * @param int $status
     * @param mixed $body
     */
    public function text(int $status, mixed $body): void
    {
        HTTPResponse::text($status, $body);
    }

    /**
     * HtML
     *
     * @param int $status
     * @param mixed $body
     */
    public function html(int $status, mixed $body): void
    {
        HTTPResponse::html($status, $body);
    }
}
