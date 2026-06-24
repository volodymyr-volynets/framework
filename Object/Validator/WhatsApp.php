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

class WhatsApp extends Base
{
    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = '+###########';
        $plain = self::plainNumber($value . '');
        if ($plain != $value || !str_starts_with($value . '', '+') || strlen($plain . '') < 11) {
            $result['error'][] = 'Invalid Phone number!';
        } else {
            $result['success'] = true;
            $result['data'] = $value . '';
        }
        return $result;
    }

    /**
     * Generate plain number
     *
     * @param string $value
     * @return int
     */
    public static function plainNumber(string $value): int
    {
        $temp = explode('ext', $value);
        $result = preg_replace('/[^\+0-9]/', '', $temp[0]);
        return (int) $result;
    }
}
