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

class Config
{
    /**
     * Process ini file
     *
     * @param string $ini_file
     * @param string $environment
     * @param array $options
     *		boolean simple_keys
     *		string libraries_folder
     *		string application_folder
     *		string ini_folder
     *      string root_folder
     * @return array
     */
    public static function ini(string $ini_file, $environment = null, array $options = []): array
    {
        $result = [];
        $host = \Request::host();
        $data = parse_ini_file($ini_file, true, INI_SCANNER_TYPED);
        // processing environment
        if (!empty($data['environment'])) {
            foreach ($data['environment'] as $k => $v) {
                if (is_string($v) && strpos($v, 'host://') !== false) {
                    $v = str_replace('host://', $host, $v);
                }
                if (is_string($v) && strpos($v, 'libraries://') !== false) {
                    $v = $options['libraries_folder'] . str_replace('libraries://', '', $v);
                }
                if (is_string($v) && strpos($v, 'application://') !== false) {
                    $v = $options['application_folder'] . str_replace('application://', '', $v);
                }
                if (is_string($v) && strpos($v, 'config://') !== false) {
                    $v = str_replace('config://', $options['ini_folder'], $v);
                }
                if (is_string($v) && strpos($v, 'root://') !== false) {
                    $v = $options['root_folder'] . str_replace('root://', '', $v);
                }
                array_key_set($result, explode('.', $k), $v);
            }
        }
        unset($data['environment']);
        // small chicken and egg problem for environment variable
        if ($environment == null && !empty($result['environment'])) {
            $environment = $result['environment'];
        }
        // module
        if (!empty($data['module'])) {
            foreach ($data['module'] as $k => $v) {
                if (empty($options['simple_keys'])) {
                    array_key_set($result, $k, $v);
                } else {
                    $result[$k] = $v;
                }
            }
        }
        unset($data['module']);
        // processing dependencies first
        if (!empty($data['dependencies'])) {
            foreach ($data['dependencies'] as $k => $v) {
                if (empty($options['simple_keys'])) {
                    array_key_set($result, $k, $v);
                } else {
                    $result[$k] = $v;
                }
            }
        }
        unset($data['dependencies']);
        // proccesing environment specific sectings
        foreach ($data as $section => $values) {
            $sections = explode(',', $section);
            if (empty($values) || (!in_array($environment, $sections) && !in_array('*', $sections))) {
                continue;
            }
            foreach ($values as $k => $v) {
                if (is_string($v) && strpos($v, 'host://') !== false) {
                    $v = str_replace('host://', $host, $v);
                }
                if (is_string($v) && strpos($v, 'application://') !== false) {
                    $v = $options['application_folder'] . str_replace('application://', '', $v);
                }
                if (is_string($v) && strpos($v, 'libraries://') !== false) {
                    $v = $options['libraries_folder'] . str_replace('libraries://', '', $v);
                }
                if (is_string($v) && strpos($v, 'config://') !== false) {
                    $v = str_replace('config://', $options['ini_folder'], $v);
                }
                if (is_string($v) && strpos($v, 'root://') !== false) {
                    $v = $options['root_folder'] . str_replace('root://', '', $v);
                }
                if (empty($options['simple_keys'])) {
                    array_key_set($result, $k, $v);
                } else {
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * Load configuration files
     *
     * @param string $ini_folder
     * @param array $options
     *		string libraries_folder
     *		string application_folder
     *      string root_folder
     * @return array
     */
    public static function load(string $ini_folder, array $options = []): array
    {
        $result = [
            'environment' => 'production'
        ];
        $options['ini_folder'] = $ini_folder;
        // environment ini file first
        $environment_file = $ini_folder . 'environment.ini';
        if (file_exists($environment_file)) {
            $ini_data = self::ini($environment_file, null, $options);
            $result = array_merge2($result, $ini_data);
        }
        // application.ini file second
        $application_file = $ini_folder . 'application.ini';
        if (file_exists($application_file)) {
            $ini_data = self::ini($application_file, $result['environment'], $options);
            $result = array_merge2($result, $ini_data);
        }
        // add dev environment last to override settings from applicaiton.ini
        if ($result['environment'] == 'development') {
            $ini_data = self::ini($environment_file, $result['environment'], $options);
            $result = array_merge_hard($result, $ini_data);
        }
        // load additional ini files, we accept double key format
        if (!empty($result['ini']['include'])) {
            foreach ($result['ini']['include'] as $v) {
                foreach ($v as $v2) {
                    if (isset($v2['submodule']) && !array_key_get($result, 'dep.submodule.' . $v2['submodule'])) {
                        continue;
                    }
                    $ini_data = self::ini($v2['ini_file'], $result['environment'], $options);
                    $result = array_merge_hard($result, $ini_data);
                }
            }
        }
        return $result;
    }
}
