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

class Wizard
{
    /**
     * Options
     *
     * @var array
     */
    public $options = [];

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $result = \HTML::wizard([
            'type' => $this->options['wizard']['type'] ?? null,
            'step' => $this->options['wizard']['__wizard_step'] ?? $this->options['input']['__wizard_step'] ?? null,
            'options' => $this->options['wizard']['options'] ?? []
        ]);
        if (!empty($result)) {
            $result .= \HTML::hr();
        }
        return $result;
    }
}
