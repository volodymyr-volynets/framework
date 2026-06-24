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

class Dimensions extends Base
{
    /**
     * @var array
     */
    public $loc = [
        'NF.Validator.WidthXHeight' => 'Width x Height',
    ];

    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder_select'] = $result['placeholder'] = loc('NF.Validator.WidthXHeight', 'Width x Height');
        $temp = explode('x', $value . '');
        if (count($temp) != 2) {
            $result['error'][] = loc('NF.Error.InvalidValues', 'Invalid value(s)!');
        } elseif (intval($temp[0]) == 0) {
            $result['error'][] = loc('NF.Error.InvalidValues', 'Invalid value(s)!');
        } elseif (intval($temp[1]) == 0) {
            $result['error'][] = loc('NF.Error.InvalidValues', 'Invalid value(s)!');
        }
        if (empty($result['error'])) {
            $result['success'] = true;
            $result['data'] = $temp[0] . 'x' . $temp[1];
        }
        return $result;
    }
}
