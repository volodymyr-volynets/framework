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

class EmailPhoneUsername extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'name@example.com or +11231231234 or dummy12';
        if (strpos($value . '', '@') !== false) {
            $data = filter_var($value, FILTER_VALIDATE_EMAIL);
            if ($data !== false) {
                $result['success'] = true;
                $result['data'] = strtolower($data . '');
            } else {
                $result['error'][] = 'Invalid email address!';
            }
        } elseif (strpos($value . '', '+') !== false) {
            $plain = Phone::plainNumber($value . '');
            $value2 = ltrim($value . '', '+');
            if (!preg_match('/^[0-9+\(\)#\.\s\/ext-]+$/', $value2) || strlen($plain . '') < 11) {
                $result['error'][] = 'Invalid phone number!';
            } else {
                $result['success'] = true;
                $result['data'] = $value . '';
            }
        } else {
            if (strtolower($value . '') !== $value) {
                $result['error'][] = Messages::STRING_LOWERCASE;
            } else {
                $result['success'] = true;
                $result['data'] = $value . '';
            }
        }
        return $result;
    }
}
