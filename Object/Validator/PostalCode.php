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

class PostalCode extends Base
{
    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $value .= '';
        // strip all white spaces
        $value = preg_replace('/\s+/', '', trim($value));
        // find country
        $country = null;
        foreach ($options['neighbouring_values'] as $k => $v) {
            if (strpos($k, 'country_code') !== false) {
                $country = $v;
                break;
            }
        }
        // postal code is different based on country
        switch ($country) {
            case 'BR':
                $result['placeholder'] = '#####-000';
                if (!(preg_match('/^[0-9]{5}$/', $value) || preg_match('/^([0-9]{5})-([0-9]{3})$/', $value))) {
                    $result['error'][] = 'Invalid postal code!';
                } else {
                    $result['data'] = $value;
                    $result['success'] = true;
                }
                break;
            case 'CA':
                $result['placeholder'] = 'A#B#C#';
                if (!preg_match('/^[a-z][0-9][a-z][0-9][a-z][0-9]$/i', $value)) {
                    $result['error'][] = 'Invalid postal code!';
                } else {
                    $result['data'] = strtoupper($value);
                    $result['success'] = true;
                }
                break;
            case 'MX':
                $result['placeholder'] = '#####';
                if (!(preg_match('/^[0-9]{5}$/', $value) || preg_match('/^[0-9]{4}$/', $value) || preg_match('/^([0-9]{4})-([0-9]{4})$/', $value))) {
                    $result['error'][] = 'Invalid postal code!';
                } else {
                    $result['data'] = $value;
                    $result['success'] = true;
                }
                break;
            case 'US':
                $result['placeholder'] = '#####-####';
                if (!(preg_match('/^[0-9]{5}$/', $value) || preg_match('/^([0-9]{5})-([0-9]{4})$/', $value))) {
                    $result['error'][] = 'Invalid zip code!';
                } else {
                    $result['data'] = $value;
                    $result['success'] = true;
                }
                break;
            default:
                $result['placeholder'] = '';
                $result['data'] = $value;
        }
        return $result;
    }
}
