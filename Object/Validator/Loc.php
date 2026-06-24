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

class Loc extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'NF.xxx.xxx';
        $result['placeholder_select'] = 'NF.xxx.xxx';
        if (is_loc($value)) {
            $result['success'] = true;
            $result['data'] = $value;
        } else {
            $result['error'][] = 'Invalid Localization Key!';
        }
        return $result;
    }
}
