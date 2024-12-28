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

class Month extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'MM';
        $result['placeholder_select'] = 'Month';
        $value = (int) $value;
        if ($value < 1 || $value > 12) {
            $result['error'][] = 'Invalid month!';
        } else {
            $result['success'] = true;
            $result['data'] = $value;
        }
        return $result;
    }
}
