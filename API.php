<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Constant\HTTPConstants;
use Object\Reflection;
use Helper\HTTPRequest;

class API
{
    /**
     * Run (local)
     *
     * @param string $class
     * @param string $method
     * @param array|null $input
     * @param array $options
     * 		scope or __scope - scope from ini files
     */
    public static function runLocal(string $class, string $method, ?array $input, array $options = []): array
    {
        $result = RESULT_BLANK;
        $object = new $class([
            'skip_constructor_loading' => false,
        ]);
        // process scope
        $scope = $options['scope'] ?? $options['__scope'] ?? null;
        if (!empty($scope)) {
            $scope = explode(',', $scope);
            foreach ($scope as $v) {
                $temp = Application::get('scope.' . $v);
                if (!empty($temp)) {
                    $input = array_merge_hard($temp, $input);
                }
            }
        }
        // if we have columns we validate by deefault
        $columns = $object->{$method . '_columns'} ?? null;
        if (!empty($columns)) {
            $validator = Validator::validateInputStatic($input, $columns);
            if ($validator->hasErrors()) {
                $result = $validator->errors('result');
                return $result;
            }
            $object->columns = $columns;
            $object->values = $validator->values();
        }
        // prepare dependency injection
        $dependency = Reflection::dependencyInjectionParameters($object, $method, $object->values);
        if (!$dependency['success']) {
            return $dependency;
        }
        // execute method
        try {
            $result = call_user_func_array([$object, $method], $dependency['data']);
        } catch (Exception $e) {
            $result['error'][] = $e->getMessage();
            $result['http_status_code'] = HTTPConstants::Status500InternalServerError;
        }
        return $result;
    }

    /**
     * Run (remote)
     *
     * @param string $class
     * @param string $method
     * @param mixed $input
     * @param array $options
     * @return array
     */
    public static function remoteRun(string $class, string $method, ?array $input, array $options = []): array
    {
        $endpoint = Route::getEndpoint($class, $method, ['include_host' => true]);
        $crypt = new Crypt();
        $bearer_token = $crypt->bearerAuthorizationTokenCreate('EVT', User::id(), Tenant::id(), Request::ip(), session_id());
        $result = HTTPRequest::createStatic()
            ->url($endpoint)
            ->acceptable(HTTPRequest::Status200OK)
            ->retry($options['retry'] ?? 1, 3)
            ->params($input)
            ->header('Authorization', 'Bearer ' . $bearer_token)
            ->post()
            ->jsonDecode(true)
            ->result();
        // if call is successfull we need to return data key as is
        if ($result['success']) {
            $result = $result['data'];
        }
        return $result;
    }
}
