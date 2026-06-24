<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace UnitTests\ConcurrentCalls;

use PHPUnit\Framework\TestCase;

class ConcurrentCallsTest extends TestCase
{
    /**
     * Test Concurrent Calls
     */
    public function testConcurrentCalls()
    {
        $ccals = new \ConcurrentCalls();
        $result = $ccals->add(function ($var) {
            sleep(1);
            return $var;
        }, [123])
        ->add(function ($var) {
            sleep(1);
            return $var;
        }, [321])
        ->run();
        $this->assertEquals($result['data'][0], 123);
        $this->assertEquals($result['data'][1], 321);
    }
}
