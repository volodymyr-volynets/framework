<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Numbers\FakeNames\FakeNames\FakerFactory;

class Random2
{
    public const LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    public const UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const NUMBERS = '0123456789';
    public const ALL = self::LOWERCASE . self::UPPERCASE . self::NUMBERS;

    /**
     * Get integer
     *
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function getInteger(int $min = 1000, int $max = 9999): int
    {
        return random_int($min, $max);
    }

    /**
     * Get integers
     *
     * @param int $count
     * @param int $min
     * @param int $max
     * @return array
     */
    public static function getIntegers(int $count = 1, int $min = 1000, int $max = 9999): array
    {
        if ($count <= 0) {
            $count = 1;
        }
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = self::getInteger($min, $max);
        }
        return $result;
    }

    /**
     * Get float
     *
     * @param float $min
     * @param float $max
     * @return int
     */
    public static function getFloat(float $min = 1000.00, float $max = 9999.99): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /**
     * Get floats
     *
     * @param int $count
     * @param float $min
     * @param float $max
     * @return array<float>
     */
    public static function getFloats(int $count = 1, float $min = 1000.00, float $max = 9999.99): array
    {
        if ($count <= 0) {
            $count = 1;
        }
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = self::getFloat($min, $max);
        }
        return $result;
    }

    /**
     * Get byte
     *
     * @param int $count
     * @return string
     */
    public static function getByte(string $str = self::LOWERCASE . self::NUMBERS): string
    {
        return self::getBytes(1, $str)[0];
    }

    /**
     * Get bytes
     *
     * @param int $count
     * @return array
     */
    public static function getBytes(int $count = 1, string $str = self::LOWERCASE . self::NUMBERS): array
    {
        if ($count <= 0) {
            $count = 1;
        }
        $array = str_split($str, 1);
        $keys = array_rand($array, $count);
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $result = [];
        foreach ($keys as $v) {
            $result[] = $array[$v];
        }
        return $result;
    }

    /**
     * Get string
     *
     * @param int $count
     * @param string $str
     * @return string
     */
    public static function getString(int $count = 1, string $str = self::LOWERCASE . self::NUMBERS): string
    {
        return implode('', self::getBytes($count, $str));
    }

    /**
     * Get faker factory model
     *
     * @throws Exception
     * @return FakerFactory
     */
    public static function getFakerFactoryModel(): object
    {
        if (!Can::submoduleExists('Numbers.FakeNames.FakeNames.FakerFactoryS')) {
            throw new Exception('Random2: submodule is not installed');
        }
        return Factory::model('\Numbers\FakeNames\FakeNames\FakerFactory', true);
    }
}
