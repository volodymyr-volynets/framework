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

use Object\Content\Messages;

class LowerCase extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'lowercase only';
        $value .= '';
        if (strtolower($value) !== $value) {
            $result['error'][] = Messages::STRING_LOWERCASE;
        } else {
            $result['success'] = true;
            $result['data'] = $value;
        }
        return $result;
    }
}
