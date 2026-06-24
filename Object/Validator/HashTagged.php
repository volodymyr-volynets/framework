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

use NF\Error;

class HashTagged extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = '#hash_tag';
        if (!str_starts_with($value, '#') || strpos($value, ' ') !== false) {
            $result['error'][] = loc(Error::INVALID_HASH_TAGGED_GIVEN);
        } else {
            $result['success'] = true;
            $result['data'] = $value . '';
        }
        return $result;
    }
}
