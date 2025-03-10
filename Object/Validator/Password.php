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

class Password extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $value = $value . '';
        $result = $this->result;
        $result['placeholder'] = '8 characters, 1 number, 1 letter';
        if (strlen($value) < 8) {
            $result['error'][] = 'Password too short, should be atleast 8 characters!';
        }
        if (!preg_match('#[0-9]+#', $value)) {
            $result['error'][] = 'Password must include at least one number!';
        }
        if (!preg_match('#[a-zA-Z]+#', $value)) {
            $result['error'][] = 'Password must include at least one letter!';
        }
        // see if we have repeat
        if (isset($options['neighbouring_values'][$options['options']['name'] . '2'])) {
            if ($options['neighbouring_values'][$options['options']['name'] . '2'] != $value) {
                $result['error'][] = 'Password must match repeat!';
            }
        }
        if (empty($result['error'])) {
            $result['success'] = true;
            $result['data'] = $value;
        }
        return $result;
    }
}
