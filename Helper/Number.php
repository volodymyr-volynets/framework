<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper;

class Number
{
    /**
     * Numbers names less 20
     *
     * @var array
     */
    public static $numbers_under_20 = [
        'zero', 'one', 'two', 'three', 'four',
        'five', 'six', 'seven', 'eight', 'nine',
        'ten', 'eleven', 'twelve', 'thirteen', 'fourteen',
        'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    ];

    /**
     * Numbers names less 20 (counted)
     *
     * @var array
     */
    public static $numbers_under_20_counted = [
        'zero', 'first', 'second', 'third', 'fourth',
        'fifth', 'sixth', 'seventh', 'eighth', 'ninth',
        'tenth', 'eleventh', 'twelveth', 'thirteenth', 'fourteenth',
        'fifteenth', 'sixteenth', 'seventeenth', 'eighteenth', 'nineteenth'
    ];

    /**
     * Tens digits
     *
     * @var array
     */
    public static $tens_digits = [
        'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'
    ];

    /**
     * Tens digits (counted)
     *
     * @var array
     */
    public static $tens_digits_counted = [
        'twentyth', 'thirtyth', 'fortyth', 'fiftyth', 'sixtyth', 'seventyth', 'eightyth', 'ninetyth'
    ];

    /**
     * Greater digits
     *
     * @var array
     */
    public static $order_of_magnitude = [
        'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion'
    ];

    /**
     * Greater digits (counted)
     *
     * @var array
     */
    public static $order_of_magnitude_counted = [
        'thousandth', 'millionth', 'billionth', 'trillionth', 'quadrillionth', 'quintillionth'
    ];

    /**
     * To words
     *
     * @param int $number
     * @param bool $recursive
     * @param bool $counted
     * @return string
     */
    public static function toWords(int $number, bool $recursive = false, bool $counted = false): string
    {
        // negative numbers
        if ($number < 0) {
            return 'negative ' . self::toWords(abs($number), true, $counted);
        }
        // zero
        if ($number == 0) {
            if ($recursive) {
                return '';
            } else {
                if ($counted) {
                    return self::$numbers_under_20_counted[0];
                } else {
                    return self::$numbers_under_20[0];
                }
            }
        }
        // less than 20
        if ($number < 20) {
            if ($counted) {
                return self::$numbers_under_20_counted[$number];
            } else {
                return self::$numbers_under_20[$number];
            }
        }
        // less than 100
        if ($number < 100) {
            $high = intval(floor($number / 10) - 2);
            $remaining = $number % 10;
            if ($remaining == 0) {
                if ($counted) {
                    return self::$tens_digits_counted[$high];
                } else {
                    return self::$tens_digits[$high];
                }
            }
            return self::$tens_digits[$high] . '-' . self::toWords($remaining, true, $counted);
        }
        // less then 1000
        if ($number < 1000) {
            $high = intval(floor($number / 100));
            $remaining = $number % 100;
            if ($remaining == 0) {
                $result = self::toWords($high, true) . '-hundred';
                if ($counted) {
                    $result .= $high % 2 ? 'ths' : 'th';
                }
                return $result;
            } else {
                return self::toWords($high, true) . '-hundred ' . self::toWords($remaining, true, $counted);
            }
        }
        // more then 1000
        $quotient = $number;
        $divide = 0;
        while ($quotient >= 1000) {
            $quotient /= 1000;
            ++$divide;
        }
        $high = intval(floor($quotient));
        $remaining = $number - ($high * pow(1000, $divide));
        if ($remaining == 0) {
            if ($counted) {
                $result = self::toWords($high, true) . '-' . self::$order_of_magnitude_counted[$divide - 1];
                $result .= $high % 2 ? 'ths' : 'th';
            } else {
                $result = self::toWords($high, true) . '-' . self::$order_of_magnitude[$divide - 1];
            }
            return $result;
        } else {
            return self::toWords($high, true) . '-' . self::$order_of_magnitude[$divide - 1] . ' ' . self::toWords($remaining, true, $counted);
        }
    }
}
