<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\Error\Base;

class SMS
{
    /**
     * Send an SMS
     *
     * Usage example:
     *
     * 	$result = SMS::send([
     * 		'to' => '+NNNNNNNNNNN',
     *		'from' => '+NNNNNNNNNNN',
     * 		'message' => 'test message',
     * 	]);
     *
     * @param array $options
     * 		to - to phone number
     * 		from - from phone number
     * 		message - a message
     * 		settings - optional settings to override ini files
     * @return array
     */
    public static function send(array $options): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => []
        ];
        // mail delivery first
        $class = Application::get('flag.global.sms.delivery.submodule', ['class' => true]);
        if (empty($class)) {
            throw new Exception('You need to specify SMS delivery submodule');
        }
        // check if backend has been enabled
        if (!Application::get($class, ['submodule_exists' => true])) {
            throw new Exception('You must enable ' . $class . ' first!');
        }
        // assemble settings
        $options['settings'] = array_merge_hard(Application::get('flag.global.sms.delivery'), $options['settings'] ?? []);
        // create object
        $object = new $class();
        $result = $object->send($options);
        Log::add([
            'type' => 'SMS',
            'only_channel' => 'default',
            'message' => 'SMS sent!',
            'other' => '[' . 'Direct SMS' . ']' . substr($options['message'], 0, 50) . '...',
            'affected_rows' => $result['error'] ? 0 : 1,
            'error_rows' => $result['error'] ? 1 : 0,
            'trace' => $result['error'] ? Base::debugBacktraceString(null, ['skip_params' => true]) : null,
            'affected_users' => ['phone' => $options['to'], 'user_id' => $options['user_id'] ?? null],
        ]);
        return $result;
    }

    /**
     * Send simple
     *
     * @param string $to
     * @param string $message
     * @return array
     */
    public static function sendSimple(string $to, string $message): array
    {
        return self::send(['to' => $to, 'message' => $message]);
    }
}
