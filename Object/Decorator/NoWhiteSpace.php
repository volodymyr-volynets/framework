<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Decorator;

class NoWhiteSpace extends Base
{
    /**
     * @var array
     */
    public $loc = [
        'NF.Validator.StringsContainsWhiteSpace' => 'String(s) contains white space!'
    ];

    /**
     * @see Base::decorate()
     */
    public function decorate($value, $options = [])
    {
        $result = $this->result;
        $result['data'] = preg_replace('/\s+/', '', (string) $value);
        if ($value != $result['data']) {
            $result['error'][] = loc('NF.Validator.StringsContainsWhiteSpace', 'String(s) contains white space!');
        } else {
            $result['success'] = true;
        }
        return $result;
    }
}
