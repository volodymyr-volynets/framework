<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Mask;

class Name
{
    /**
     * Mask
     *
     * @param string $value
     * @param array $options
     *		mask - symbol
     * @return string
     */
    public function mask(string $value, array $options = [])
    {
        $options['mask'] = $options['mask'] ?? '*';
        $len = (strlen($value) - 6);
        if ($len < 0) {
            $len = 0;
        }
        return substr($value, 0, 3)
            . str_repeat($options['mask'], $len)
            . substr($value, -3);
    }
}
