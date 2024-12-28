<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Alive
{
    public static $alive = false;
    public static $buffer = '';
    public static $ob_start = false;

    /**
     * Start
     */
    public static function start()
    {
        // get buffer
        if (ob_get_level()) {
            self::$ob_start = true;
            self::$buffer = @ob_get_clean();
        }
        register_tick_function('alive_tick');
        declare(ticks=20000);
        self::$alive = true;
    }

    /**
     * Stop
     */
    public static function stop()
    {
        unregister_tick_function('alive_tick');
        if (self::$ob_start) {
            ob_start();
            echo self::$buffer;
        }
    }
}

/**
 * Echo and flush spaces
 */
function alive_tick()
{
    // we exit if configured to
    if (Application::get('flag.alive.exit_on_disconnect')) {
        if (connection_aborted()) {
            exit;
        }
    }
    // send space to frontend
    echo ' ';
    flush();
}
