<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Memory2
{
    private static $units_1024 = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    private static $units_1000 = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    private static $units_full = ['Bytes', 'Kilobytes', 'Megabytes', 'Gigabytes', 'Terabytes', 'Petabytes', 'Exabytes', 'Zettabytes', 'Yottabytes'];
    private static $units_bits = ['bit', 'kbit', 'Mbit', 'Gbit', 'Tbit', 'Pbit', 'Ebit', 'Zbit', 'Ybit'];

    /**
     * Get current memory usage (static)
     *
     * @param bool $real_usage
     * @param bool $peak_usage
     * @return int
     */
    public static function getCurrentUsageStatic(bool $real_usage = true, bool $peak_usage = false): int
    {
        if ($peak_usage) {
            return memory_get_peak_usage($real_usage);
        }
        return memory_get_usage($real_usage);
    }

    /**
     * Get current memory usage
     *
     * @param bool $real_usage
     * @param bool $peak_usage
     * @return int
     */
    public function getCurrentUsage(bool $real_usage = true, bool $peak_usage = false): int
    {
        return self::getCurrentUsageStatic($real_usage, $peak_usage);
    }

    /**
     * Get MAX memory (static)
     *
     * @param bool $real_usage
     * @param bool $peak_usage
     * @return int
     */
    public static function getMaxStatic(): int
    {
        $result = ini_get('memory_limit');
        if ($result == '-1') {
            return -1;
        }
        return self::bytes($result);
    }

    /**
     * Get MAX memory
     *
     * @param bool $real_usage
     * @param bool $peak_usage
     * @return int
     */
    public function getMax(): int
    {
        return self::getMaxStatic();
    }

    /**
     * Format
     *
     * @param int $memory
     * @param array $options
     *      bool binary - default true
     *      bool full_names - default false
     *      bool bits - default false
     * @return string
     */
    public static function format(int $memory, array $options = []): string
    {
        $options['binary'] ??= true;
        $options['full_names'] ??= false;
        $options['bits'] ??= false;
        if (!empty($options['binary'])) {
            $unit = self::$units_1024;
            $weight = 1024;
        } else {
            $unit = self::$units_1000;
            $weight = 1000;
        }
        if ($options['full_names']) {
            $unit = self::$units_full;
        }
        if ($options['bits']) {
            $unit = self::$units_bits;
            $memory = $memory * 8;
        }
        if ($memory == 0) {
            return '0 ' . $unit[0];
        }
        return round($memory / pow($weight, ($i = floor(log($memory, $weight)))), 2) . ' ' . ($unit[$i] ?? $unit[0]);
    }

    /**
     * Bytes (static)
     *
     * @param string $value
     * @return int
     */
    public static function bytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower($value[strlen($value) - 1]);
        $result = (int) $value;
        switch ($unit) {
            case 'g':
                $result *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $result *= 1024 * 1024;
                break;
            case 'k':
                $result *= 1024;
                break;
        }
        return $result;
    }
}

/*
$memory = \Memory2::getStatic();
echo $memory . "\n";
echo \Memory::format($memory, ['bits' => true]) . "\n";
echo \Memory::format($memory, ['full_names' => true]) . "\n";
*/
