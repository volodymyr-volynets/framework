<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object;

use Object\Override\Data;

class Sequence extends Data
{
    /**
     * Link to database
     *
     * @var string
     */
    public $db_link;

    /**
     * Override for link to database
     *
     * @var string
     */
    public $db_link_flag;

    /**
     * Schema name
     *
     * @var string
     */
    public $schema = '';

    /**
     * Sequence name
     *
     * @var string
     */
    public $name;

    /**
     * Full sequence name
     *
     * @var string
     */
    public $full_sequence_name;

    /**
     * Sequence type
     *		global_simple
     *		global_advanced
     *		tenant_simple
     *		tenant_advanced
     *		module_simple
     *		module_advanced
     *
     * @var string
     */
    public $type = "global_simple";

    /**
     * Sequence prefix
     *
     * @var string
     */
    public $prefix;

    /**
     * Sequence length
     *
     * @var int
     */
    public $length = 0;

    /**
     * Sequence suffix
     *
     * @var string
     */
    public $suffix;

    /**
     * Constructing object
     *
     * @throws Exception
     */
    public function __construct()
    {
        // we need to handle overrrides
        parent::overrideHandle($this);
        // we need to determine db link
        if (empty($this->db_link)) {
            // get from flags first
            if (!empty($this->db_link_flag)) {
                $this->db_link = \Application::get($this->db_link_flag);
            }
            // get default link
            if (empty($this->db_link)) {
                $this->db_link = \Application::get('flag.global.default_db_link');
            }
            // if we could not determine the link we throw exception
            if (empty($this->db_link)) {
                throw new \Exception('Could not determine db link in sequnce!');
            }
        }
        // process sequence name and schema
        $db_object = \Factory::get(['db', $this->db_link, 'object']);
        if (isset($db_object) && method_exists($db_object, 'handleName')) {
            $this->full_sequence_name = $db_object->handleName($this->schema, $this->name);
        } else {
            if (!empty($this->schema)) {
                $this->full_sequence_name = $this->schema . '.' . $this->name;
            } else {
                $this->full_sequence_name = $this->name;
                $this->schema = '';
            }
        }
        // data fixes
        if (!isset($this->length)) {
            $this->length = 0;
        }
    }

    /**
     * Get next sequence number
     *
     * @param string|null $type
     *	simple
     *	advanced
     * @return array
     */
    public function nextval($type = null)
    {
        $result = $this->getByType('nextval');
        if (!empty($type)) {
            return $result[$type];
        }
        return $result;
    }

    /**
     * Get current sequence value
     *
     * @param string|null $type
     *	simple
     *	advanced
     * @return type
     */
    public function currval($type = null)
    {
        $result = $this->getByType('currval');
        if (!empty($type)) {
            return $result[$type];
        }
        return $result;
    }

    /**
     * Get next sequence number
     *
     * @param string $type
     * @param int $tenant
     * @param int $module
     * @return mixed
     */
    public function getByType($type, $tenant = null, $module = null)
    {
        $result = [
            'success' => false,
            'error' => [],
            'simple' => null,
            'advanced' => null
        ];
        $db = new \Db($this->db_link);
        $temp = $db->sequence($this->full_sequence_name, $type, $tenant, $module);
        if (!$temp['success']) {
            $result['error'] = $temp['error'];
        } elseif (!empty($temp['rows'][0])) {
            if (in_array($this->type, ['global_advanced', 'tenant_advanced', 'module_advanced'])) {
                $result['simple'] = $temp['rows'][0]['counter'];
                $sequence = $result['simple'] . '';
                // if we need to pad sequence
                if (strlen($sequence) < $this->length) {
                    $sequence = str_pad($sequence, $this->length, '0', STR_PAD_LEFT);
                }
                $result['advanced'] = $this->prefix . $sequence . $this->suffix;
            } else {
                $result['simple'] = $result['advanced'] = $temp['rows'][0]['counter'];
            }
            $result['success'] = true;
        }
        return $result;
    }
}
