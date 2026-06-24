<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Constant\DatetimeConstants;
use Helper\Date;

class Sleep extends DatetimeConstants
{
    /**
     * For
     *
     * @param int $duration
     * @param string $type
     * @param callable|null $then
     * @return mixed
     */
    public static function forStatic(int $duration, string $type = self::SECONDS, callable|null $then = null): mixed
    {
        switch ($type) {
            case self::MICROSECONDS:
                usleep($duration);
                break;
            case self::MILLISECONDS:
                usleep($duration * 1000);
                break;
            case self::SECONDS:
                sleep($duration);
                break;
            case self::MINUTES:
                sleep($duration * 60);
                break;
            case self::HOURS:
                sleep($duration * 60 * 60);
                break;
            case self::DAYS:
                sleep($duration * 60 * 60 * 24);
                break;
            default:
                throw new Exception('Sleep: unknow type!');
        }
        if ($then) {
            return $then();
        }
        return null;
    }

    /**
     * Until (static)
     *
     * @param string $datetime
     * @param callable|null $then
     * @return mixed
     */
    public static function untilStatic(string $datetime, callable|null $then = null): mixed
    {
        $seconds = Date::diff(Format::now('datetime'), $datetime, 'seconds');
        if ($seconds > 0) {
            self::forStatic($seconds, type: self::SECONDS);
        }
        if ($then) {
            return $then();
        }
        return null;
    }

    /**
     * Timebox (static)
     *
     * @param callable $then
     * @param int $duration
     * @param string $type
     * @return mixed
     */
    public static function timeboxStatic(callable $then, int $duration, string $type = self::SECONDS): mixed
    {
        $start = microtime(true);
        $result = $then();
        $mduration = round(self::convertToMicroSeconds($duration, $type) - (microtime(true) - $start), 0);
        if ($mduration > 0) {
            self::forStatic($mduration, self::MICRO_SECONDS);
        }
        return $result;
    }
}
