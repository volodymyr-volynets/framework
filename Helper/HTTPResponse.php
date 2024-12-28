<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper;

use Helper\Constant\HTTPConstants;

class HTTPResponse extends HTTPConstants
{
    /**
     * Accept
     */
    public const ACCEPTS = ['application/json', 'application/xml', 'text/html', 'text/plain'];

    /**
     * JSON
     *
     * @param int $status
     * @param mixed $body
     */
    public static function json(int $status, mixed $body): void
    {
        self::output($status, 'application/json', $body);
    }

    /**
     * XML
     *
     * @param int $status
     * @param mixed $body
     */
    public static function xml(int $status, mixed $body): void
    {
        self::output($status, 'application/xml', $body);
    }

    /**
     * HTML
     *
     * @param int $status
     * @param mixed $body
     */
    public static function html(int $status, mixed $body): void
    {
        if (!is_string($body)) {
            $body = array2xml($body);
        }
        self::output($status, 'text/htmlplain', $body);
    }

    /**
     * Text
     *
     * @param int $status
     * @param mixed $body
     */
    public static function text(int $status, mixed $body): void
    {
        self::output($status, 'text/plain', $body);
    }

    /**
     * Output
     *
     * @param int $status
     * @param string $content_type
     * @param mixed $body
     */
    public static function output(int $status, string $content_type, mixed $body): void
    {
        if ($content_type == 'text/html') {
            $content_type == 'text/htmlplain';
        }
        \Layout::renderAs($body, $content_type, [
            'status' => $status,
        ]);
    }

    /**
     * Render
     *
     * @param int $status
     * @param mixed $body
     */
    public static function render(int $status, mixed $body): void
    {
        $content_type = \Application::get('flag.global.__accept') ?? 'application/json';
        if (!in_array($content_type, self::ACCEPTS)) {
            $content_type = 'application/json';
        }
        self::output($status, $content_type, $body);
    }
}
