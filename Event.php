<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\Enum\EventTypes;
use Object\Event\EventBase;

class Event
{
    /**
     * Events
     *
     * @var array
     */
    protected static array $events = [];

    /**
     * Dispatch
     *
     * @param string|EventBase $code
     * @param string $queue
     * @param mixed $data
     * @param array $options
     * @return array
     */
    public static function dispatch(string|EventBase $code, string $queue, mixed $data, array $options = []): array
    {
        // if we pass event object
        if ($code instanceof EventBase) {
            $result = $code->validate($data, $options);
            if (!$result['success']) {
                return $result;
            }
            $code = $code->code;
        }
        $options['type'] = $options['type'] ?? EventTypes::RequestEnd->value;
        $options['queue'] = $options['queue'] ?? $queue;
        // if submodule is not active we simply skip processing and return true
        if (!Can::submoduleExists('Numbers.Backend.System.Events')) {
            Log::warning('Submitted an event but Numbers.Backend.System.Events is not active.');
            return ['success' => true, 'error' => []];
        }
        $model = Factory::model('\Numbers\Backend\System\Events\Base', true);
        return $model->registerAnEvent($code, $data, $options);
    }

    /**
     * Schedule as cron
     *
     * @param string $code
     * @param string $queue
     * @param mixed $data
     * @param string $cron
     * @param array $options
     * @return array
     */
    public static function cron(string $code, string $queue, mixed $data, string $cron, array $options = []): array
    {
        $options['type'] = EventTypes::Cron->value;
        $options['cron'] = $cron;
        $options['queue'] = $queue;
        return self::dispatch($code, $data, $options);
    }

    /**
     * Schedule for later (at datetime)
     *
     * @param string $code
     * @param string $queue
     * @param mixed $data
     * @param Datetime2 $datetime
     * @param array $options
     * @return array
     */
    public static function later(string $code, string $queue, mixed $data, Datetime2 $datetime, array $options = []): array
    {
        $options['type'] = EventTypes::AtDatetime->value;
        $options['datetime'] = $datetime;
        $options['queue'] = $queue;
        return self::dispatch($code, $data, $options);
    }

    /**
     * Realtime (thought daemon)
     *
     * @param string $code
     * @param string $queue
     * @param mixed $data
     * @param array $options
     * @return array
     */
    public static function realtime(string $code, string $queue, mixed $data, array $options = []): array
    {
        $options['type'] = EventTypes::Realtime->value;
        $options['queue'] = $queue;
        return self::dispatch($code, $data, $options);
    }

    /**
     * End of request
     *
     * @param string $code
     * @param string $queue
     * @param mixed $data
     * @param array $options
     * @return array
     */
    public static function requestEnd(string $code, string $queue, mixed $data, array $options = []): array
    {
        $options['type'] = EventTypes::RequestEnd->value;
        $options['queue'] = $queue;
        return self::dispatch($code, $data, $options);
    }

    /**
     * Set event
     *
     * @param array $options
     * @return void
     */
    public static function setEvent(array $options = []): void
    {
        // preset types as array
        if (!isset(self::$events[$options['type']])) {
            self::$events[$options['type']] = [];
        }
        // for errors we append to execute later
        if ($options['type'] == 'SM::ERRORS') {
            self::$events[$options['type']][] = $options;
        } else {
            self::$events[$options['type']][$options['request_id']] = $options;
        }
    }

    /**
     * Process events
     *
     * @param string $type
     * @return array
     */
    public static function processEvents(string $type): array
    {
        // if submodule is not active we simply skip processing and return true
        if (!Can::submoduleExists('Numbers.Backend.System.Events')) {
            Log::warning('Submitted an event but Numbers.Backend.System.Events is not active.');
            return ['success' => true, 'error' => []];
        }
        // we success if there's no events
        if (empty(self::$events[$type])) {
            return ['success' => true, 'error' => []];
        }
        $model = Factory::model('\Numbers\Backend\System\Events\Base', true);
        foreach (self::$events[$type] as $k => $v) {
            $result = $model->processOneEvent($k);
            if (!$result['success']) {
                Log::warning('Event returned error!', [
                    'other' => $k . ' ' . implode(', ', $result['error']),
                ]);
            }
        }
        return ['success' => true, 'error' => []];
    }
}
