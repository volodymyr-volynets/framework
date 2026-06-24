<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Controller;

use Object\Controller\Abstract2\ExternalAbstract2;

class ExternalAPI
{
    /**
     * Run (remote)
     *
     * @param string $class
     * @param string $method
     * @param mixed $input
     * @param array $options
     * @return array
     */
    public static function remoteRun(string $endpoint, string $method, ?array $input, array $options = []): array
    {
        $endpoint = rtrim(\Application::get('ps.python_flask_3_1_1.host'), '/') . '/' . ltrim($endpoint, '/');
        $model = new ExternalAbstract2();
        $input['api_key'] = \Application::get('ps.python_flask_3_1_1.api_key');
        $options['input_as_raw'] = true;
        $model->preset($endpoint, $method, $input, $options);
        return $model->remoteExecute();
    }
}
