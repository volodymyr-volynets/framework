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

class Email
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
        $temp = explode('@', trim($value));
        $result = (new Name())->mask($temp[0], $options);
        return $result . '@' . $temp[1];
    }
}
