<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Validator;

class Year extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'YYYY';
        $result['placeholder_select'] = 'Year';
        $value = (int) $value;
        if ($value < 1000 || $value > 9999) {
            $result['error'][] = 'Invalid year!';
        } else {
            $result['success'] = true;
            $result['data'] = $value;
        }
        return $result;
    }
}
