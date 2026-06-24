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

class AccessKey extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'XXXX XXXX XXXX XXXX';
        $result['placeholder_select'] = 'Access Key';
        if (strlen($value . '') < 16) {
            $result['error'][] = 'Invalid Access Key!';
        } else {
            $result['success'] = true;
            $result['data'] = $value . '';
        }
        return $result;
    }
}
