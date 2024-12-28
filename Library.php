<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Library
{
    /**
     * Add library to the application
     *
     * @param string $library
     */
    public static function add($library)
    {
        $connected = Application::get('flag.global.library.' . $library . '.connected');
        if (!$connected) {
            Factory::submodule('flag.global.library.' . $library . '.submodule')->add();
            Application::set('flag.global.library.' . $library . '.connected', true);
        }
    }
}
