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

class Phone extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = '# (###) ###-#### ext ####';
        $plain = self::plainNumber($value . '');
        if (!preg_match('/^[0-9+\(\)#\.\s\/ext-]+$/', $value . '') || strlen($plain . '') < 11) {
            $result['error'][] = 'Invalid phone number!';
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
        $result = preg_replace('/[^0-9]/', '', $temp[0]);
        return (int) $result;
    }

    /**
     * Format
     *
     * @param mixed $value
     * @param array $options
     * @retrun string
     */
    public function format($value, array $options = []): string
    {
        $value = preg_replace('/[^0-9]/', '', (string) $value);
        if (strlen($value) > 10) {
            $country_code = substr($value, 0, strlen($value) - 10);
            $area_code = substr($value, -10, 3);
            $next_three = substr($value, -7, 3);
            $last_four = substr($value, -4, 4);
            $result = '+' . $country_code . ' ('.$area_code.') ' . $next_three . '-' . $last_four;
        } elseif (strlen($value) == 10) {
            $area_code = substr($value, 0, 3);
            $next_three = substr($value, 3, 3);
            $last_four = substr($value, 6, 4);
            $result = '(' . $area_code . ') ' . $next_three . '-' . $last_four;
        } elseif (strlen($value) == 7) {
            $next_three = substr($value, 0, 3);
            $last_four = substr($value, 3, 4);
            $result = $next_three . '-' . $last_four;
        } else {
            $result = (string) $value;
        }
        return $result;
    }
}
