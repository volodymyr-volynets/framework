<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class WebSockets
{
    /**
     * Web socket object
     *
     * @var object
     */
    public $object;

    /**
     * Options
     *
     * @var array
     */
    public $options = [];

    /**
     * Constructing web socket object
     *
     * @param string $web_socket_link
     * @param string $class
     * @param array $options
     */
    public function __construct($web_socket_link = 'default', $class = null, $options = [])
    {
        // get object from factory
        $temp = Factory::get(['websockets', $web_socket_link]);
        // if we have class
        if (!empty($class) && !empty($web_socket_link)) {
            // check if backend has been enabled
            if (!Application::get($class, ['submodule_exists' => true])) {
                throw new Exception('You must enable ' . $class . ' first!');
            }
            // replaces in case we have it as submodule
            $class = str_replace('.', '\\', trim($class));
            // need to manually close connection
            if (!empty($temp['object']) && $temp['class'] != $class) {
                $temp['object']->close();
                unset($this->object);
            }
            $this->options = $options;
            $this->object = new $class($web_socket_link, $options);
            // putting every thing into factory
            Factory::set(['websockets', $web_socket_link], [
                'object' => $this->object,
                'class' => $class,
                'options' => $options,
            ]);
        } elseif (!empty($temp['object'])) {
            $this->object = $temp['object'];
        } else {
            throw new Exception('Could not initialize web socket object!');
        }
    }

    /**
     * Connect
     *
     * @param array $options
     * @return array
     */
    public function connect($options)
    {
        return $this->object->connect($options);
    }

    /**
     * Close
     *
     * @return array
     */
    public function close()
    {
        return $this->object->close();
    }

    /**
     * Send
     *
     * @param string $message
     * @param array $data
     * @param bool|null $ack
     * @return array
     */
    public function send(string $message, array $data = [], ?bool $ack = null): array
    {
        return $this->object->send($message, $data, $ack);
    }

    /**
     * Connect, join and send
     *
     * @param array $room_list
     * @param array $message
     * @return array
     */
    public function connectJoinAndSend(array $room_list, array $message = []): array
    {
        $daemon = defined('DAEMON_NAME') ? constant('DAEMON_NAME') : 'Application';
        try {
            $result = $this->connect($this->options);
            if ($result['success']) {
                $result = $this->send('join', ['rooms' => $room_list, 'message' => 'Daemon ' . $daemon]);
                if ($result['success']) {
                    $result = $this->send('update', ['rooms' => $room_list] + $message);
                }
            }
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'error' => [$e->getMessage()],
            ];
        }
        if (!$result['success']) {
            Log::add([
                'type' => 'WebSockets',
                'only_channel' => 'default',
                'message' => 'WebSockets failed!',
                'other' => 'Daemon ' . $daemon . ': ' . implode(', ', $result['error']),
            ]);
        }
        return $result;
    }
}
