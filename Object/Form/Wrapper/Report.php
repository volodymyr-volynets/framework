<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Wrapper;

class Report extends Base
{
    /**
     * Constructor
     *
     * @see \Object\Form\Wrapper\Base::construct()
     */
    public function __construct($options = [])
    {
        $options['initiator_class'] = 'report';
        parent::__construct($options);
    }
}
