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

class Email extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'example@domain.com';
        $data = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($data !== false) {
            $result['success'] = true;
            $result['data'] = strtolower($data . '');
        } else {
            $result['error'][] = 'Invalid email address!';
        }
        return $result;
    }
}
