<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper;

class Ob
{
    /**
     * Start output buffering
     */
    public static function start(bool $clean = false)
    {
        if ($clean) {
            self::cleanAll();
        }
        ob_start();
    }

    /**
     * Clean output buffer and return its content
     *
     * @return string
     */
    public static function clean()
    {
        if (ob_get_level() != 0) {
            return ob_get_clean();
        }
    }

    /**
     * Clean all output buffers and return their content
     *
     * @return string
     */
    public static function cleanAll()
    {
        $result = '';
        for ($i = 0; $i < ob_get_level(); $i++) {
            $result .= ob_get_clean();
        }
        return $result;
    }

    /**
     * Require file
     *
     * @param string $filename
     * @return string
     */
    public static function require(string $filename): string
    {
        ob_start();
        require($filename);
        return ob_get_clean();
    }
}
