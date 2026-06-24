<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper\Constant;

class DatetimeConstants
{
    // time
    public const MICROSECONDS = 'microseconds';
    public const MILLISECONDS = 'milliseconds';
    public const SECONDS = 'seconds';
    public const MINUTES = 'minutes';
    public const HOURS = 'hours';
    public const DAYS = 'days';
    public const WEEKS = 'weeks';
    public const MONTHS = 'months';
    public const YEARS = 'years';

    // other
    public const AGO = 'ago';
    public const BEFORE = 'before';
    public const NOW = 'now';
    public const JUST_NOW = 'just now';
    public const NONE = 'none';

    public const TYPES = [
        self::MICROSECONDS => ['name' => 'microsecond(s)', 'loc' => 'NF.Datetime.Microseconds', 'short' => 'µs', 'min' => 1 / 1_000_000],
        self::MILLISECONDS => ['name' => 'millisecond(s)', 'loc' => 'NF.Datetime.Milliseconds', 'short' => 'ms', 'min' => 1 / 1_000],
        self::SECONDS => ['name' => 'second(s)', 'loc' => 'NF.Datetime.Seconds', 'short' => 's', 'min' => 1],
        self::MINUTES => ['name' => 'minute(s)', 'loc' => 'NF.Datetime.Minutes', 'short' => 'm', 'min' => 60],
        self::HOURS => ['name' => 'hour(s)', 'loc' => 'NF.Datetime.Hours', 'short' => 'h', 'min' => 3_600],
        self::DAYS => ['name' => 'day(s)', 'loc' => 'NF.Datetime.Days', 'short' => 'd', 'min' => 86_400],
        self::WEEKS => ['name' => 'week(s)', 'loc' => 'NF.Datetime.Weeks', 'short' => 'W', 'min' => 604_800],
        self::MONTHS => ['name' => 'month(s)', 'loc' => 'NF.Datetime.Months', 'short' => 'M.', 'min' => 2_592_000],
        self::YEARS => ['name' => 'year(s)', 'loc' => 'NF.Datetime.Years', 'short' => 'Y', 'min' => 31_536_000],
    ];

    public $loc = [
        'NF.Datetime.Microseconds' => 'microsecond(s)',
        'NF.Datetime.Microseconds[0-1]' => 'microsecond',
        'NF.Datetime.Microseconds[2-N]' => 'microseconds',
        'NF.Datetime.Milliseconds' => 'millisecond(s)',
        'NF.Datetime.Milliseconds[0-1]' => 'millisecond',
        'NF.Datetime.Milliseconds[2-N]' => 'milliseconds',
        'NF.Datetime.Seconds' => 'second(s)',
        'NF.Datetime.Seconds[0-1]' => 'second',
        'NF.Datetime.Seconds[2-N]' => 'seconds',
        'NF.Datetime.Minutes' => 'minute(s)',
        'NF.Datetime.Minutes[0-1]' => 'minute',
        'NF.Datetime.Minutes[2-N]' => 'minutes',
        'NF.Datetime.Hours' => 'hour(s)',
        'NF.Datetime.Hours[0-1]' => 'hour',
        'NF.Datetime.Hours[2-N]' => 'hours',
        'NF.Datetime.Days' => 'day(s)',
        'NF.Datetime.Days[0-1]' => 'day',
        'NF.Datetime.Days[2-N]' => 'days',
        'NF.Datetime.Weeks' => 'week(s)',
        'NF.Datetime.Weeks[0-1]' => 'week',
        'NF.Datetime.Weeks[2-N]' => 'weeks',
        'NF.Datetime.Months' => 'month(s)',
        'NF.Datetime.Months[0-1]' => 'month',
        'NF.Datetime.Months[2-N]' => 'months',
        'NF.Datetime.Years' => 'year(s)',
        'NF.Datetime.Years[0-1]' => 'year',
        'NF.Datetime.Years[2-N]' => 'years',
        // other
        'NF.Datetime.Ago' => 'ago',
        'NF.Datetime.Before' => 'before',
        'NF.Datetime.Now' => 'now',
        'NF.Datetime.JustNow' => 'just now',
        'NF.Datetime.None' => 'none',
        'NF.Datetime.About' => 'about',
        'NF.Datetime.AboutTime' => 'about {time}',
        // micro letters
        'NF.Datetime.Micro_Microseconds' => 'µs',
        'NF.Datetime.Micro_Milliseconds' => 'ms',
        'NF.Datetime.Micro_Seconds' => 's',
        'NF.Datetime.Micro_Minutes' => 'm',
        'NF.Datetime.Micro_Hours' => 'h',
        'NF.Datetime.Micro_Days' => 'd',
        'NF.Datetime.Micro_Weeks' => 'W',
        'NF.Datetime.Micro_Months' => 'M',
        'NF.Datetime.Micro_Years' => 'Y',
    ];

    /**
     * Convert to micro seconds
     *
     * @param int $duration
     * @param string $type
     * @throws \Exception
     * @return int
     */
    public static function convertToMicroSeconds(int $duration, string $type = self::SECONDS): int
    {
        switch ($type) {
            case self::MICROSECONDS:
                return $duration;
            case self::MILLISECONDS:
                return $duration * 1000;
            case self::SECONDS:
                return $duration * 1000 * 1000;
            case self::MINUTES:
                return $duration * 1000 * 1000 * 60;
            case self::HOURS:
                return $duration * 1000 * 1000 * 60 * 60;
            case self::DAYS:
                return $duration * 1000 * 1000 * 60 * 60 * 24;
            default:
                throw new \Exception('Datetime Constants: unknow type!');
        }
    }
}
