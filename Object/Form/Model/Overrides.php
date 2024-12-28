<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Model;

use Object\Override\Data;

class Overrides extends Data
{
    /**
     * Data
     *
     * @var array
     */
    public $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // we need to handle overrrides
        parent::overrideHandle($this);
    }

    /**
     * Get overrides
     *
     * @param string $form_class
     * @return array
     */
    public function getOverrides(string $form_class): array
    {
        $form_class = '\\' . ltrim($form_class, '\\');
        return array_keys($this->data[$form_class] ?? []);
    }
}
