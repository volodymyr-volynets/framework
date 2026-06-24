<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace UnitTests\Db;

use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    /**
     * Init
     */
    public function testInitialize()
    {
        // tenant
        $tenant_id = (int) \Application::get('phpunit.tenant_default_id');
        $this->assertEquals(true, !empty($tenant_id));
        \Tenant::setOverrideTenantId($tenant_id);
        // db
        \Db::backendInitializedStatic('default', true);
    }

    /**
     * Test Prepare Exacute Deallocate
     */
    public function testPrepareExacuteDeallocate()
    {
        $sql = 'SELECT ? AS a, ? AS b, ? AS c';
        $result = \Db::prepareStatic('default', 'test_prepared_statement_001', $sql);
        $this->assertEquals($result['success'], true, 'Prepare? ' . $result['sql']);
        $result = \Db::executeStatic('default', $result['name'], [1, '2', 3]);
        $this->assertEquals($result['success'], true, 'Execute? ' . $result['sql']);
        $this->assertEquals($result['rows'][0], ['a' => 1, 'b' => '2', 'c' => 3], 'Rows? ' . print_r($result['rows'][0], true));
        $result = \Db::deallocateStatic('default', 'test_prepared_statement_001');
        $this->assertEquals($result['success'], true, 'Deallocate? ' . $result['sql']);
    }

    /**
     * Test Prepare Exacute Deallocate
     */
    public function testTemplateInPrepare()
    {
        $result = \Db::prepareStatic('default', 'test_prepared_statement_002', 'template:///Numbers/Framework/UnitTests/Db/DbTest.template.sql', ['x1' => '01', 'x2' => '02', 'x3' => '03', 'x4' => [1, 2, 3]]);
        $this->assertEquals($result['success'], true, 'Prepare? ' . $result['sql']);
        $result = \Db::executeStatic('default', $result['name'], [1, '2', 3]);
        $this->assertEquals($result['success'], true, 'Execute? ' . $result['sql']);
        $this->assertEquals($result['rows'][0], ['a' => 1, 'b' => '2', 'c' => 3, 'd' => '01-02-03'], 'Rows? ' . print_r($result['rows'][0], true));
        $result = \Db::deallocateStatic('default', 'test_prepared_statement_002');
        $this->assertEquals($result['success'], true, 'Deallocate? ' . $result['sql']);
    }
}
