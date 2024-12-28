<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Value;

class Math
{
    /**
     * Scale
     *
     * @var int
     */
    private $scale = 2;

    /**
     *
     * @var type
     */
    private $result = '0';

    /**
     * Options
     *
     * @var array
     */
    private $options = [
        'default' => []
    ];

    /**
     * Constructor
     */
    public function __construct(int $scale = 2, string $result = '0')
    {
        $this->scale = $this->options['default']['scale'] = $scale;
        $this->result = $this->options['default']['result'] = \Math::truncate($result, $this->scale);
    }

    /**
     * Reset
     *
     * @return Math
     */
    public function reset(): Math
    {
        foreach ($this->options['default'] as $k => $v) {
            $this->{$k} = $v;
        }
        return $this;
    }

    /**
     * Set scale
     *
     * @param int $scale
     * @return Math
     */
    public function scale(int $scale): Math
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * Get result
     *
     * @return string
     */
    public function result(): string
    {
        return $this->result;
    }

    /**
     * Add
     *
     * @param mixed $arg1
     * @return Math
     */
    public function add($arg1): Math
    {
        \Math::add2($this->result, $arg1, $this->scale);
        return $this;
    }

    /**
     * Subtract
     *
     * @param mixed $arg1
     * @return Math
     */
    public function subtract($arg1): Math
    {
        \Math::subtract2($this->result, $arg1, $this->scale);
        return $this;
    }

    /**
     * Multiply
     *
     * @param mixed $arg1
     * @return Math
     */
    public function multiply($arg1): Math
    {
        $temp = \Math::multiply($this->result, $arg1, \Math::double($this->scale));
        $this->result = \Math::truncate($temp, $this->scale);
        return $this;
    }

    /**
     * Divide
     *
     * @param mixed $arg1
     * @return Math
     */
    public function divide($arg1): Math
    {
        $temp = \Math::divide($this->result, $arg1, \Math::double($this->scale));
        $this->result = \Math::truncate($temp, $this->scale);
        return $this;
    }
}
