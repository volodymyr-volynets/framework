<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Validator\Domain;

use Object\Validator\Base;

class Part extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $value = $value ?? '';
        if (!preg_match('/[^a-zA-Z0-9_]+/', $value)) {
            $result['success'] = true;
            $result['data'] = strtolower($value);
        } else {
            $result['error'][] = 'Invalid domain part!';
        }
        return $result;
    }
}
