<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace System;

use Helper\Cmd;

class Daemons
{
    /**
     * @var bool
     */
    public static $run = true;

    /**
     * Initialize
     */
    public static function initialize(): array
    {
        $result = RESULT_BLANK;
        // we must be in command line
        if (PHP_SAPI !== 'cli') {
            echo "We must be in command line";
            exit;
        }
        // we must be in detached child process
        if (DAEMON_FORK && pcntl_fork() !== 0) {
            echo "We must be in detached child process!";
            exit;
        }

        // ticks for signals
        declare(ticks=1);
        pcntl_signal(SIGTERM, 'System\Daemons::signals');
        pcntl_signal(SIGHUP, 'System\Daemons::signals');
        pcntl_signal(SIGUSR1, 'System\Daemons::signals');

        if (DAEMON_FORK) {
            file_put_contents(DAEMON_PID, getmypid());

            posix_setuid(DAEMON_UID);
            posix_setgid(DAEMON_GID);

            Cmd::initializeConsole(posix_ttyname(STDOUT));

            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);

            //ini_set('display_errors', 0);
        }

        return $result;
    }

    /**
     * Destroy
     */
    public static function destroy()
    {
        // delete PID
        if (DAEMON_PID) {
            unlink(DAEMON_PID);
        }
    }

    /**
     * Signals
     *
     * @param int $signo
     */
    public static function signals($signo)
    {
        switch ($signo) {
            case SIGTERM:
                Cmd::message('Received SIGTERM, dying...', 'red');
                self::$run = false;
                return;
            case SIGHUP:
                Cmd::message('Received SIGHUP, starting...', 'red');
                return;
            case SIGUSR1:
                if (Cmd::$console !== null) {
                    @fclose(Cmd::$console);
                }
                Cmd::$console = null;
                if (preg_match('|pts/([0-9]+)|', `who`, $out) && !empty($out[1])) {
                    Cmd::initializeConsole('/dev/pts/' . $out[1]);
                }
                return;
        }
    }

    /**
     * Loop
     *
     * @param callable $callback
     * @return array
     */
    public static function loop(callable $callback)
    {
        do {
            $load = $callback();
            if (DAEMON_FORK) {
                //$sleep = MAX_SLEEP + $load * (MIN_SLEEP - MAX_SLEEP);
                //cli_set_process_title(DAEMON_NAME . ': ' . round(100 * $load, 1) . '%');
                sleep(MAX_SLEEP);
            }
        } while (self::$run);
    }
}
