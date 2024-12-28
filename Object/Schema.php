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

class Schema
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
     * Extension name
     *
     * @var string
     */
    public $name;

    /**
     * Constructing object
     *
     * @throws Exception
     */
    public function __construct()
    {
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
                throw new \Exception('Could not determine db link in schema!');
            }
        }
    }
}
