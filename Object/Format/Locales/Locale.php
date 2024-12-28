<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Format\Locales;

class Locale
{
    /**
     * Get locale settings
     *
     * @param array $options
     */
    public static function localeconv($options)
    {
        return $options;
    }

    /**
     * Convert numbers from native to ASCII
     *
     * @param string $amount
     * @param array $options
     */
    public static function readFloatval($amount, $options = [])
    {
        return $amount;
    }

    /**
     * Convert ASCII to native numbers
     *
     * @param string $amount
     * @param array $options
     */
    public static function amount($amount, $options = [])
    {
        return $amount;
    }

    /**
     * JavaScript conversion functions
     */
    public static function js()
    {

    }
}
