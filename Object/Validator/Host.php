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

class Host extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'http://www.domain.com/';
        $result['placeholder_select'] = 'Host';
        $value = (string) $value;
        // Host
        if (!(str_starts_with($value, 'http://') || str_starts_with($value, 'ftp://') || str_starts_with($value, 'https://'))) {
            $result['error'][] = 'Host should start with http or https!';
        }
        if (!str_ends_with($value, '/')) {
            $result['error'][] = 'Host should end with /!';
        }
        $result['success'] = empty($result['error']);
        $result['data'] = $value;
        return $result;
    }
}
