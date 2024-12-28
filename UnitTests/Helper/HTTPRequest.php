<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace UnitTests\Helper;

use PHPUnit\Framework\TestCase;

class HTTPRequest extends TestCase
{
    /**
     * Test \Helper\HTTPRequest class
     */
    public function testHttpRequestGet200()
    {
        $result = \Helper\HTTPRequest::createStatic()
            ->url('https://httpstat.us/200')
            ->acceptable(\Helper\HTTPRequest::Status200OK)
            ->get()
            ->result();
        $this->assertEquals($result['status'], \Helper\HTTPRequest::Status200OK, 'Status?');
    }

    /**
     * Test \Helper\HTTPRequest class
     */
    public function testHttpRequestGet500()
    {
        $result = \Helper\HTTPRequest::createStatic()
            ->url('https://httpstat.us/500')
            ->acceptable(\Helper\HTTPRequest::Status200OK)
            ->retry(2, 1)
            ->get()
            ->result();
        $this->assertEquals($result['status'], \Helper\HTTPRequest::Status500InternalServerError, 'Status?');
    }

    /**
     * Test \Helper\HTTPRequest class
     */
    public function testHttpRequestPost200()
    {
        $result = \Helper\HTTPRequest::createStatic()
            ->url('https://httpstat.us/200')
            ->accept('application/json')
            ->acceptable(\Helper\HTTPRequest::Status200OK)
            ->param('id', \Db::uuid4(), 'GET')
            ->body([123 => [123]], 'JSON')
            ->post()
            ->result();
        $this->assertEquals($result['status'], \Helper\HTTPRequest::Status200OK, 'Status?');
        $this->assertEquals($result['data']['code'], \Helper\HTTPRequest::Status200OK, 'Code?');
        $this->assertEquals($result['data']['description'], 'OK', 'Code?');
    }

    /**
     * Test \Helper\HTTPRequest class
     */
    public function testHttpRequestPut200()
    {
        $result = \Helper\HTTPRequest::createStatic()
            ->url('https://httpstat.us/200')
            ->accept('application/json')
            ->acceptable(\Helper\HTTPRequest::Status200OK)
            ->param('id', \Db::uuid4(), 'GET')
            ->body([123 => [123]], 'JSON')
            ->put()
            ->result();
        $this->assertEquals($result['status'], \Helper\HTTPRequest::Status200OK, 'Status?');
        $this->assertEquals($result['data']['code'], \Helper\HTTPRequest::Status200OK, 'Code?');
        $this->assertEquals($result['data']['description'], 'OK', 'Code?');
    }

    /**
     * Test \Helper\HTTPRequest class
     */
    public function testHttpRequestDelete200()
    {
        $result = \Helper\HTTPRequest::createStatic()
            ->url('https://httpstat.us/')
            ->path('{code}', ['code' => 200])
            ->accept('application/json')
            ->acceptable(\Helper\HTTPRequest::Status200OK)
            ->delete()
            ->result();
        $this->assertEquals($result['status'], \Helper\HTTPRequest::Status200OK, 'Status?');
        $this->assertEquals($result['data']['code'], \Helper\HTTPRequest::Status200OK, 'Code?');
        $this->assertEquals($result['data']['description'], 'OK', 'Code?');
    }
}
