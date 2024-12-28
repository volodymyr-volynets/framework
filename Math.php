<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Math
{
    /**
     * Scale
     *
     * @var int
     */
    public static $scale = 2;

    /**
     * Scale
     *
     * @param int $scale
     */
    public static function scale($scale)
    {
        self::$scale = $scale;
        bcscale($scale);
    }

    /**
     * Double the scale
     *
     * @param int scale
     * @return integer
     */
    public static function double(int $scale): int
    {
        return (intval($scale) * 2) + 2;
    }

    /**
     * Compare
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return int, -1, 0 or 1
     */
    public static function compare($arg1, $arg2, $scale = null): int
    {
        return bccomp($arg1 . '', $arg2 . '', $scale ?? self::$scale);
    }

    /**
     * Is equal
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return boolean
     */
    public static function isEqual($arg1, $arg2, $scale = null)
    {
        return (self::compare($arg1, $arg2, $scale ?? 13) == 0);
    }

    /**
     * Is less then zero
     *
     * @param mixed $arg1
     * @param int $scale
     * @return boolean
     */
    public static function isLess($arg1, $scale = null)
    {
        return (self::compare($arg1, '0', $scale ?? 13) < 0);
    }

    /**
     * Is less then amount
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return boolean
     */
    public static function isLessThenAmount($arg1, $arg2, $scale = null)
    {
        return (self::compare($arg1, $arg2, $scale ?? 13) < 0);
    }

    /**
     * Is less then amount and equal
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return boolean
     */
    public static function isLessThenAndEqualAmount($arg1, $arg2, $scale = null)
    {
        return (self::compare($arg1, $arg2, $scale ?? 13) <= 0);
    }

    /**
     * Is not zero
     *
     * @param mixed $arg1
     * @param int $scale
     * @return boolean
     */
    public static function isNotEmpty($arg1, $scale = null)
    {
        return (self::compare($arg1, '0', $scale ?? 13) != 0);
    }

    /**
     * Is zero
     *
     * @param mixed $arg1
     * @param int $scale
     * @return boolean
     */
    public static function isEmpty($arg1, $scale = null)
    {
        return (self::compare($arg1, '0', $scale ?? 13) == 0);
    }

    /**
     * Is between
     *
     * @param mixed $arg1
     * @param mixed $from
     * @param mixed $to
     * @param int $scale
     * @return boolean
     */
    public static function isBetween($arg1, $from, $to, $scale = null)
    {
        return ((self::compare($arg1, $from, $scale ?? 13) >= 0) && (self::compare($arg1, $to, $scale ?? 13) <= 0));
    }

    /**
     * Add
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return string
     */
    public static function add($arg1, $arg2, $scale = null): string
    {
        return self::__operator('bcadd', $arg1, $arg2, $scale ?? self::$scale);
    }

    /**
     * Add with reference
     *
     * @param string $arg1
     * @param string $arg2
     * @param int $scale
     * @return string
     */
    public static function add2(& $arg1, $arg2, $scale = null): string
    {
        $arg1 = self::add($arg1, $arg2, $scale);
        return $arg1;
    }

    /**
     * Subtract
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return string
     */
    public static function subtract($arg1, $arg2, $scale = null): string
    {
        return self::__operator('bcsub', $arg1, $arg2, $scale ?? self::$scale);
    }

    /**
     * Subtract with reference
     *
     * @param string $arg1
     * @param string $arg2
     * @param int $scale
     * @return string
     */
    public static function subtract2(& $arg1, $arg2, $scale = null): string
    {
        $arg1 = self::subtract($arg1, $arg2, $scale);
        return $arg1;
    }

    /**
     * Multiply
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return string
     */
    public static function multiply($arg1, $arg2, $scale = null): string
    {
        return self::__operator('bcmul', $arg1, $arg2, $scale ?? self::$scale);
    }

    /**
     * Divide
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return string
     */
    public static function divide($arg1, $arg2, $scale = null): string
    {
        return self::__operator('bcdiv', $arg1, $arg2, $scale ?? self::$scale);
    }

    /**
     * Wrapper for bcmath functions
     *
     * @param string $function
     * @param mixed $arg1
     * @param mixed $arg2
     * @param int $scale
     * @return string
     */
    private static function __operator($function, $arg1, $arg2, $scale): string
    {
        if (is_array($arg1)) {
            $arg1_temp = $arg1;
            $temp1 = array_shift($arg1_temp);
            foreach ($arg1_temp as $v) {
                $temp1 = call_user_func_array($function, [$temp1, $v . '', $scale]);
            }
        } else {
            $temp1 = $arg1 . '';
        }
        if (is_array($arg2)) {
            $temp2 = array_shift($arg2);
            foreach ($arg2 as $v) {
                $temp2 = call_user_func_array($function, [$temp2, $v . '', $scale]);
            }
        } else {
            $temp2 = $arg2 . '';
        }
        return call_user_func_array($function, [$temp1, $temp2, $scale]);
    }

    /**
     * Round
     *
     * @param string $arg1
     * @param int $scale
     * @return string
     */
    public static function round($arg1, $scale = 0): string
    {
        if (!isset($scale)) {
            $scale = self::$scale;
        }
        $arg1 = $arg1 . '';
        if ($arg1[0] != '-') {
            return bcadd($arg1, '0.' . str_repeat('0', $scale) . '5', $scale);
        } else {
            return bcsub($arg1, '0.' . str_repeat('0', $scale) . '5', $scale);
        }
    }

    /**
     * Floor, round fractions down
     *
     * @param string $arg1
     * @param int $scale
     * @return string
     */
    public static function floor($arg1, $scale = 0): string
    {
        $arg1 = $arg1 . '';
        if ($arg1[0] != '-') {
            return bcadd($arg1, '0', $scale);
        } else {
            $value = '1';
            if ($scale != 0) {
                $value = self::divide('1', 10 ** $scale, $scale);
            }
            return bcsub($arg1, $value, $scale);
        }
    }

    /**
     * Ceil, round fractions up
     *
     * @param string $arg1
     * @param int $scale
     * @return string
     */
    public static function ceil($arg1, $scale = 0): string
    {
        $arg1 = $arg1 . '';
        if ($arg1[0] != '-') {
            $value = '1';
            if ($scale != 0) {
                $value = self::divide('1', 10 ** $scale, $scale);
            }
            return bcadd($arg1, $value, $scale);
        } else {
            return bcsub($arg1, '0', $scale);
        }
    }

    /**
     * Absolute
     *
     * @param string $arg1
     * @return string
     */
    public static function abs($arg1): string
    {
        return ltrim($arg1, '-');
    }

    /**
     * Opposite
     *
     * @param mixed $arg1
     * @param int $scale
     * @return string
     */
    public static function opposite($arg1, $scale = null): string
    {
        return self::multiply($arg1, '-1', $scale ?? self::$scale);
    }

    /**
     * Double reversal
     *
     * @param string $arg1
     * @param boolean $reverse1
     * @param boolean $reverse2
     * @param int $scale
     * @return string
     */
    public static function doubleReverse($arg1, $reverse1, $reverse2, $scale = null): string
    {
        if ($reverse1) {
            $arg1 = self::opposite($arg1, $scale);
        }
        if ($reverse2) {
            $arg1 = self::opposite($arg1, $scale);
        }
        if ($scale !== null) {
            $arg1 = self::round($arg1, $scale);
        }
        return $arg1;
    }

    /**
     * Zero
     *
     * @param int $scale
     * @return string
     */
    public static function zero($scale = null): string
    {
        return self::add('0', '0.0000000000000', $scale ?? self::$scale);
    }

    /**
     * Sum
     *
     * @param array $array
     * @param int $scale
     * @return string
     */
    public static function sum(array $array, $scale = null): string
    {
        $result = '0';
        foreach ($array as $v) {
            $result = Math::add($result, $v . '', $scale);
        }
        return $result;
    }

    /**
     * Truncate
     *
     * @param mixed $arg1
     * @param int $scale
     * @return string
     */
    public static function truncate($arg1, $scale = null): string
    {
        $scale = $scale ?? self::$scale;
        if (($position = strpos($arg1 . '', '.')) !== false) {
            return substr($arg1 . '', 0, $position + 1 + $scale);
        } else {
            return $arg1;
        }
    }

    /**
     * Has fraction
     *
     * @param string $arg1
     * @param int $scale
     * @return bool
     */
    public static function hasFraction($arg1, $scale = null): bool
    {
        $scale = $scale ?? self::$scale;
        $not_fraction = self::truncate($arg1, 0);
        return !self::isEqual($not_fraction, $arg1, $scale);
    }

    /**
     * Formula
     *
     * @param array $arg1
     * @param int $scale
     * @return string
     */
    public static function formula(array $arg1, $scale = null): string
    {
        // process all ()
        foreach ($arg1 as $k => $v) {
            if (is_array($v)) {
                $arg1[$k] = self::formula($v, $scale);
            }
        }
        // process muls and divs
        foreach ($arg1 as $k => $v) {
            if ($v == '*') {
                $arg1[$k + 1] = self::multiply($arg1[$k - 1], $arg1[$k + 1], $scale);
                unset($arg1[$k - 1], $arg1[$k]);
            } elseif ($v == '/') {
                $arg1[$k + 1] = self::divide($arg1[$k - 1], $arg1[$k + 1], $scale);
                unset($arg1[$k - 1], $arg1[$k]);
            }
        }
        $arg1 = array_values($arg1);
        // process adds and subs
        foreach ($arg1 as $k => $v) {
            if ($v == '+') {
                $arg1[$k + 1] = self::add($arg1[$k - 1], $arg1[$k + 1], $scale);
                unset($arg1[$k - 1], $arg1[$k]);
            } elseif ($v == '-') {
                $arg1[$k + 1] = self::subtract($arg1[$k - 1], $arg1[$k + 1], $scale);
                unset($arg1[$k - 1], $arg1[$k]);
            }
        }
        return array_shift($arg1);
    }

    /**
     * Random hash
     *
     * @param int $length
     * @return string
     */
    public static function randomHash(int $length): string
    {
        $str = bin2hex(random_bytes($length));
        return substr($str, 0, $length);
    }

    /**
     * Random number
     *
     * @param int $length
     * @return string
     */
    public static function randomNumber(int $length): string
    {
        $min = (int) str_pad('1', $length - 1, '0', STR_PAD_RIGHT);
        $max = (int) str_pad('9', $length - 1, '9', STR_PAD_RIGHT);
        return rand($min, $max);
    }
}
