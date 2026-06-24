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

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Datetime2 extends DatetimeConstants
{
    /**
     * Formats
     */
    public const FORMATS = [
        'date' => 'Y-m-d',
        'time' => 'H:i:s',
        'datetime' => 'Y-m-d H:i:s',
        'timestamp' => 'Y-m-d H:i:s.u',
    ];

    /**
     * @var DateTimeImmutable|null
     */
    protected ?DateTimeImmutable $datetime = null;

    /**
     * @var DateTimeZone|null
     */
    protected ?DateTimeZone $timezone = null;

    /**
     * @var string
     */
    protected string $format = 'timestamp';

    /**
     * Constructor
     *
     * @param DateTimeImmutable|DateTime|string|int|null $datetime
     * @param ?string $format
     * @param DateTimeZone|string|null $timezone
     */
    public function __construct(DateTimeImmutable|DateTime|string|int|float|null $datetime = null, ?string $format = 'timestamp', DateTimeZone|string|null $timezone = 'user')
    {
        // format
        $this->format = self::FORMATS[$format] ?? $format ?? self::FORMATS['timestamp'];
        // timezone
        if ($timezone instanceof DateTimeZone) {
            $this->timezone = $timezone;
        } elseif (is_string($timezone)) {
            // if we need to fetch timezone from global settings
            if ($timezone == 'user') {
                $this->timezone = new DateTimeZone(Format::$options['timezone_code']);
            } elseif ($timezone == 'system') {
                $this->timezone = new DateTimeZone(Format::$options['server_timezone_code']);
            } else {
                $this->timezone = new DateTimeZone($timezone);
            }
        }
        // datetime
        if ($datetime instanceof DateTimeImmutable) {
            $this->datetime = $datetime;
        } elseif ($datetime instanceof DateTime) {
            $this->datetime = DateTimeImmutable::createFromMutable($datetime);
        } elseif (is_string($datetime)) {
            $this->datetime = DateTimeImmutable::createFromFormat($this->format, $datetime, $this->timezone);
        } elseif (is_int($datetime)) { // timestamp as int
            $this->datetime = (new DateTimeImmutable())->setTimestamp($datetime);
        } elseif (is_float($datetime)) { // timestamp as float
            $this->datetime = (new DateTimeImmutable())->setTimestamp((int) $datetime);
            $microseconds = (int) (($datetime - (int) $datetime) * 1_000_000);
            if ($microseconds != 0) {
                $this->modify("+{$microseconds} microseconds");
            }
        }
    }

    /**
     * Format
     *
     * @param ?string $format
     * @return string
     */
    public function format(?string $format = null): ?string
    {
        if ($this->datetime !== null) {
            if (isset($format) && str_starts_with($format, 'system_')) {
                $format = Format::getDateFormat(str_replace('system_', '', $format));
            }
            return $this->datetime->format(self::FORMATS[$format] ?? $format ?? $this->format);
        }
        return null;
    }

    /**
     * To string
     *
     * @return string|null
     */
    public function toString(): ?string
    {
        if ($this->datetime !== null) {
            return $this->datetime->format($this->format);
        }
        return null;
    }

    /**
     * To timestamp
     *
     * @param bool $include_microseconds
     * @return int
     */
    public function toTimestamp(bool $include_microseconds = false): int|float
    {
        $result = (int) $this->datetime->format('U');
        if ($include_microseconds) {
            $result += ((int) $this->datetime->format('u')) / 1000000;
        }
        return $result;
    }

    /**
     * String2
     *
     * @return String2
     */
    public function string2(): String2
    {
        return new String2($this->toString());
    }

    /**
     * Now
     *
     * @param string $type
     * @param array $options
     * @return Datetime2
     */
    public function now(string $type = 'datetime', array $options = []): Datetime2
    {
        return new self(Format::now($type, $options), $type);
    }

    /**
     * Modify
     *
     * @param string $modifier
     * @return Datetime2
     */
    public function modify(string $modifier): Datetime2
    {
        if ($this->datetime === null) {
            $this->datetime = new DateTimeImmutable('now', $this->timezone);
        }
        $this->datetime = $this->datetime->modify($modifier);
        return $this;
    }

    /**
     * Ago
     *
     * @param array $options
     *      int precision - default 0
     *      bool short
     *      string in - default auto
     *      bool main_locale
     *      bool skip_ago_before
     * @return string|null
     */
    public function ago(array $options = []): string|null
    {
        if ($this->datetime === null) {
            return null;
        }
        // set default options
        $options['precision'] = $options['precision'] ?? 0;
        $options['short'] = $options['short'] ?? false;
        $options['in'] = $options['in'] ?? 'auto';
        $options['now'] = $options['now'] ?? microtime(true);
        $options['main_locale'] ??= false;
        // calculate
        $now = new static($options['now'])->toTimestamp(true);
        $new = $this->toTimestamp(true);
        $diff = $now - $new;
        $abs = abs($diff);
        foreach (array_reverse(self::TYPES, true) as $k => $v) {
            // if we specify current
            if ($options['in'] != 'auto' && $options['in'] != $k) {
                continue;
            }
            if ($abs >= $v['min']) {
                $ago = round($abs / $v['min'], $options['precision']);
                $formatted = Format::number($ago, [
                    'decimals' => $options['precision'],
                ]);
                if ($options['short']) {
                    $formatted .= ' ' . loc('NF.Datetime.Micro_' . ucfirst($k), $k, [
                        'main_locale' => $options['main_locale'],
                    ]);
                } else {
                    $formatted .= ' ' . loc($v['loc'], $v['name'], [
                        '__plural' => $ago,
                        'main_locale' => $options['main_locale'],
                    ]);
                }
                if (empty($options['skip_ago_before'])) {
                    if ($diff < 0) {
                        return $formatted . ' ' . loc('NF.Datetime.Before', 'before', [
                            'main_locale' => $options['main_locale'],
                        ]);
                    } else {
                        return $formatted . ' ' . loc('NF.Datetime.Ago', 'ago', [
                            'main_locale' => $options['main_locale'],
                        ]);
                    }
                } else {
                    return $formatted;
                }
            }
        }
        return null;
    }

    /**
     * Duration
     *
     * @param array $options
     *      bool short
     *      string min_in - default seconds
     *      bool main_locale
     *      bool less_than_min_in
     * @return string|null
     */
    public function duration(array $options = []): string|null
    {
        if ($this->datetime === null) {
            return null;
        }
        // set default options
        $options['short'] = $options['short'] ?? false;
        $options['min_in'] = $options['min_in'] ?? self::SECONDS;
        $options['main_locale'] ??= false;
        // calculate
        $now = microtime(true);
        $new = (int) $this->datetime->format('U') + ((int) $this->datetime->format('u')) / 1000000;
        $diff = $now - $new;
        $abs = abs($diff);
        $result = [];
        foreach (array_reverse(self::TYPES, true) as $k => $v) {
            $whole = (int) ($abs / $v['min']);
            $abs = $abs - ($whole * $v['min']);
            if ($whole > 0) {
                $formatted = $whole;
                if ($options['short']) {
                    $formatted .= loc('NF.Datetime.Micro_' . ucfirst($k), $k, [
                        'main_locale' => $options['main_locale'],
                    ]);
                } else {
                    $formatted .= ' ' . loc($v['loc'], $v['name'], [
                        '__plural' => $whole,
                        'main_locale' => $options['main_locale'],
                    ]);
                }
                $result[] = $formatted;
            }
            // cut off
            if ($options['min_in'] == $k) {
                // if we need to have less then
                if (!empty($options['less_than_min_in']) && count($result) == 0) {
                    $result[] = '<1' . loc('NF.Datetime.Micro_' . ucfirst($k), $k, [
                        'main_locale' => $options['main_locale'],
                    ]);
                }
                break;
            }
        }
        return $result ? implode(' ', $result) : null;
    }

    /**
     * About
     *
     * @param array $options
     *      bool short
     *      string min_in - default seconds
     *      bool main_locale
     * @return string|null
     */
    public static function about(int $seconds, array $options = []): string|null
    {
        // set default options
        $options['short'] = $options['short'] ?? false;
        $options['min_in'] = $options['min_in'] ?? self::MINUTES;
        $options['main_locale'] ??= false;
        // calculate
        $diff = $seconds;
        $abs = abs($diff);
        $result = [];
        foreach (array_reverse(self::TYPES, true) as $k => $v) {
            $whole = (int) ($abs / $v['min']);
            $abs = $abs - ($whole * $v['min']);
            if ($whole > 0) {
                $formatted = $whole;
                if ($options['short']) {
                    $formatted .= ' ' . loc('NF.Datetime.Micro_' . ucfirst($k), $k, [
                        'main_locale' => $options['main_locale'],
                    ]);
                } else {
                    $formatted .= ' ' . loc($v['loc'], $v['name'], [
                        '__plural' => $whole,
                        'main_locale' => $options['main_locale'],
                    ]);
                }
                $result[] = $formatted;
            }
            // cut off
            if ($options['min_in'] == $k) {
                break;
            }
        }
        // format
        if ($options['short']) {
            return '~' . implode(' ', $result);
        } else {
            return loc('NF.Datetime.AboutTime', 'about {time}', [
                'time' => implode(' ', $result),
                'main_locale' => $options['main_locale'],
            ]);
        }
    }
}
