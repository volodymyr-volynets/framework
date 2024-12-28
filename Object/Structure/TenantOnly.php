<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Structure;

use Numbers\Backend\Db\Common\Schemas;
use Object\ACL\Resources;
use Object\Content\Messages;
use Object\Error\Base;
use Object\Error\UserException;
use Object\Validator\Domain\Part;

class TenantOnly
{
    /**
     * Get settings
     *	- database
     *	- cache
     *
     * @return array
     */
    public function settings()
    {
        $structure = \Application::get('application.structure') ?? [];
        $result = [];
        $host_parts = \Request::hostParts(\Request::host());
        $validator = new Part();
        // multi tenant environment
        if (!empty($structure['tenant_multiple'])) {
            // if we are accessing by IP
            if (filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP)) {
                goto accessed_by_ip;
            }
            if (strtolower($host_parts[$structure['tenant_domain_level']] ?? '') === 'www') {
                goto accessed_by_www;
            }
            if (!empty($host_parts[$structure['tenant_domain_level']])) {
                $validated = $validator->validate($host_parts[$structure['tenant_domain_level']]);
                if (empty($validated['success'])) {
                    if (!empty($structure['tenant_not_found_url'])) {
                        \Request::redirect($structure['tenant_not_found_url']);
                    } else {
                        Base::$flag_database_tenant_not_found = true;
                        throw new \Exception('Invalid URL!', -1);
                    }
                }
                // clenup tenant name
                $result['tenant']['code'] = strtoupper($validated['data']);
            } else {
                accessed_by_www:
                accessed_by_ip:
                                $result['tenant']['code'] = $structure['tenant_no_domain'] ?? 'DEFAULT';
            }
        } elseif (!empty($structure['tenant_default_id'])) { // we simply use id if its a single tenant system
            $result['tenant']['id'] = (int) $structure['tenant_default_id'];
        }
        // see if we are in multi db environment
        if (!empty($structure['db_multiple'])) {
            $schema_result = Schemas::getSettings([
                'db_link' => 'default',
                'search_tenant_code' => $result['tenant']['code']
            ]);
            if (empty($schema_result['tenant_list'][$result['tenant']['code']])) {
                if (!empty($structure['db_not_found_url'])) {
                    \Request::redirect($structure['db_not_found_url']);
                } else {
                    Base::$flag_database_tenant_not_found = true;
                    throw new \Exception('Invalid URL!', -1);
                }
            } else {
                // default settings are for default db and cache links
                $result['cache']['default']['cache_key'] = $schema_result['tenant_list'][$result['tenant']['code']]['tm_tenant_tm_database_code'] . '-' . strtolower($result['tenant']['code']);
                \Application::set('cache.default.cache_key', $result['cache']['default']['cache_key']);
                $result['db']['default']['dbname'] = $schema_result['tenant_list'][$result['tenant']['code']]['tm_tenant_tm_database_code'];
                $result['tenant']['id'] = $schema_result['tenant_list'][$result['tenant']['code']]['tm_tenant_id'];
            }
        }
        // put settings back to registry
        finish:
                \Application::set('application.structure.settings', $result);
        return $result;
    }

    /**
     * Get tenant settings
     *
     * @return array
     */
    public function tenant()
    {
        $structure = \Application::get('application.structure') ?? [];
        // see if we have a tenant in a __token
        $input = \Request::input(null, true, true);
        if (!empty($input['__db_token'])) {
            $crypt = new \Crypt();
            $token_result = $crypt->tokenValidate($input['__db_token'], ['skip_time_validation' => true]);
            if ($token_result === false) {
                throw new UserException(Messages::TOKEN_EXPIRED);
            }
            $host_parts = \Request::hostParts(\Request::host());
            if ($host_parts[$structure['tenant_domain_level']] === 'system') {
                return;
            }
        }
        $tenant_datasource_settings = Resources::getStatic('application_structure', 'tenant');
        if (!empty($tenant_datasource_settings['tenant_datasource'])) {
            // prepare to query tenant
            $tenant_input = \Application::get('application.structure.settings.tenant');
            // see if we have tenant override from __token
            if (!empty($crypt_result['id'])) {
                $tenant_input = ['id' => (int) $crypt_result['id']];
            }
            if (!empty($tenant_input)) {
                if (!empty($tenant_datasource_settings['column_prefix'])) {
                    array_key_prefix_and_suffix($tenant_input, $tenant_datasource_settings['column_prefix']);
                }
                // find tenant
                $class = $tenant_datasource_settings['tenant_datasource'];
                $datasource = new $class();
                $tenant_result = $datasource->get(['where' => $tenant_input, 'single_row' => true]);
                if (empty($tenant_result)) {
                    $structure = \Application::get('application.structure') ?? [];
                    if (!empty($structure['tenant_not_found_url'])) {
                        \Request::redirect($structure['tenant_not_found_url']);
                    } else {
                        throw new \Exception('Invalid URL!', -1);
                    }
                } else {
                    if (!empty($tenant_datasource_settings['column_prefix'])) {
                        array_key_prefix_and_suffix($tenant_result, $tenant_datasource_settings['column_prefix'], null, true);
                    }
                    \Application::set('application.structure.settings.tenant', $tenant_result);
                }
            }
        }
    }
}
