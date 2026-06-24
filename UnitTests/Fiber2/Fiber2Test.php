<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace UnitTests\Fiber2;

use PHPUnit\Framework\TestCase;

class Fiber2Test extends TestCase
{
    /**
     * Test Fiber2 run
     */
    public function testFiber2Run()
    {
        $fiber = new \Fiber2();
        $result = $fiber->add('Return 1', function ($var) {
            sleep(2);
            return $var;
        }, [123])
        ->add('Return 2', function ($var) {
            sleep(1);
            return $var;
        }, [321])
        ->run();
        $this->assertEquals($result['data']['Return 1'], 123);
        $this->assertEquals($result['data']['Return 2'], 321);
    }

    /**
     * Test Fiber2 iterate
     */
    public function testFiber2Iterate()
    {
        $fiber = new \Fiber2();
        $result = $fiber->add('Return 1', function ($var) {
            sleep(2);
            return $var;
        }, [123])
        ->add('Return 2', function ($var) {
            sleep(1);
            return $var;
        }, [123])
        ->add('Return 3', function ($var) {
            sleep(1);
            throw new \Exception('Error!');
            return $var;
        }, [123]);
        foreach ($result->iterate() as $k => $v) {
            $this->assertTrue(str_starts_with($k, 'Return '));
            $this->assertTrue($v instanceof \Throwable || $v == 123);
        }
    }
}
