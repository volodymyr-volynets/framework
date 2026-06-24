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

class IP extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'n.n.n.n';
        $result['placeholder_select'] = 'IP';
        // IPv4
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $result['success'] = true;
            $result['data'] = $value;
        } elseif (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $result['success'] = true;
            $result['data'] = $value;
        } else {
            $result['error'][] = 'Invalid IP!';
        }
        return $result;
    }
}
