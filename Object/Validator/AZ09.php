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

class AZ09 extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'A#-_';
        $result['placeholder_select'] = '';
        $value .= '';
        if (!preg_match("/^[a-zA-Z0-9\-\_]+$/", $value)) {
            $result['error'][] = loc('NF.Error.InvalidStringLettersNumbersDashesUnderscores', 'String must contain letters, numbers, dashes and underscores!');
        } else {
            $result['success'] = true;
            $result['data'] = $value;
        }
        return $result;
    }
}
