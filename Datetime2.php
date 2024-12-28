<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Datetime2
{
    /**
     * Formats
     */
    public const FORMATS = [
        'date' => 'Y-m-d',
        'time' => 'H:i:s',
        'datetime' => 'Y-m-d H:i:s',
        'timestamp' => 'Y-m-d H:i:s',
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
     * @param DateTimeImmutable|DateTime|string|null $datetime
     * @param ?string $format
     * @param DateTimeZone|string|null $timezone
     */
    public function __construct(DateTimeImmutable|DateTime|string|null $datetime = null, ?string $format = 'timestamp', DateTimeZone|string|null $timezone = 'user')
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
        }
    }

    /**
     * Format
     *
     * @param ?string $format
     * @return string
     */
    public function format(?string $format = null): string
    {
        if ($this->datetime !== null) {
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
}
