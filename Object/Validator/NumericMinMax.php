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

class NumericMinMax extends Base
{
    /**
     * @var array
     */
    public $loc = [
        'NF.Validator.InvalidMinValue' => 'Invalid value, min {value}!',
        'NF.Validator.InvalidMaxValue' => 'Invalid value, max {value}!',
        'NF.Validator.NotNumeric' => 'Not numeric value!',
    ];

    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'NNNN';
        $result['placeholder_select'] = 'Number';
        // numeric
        if (!is_numeric($value) || floatval($value) != $value) {
            $result['error'][] = loc('NF.Validator.NotNumeric', 'Not numeric value!');
        }
        // min
        if (isset($options['min'])) {
            if ($value < $options['min']) {
                $result['error'][] = loc('NF.Validator.InvalidMinValue', '', ['value' => $options['min']]);
            }
        }
        // max
        if (isset($options['max'])) {
            if ($value > $options['max']) {
                $result['error'][] = loc('NF.Validator.InvalidMaxValue', '', ['value' => $options['max']]);
            }
        }
        if (empty($result['error'])) {
            $result['success'] = true;
        }
        $result['data'] = $value;
        return $result;
    }
}
