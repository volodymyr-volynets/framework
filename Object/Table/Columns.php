<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Table;

use Object\Content\Messages;
use Object\Data;
use Object\Data\Common;
use NF\Error;

class Columns extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object Table Columns';
    public $column_key = 'code';
    public $column_prefix = null; // must not change it
    public $orderby = [];
    public $columns = [
        'code' => ['name' => 'Attribute Code', 'domain' => 'code'],
        'name' => ['name' => 'Name', 'type' => 'text'],
        'description' => ['name' => 'Description', 'type' => 'text']
    ];
    public $data = [
        'name' => ['name' => 'Name', 'description' => 'Name of a column'],
        // ddl related attributes
        'domain' => ['name' => 'Domain', 'description' => 'Domain from \Object\Data\Domains'],
        'type' => ['name' => 'Data Type', 'description' => 'Data Type from \Object\Data\Types'],
        'null' => ['name' => 'Null', 'description' => 'Whether column is null'],
        'default' => ['name' => 'Default', 'description' => 'Default value'],
        'length' => ['name' => 'Length', 'description' => 'String length'],
        'precision' => ['name' => 'Precision', 'description' => 'Numeric/Datetime precision'],
        'scale' => ['name' => 'Scale', 'description' => 'Numeric scale'],
        'sequence' => ['name' => 'Sequence', 'description' => 'Indicates that its a sequence'],
        // php attributes
        'php_type' => ['name' => 'PHP Type'],
        // misc attributes
        'format' => ['name' => '\Format'],
        'format_options' => ['name' => '\Format Options'],
        'align' => ['name' => 'Align'],
        'validator_method' => ['name' => 'Validator Method'],
        'validator_params' => ['name' => 'Validator Params'],
        'placeholder' => ['name' => 'Placeholder'],
        'searchable' => ['name' => 'Searchable'],
        'tree' => ['name' => 'Tree']
    ];

    /**
     * Process single column
     *
     * @param string $column_name
     * @param array $column_options
     * @param array $data
     * @param array $options
     * @return array
     */
    public static function processSingleColumn($column_name, $column_options, $data, $options = [])
    {
        $result = [];
        // process domain
        if (!empty($options['process_domains'])) {
            $temp = [$column_name => $column_options];
            $temp = Common::processDomainsAndTypes($temp);
            $column_options = $temp[$column_name];
        }
        // if we ignoring not set fields
        if (!empty($options['ignore_not_set_fields']) && !array_key_exists($column_name, $data)) {
            return $result;
        }
        // processing
        $value = $data[$column_name] ?? null;
        if (is_array($value)) {
            $result2 = [];
            foreach ($value as $k => $v) {
                $temp = self::processSingleColumnType($column_name, $column_options, $v, ['ignore_defaults' => $options['ignore_defaults'] ?? false]);
                if (array_key_exists($column_name, $temp) && $temp[$column_name] !== null) {
                    $result2[] = $temp[$column_name];
                }
            }
            $result[$column_name] = $result2;
        } else {
            $result = self::processSingleColumnType($column_name, $column_options, $value, ['ignore_defaults' => $options['ignore_defaults'] ?? false]);
        }
        return $result;
    }

    /**
     * Process single type for column
     *
     * @param string $column_name
     * @param array $column_options
     * @param mixed $value
     * @return array
     */
    public static function processSingleColumnType($column_name, $column_options, $value, $options = [])
    {
        // we need to fix default
        if (isset($column_options['default'])) {
            foreach (['dependent::', 'parent::', 'master_object::', 'static::'] as $v) {
                if (strpos($column_options['default'] . '', $v) !== false) {
                    unset($column_options['default']);
                    break;
                }
            }
        }
        // process domain
        if (!empty($options['process_domains'])) {
            $temp = [$column_name => $column_options];
            $temp = Common::processDomainsAndTypes($temp);
            $column_options = $temp[$column_name];
        }
        $result = [];
        // processing as per different data types
        if ($column_options['type'] == 'boolean') { // booleans
            $result[$column_name] = !empty($value) ? 1 : 0;
        } elseif (in_array($column_options['type'], ['smallserial', 'serial', 'bigserial'])) {
            if (\Format::readIntval($value, ['valid_check' => 1])) {
                $temp = \Format::readIntval($value);
                if ($temp !== 0) {
                    $result[$column_name] = $temp;
                }
            } else {
                $result[$column_name . '_is_serial_error'] = true;
            }
            $result[$column_name . '_is_serial'] = true;
        } elseif (in_array($column_options['type'], ['smallint', 'integer', 'bigint'])) { // integers
            // if we got empty string we say its null
            if (is_string($value) &&  $value === '') {
                $value = null;
            }
            if (is_null($value)) {
                if (!empty($column_options['null']) || !empty($options['ignore_defaults'])) {
                    $result[$column_name] = null;
                } else {
                    $result[$column_name] = $column_options['default'] ?? 0;
                }
            } else {
                $result[$column_name] = \Format::readIntval($value);
            }
        } elseif (in_array($column_options['type'], ['numeric', 'bcnumeric'])) { // numerics as floats or strings
            // if we got empty string we say its null
            if (is_string($value) &&  $value === '') {
                $value = null;
            }
            if (is_null($value)) {
                if (!empty($column_options['null']) || !empty($options['ignore_defaults'])) {
                    $result[$column_name] = null;
                } else {
                    $result[$column_name] = $column_options['default'] ?? ($column_options['type'] == 'bcnumeric' ? '0' : 0);
                }
            } else {
                $result[$column_name] = \Format::readFloatval($value, ['bcnumeric' => $column_options['type'] == 'bcnumeric']);
            }
        } elseif (in_array($column_options['type'], ['date', 'time', 'datetime', 'timestamp'])) {
            $result[$column_name] = \Format::readDate($value, $column_options['type']);
            // for datetime we do additional processing
            if (!empty($options['process_datetime'])) {
                $result[$column_name . '_strtotime_value'] = 0;
                if (!empty($value)) {
                    $result[$column_name . '_strtotime_value'] = strtotime($result[$column_name]);
                }
            }
        } elseif ($column_options['type'] == 'json') {
            if (!is_json($value)) {
                $result[$column_name] = json_encode($value);
            } else {
                $result[$column_name] = $value;
            }
        } elseif ($column_options['type'] == 'mixed') {
            $result[$column_name] = $value;
        } elseif (!empty($column_options['multiple_column'])) {
            $result[$column_name] = (array) $value;
        } else {
            if (is_null($value)) {
                $result[$column_name] = null;
            } elseif (($column_options['method'] ?? '') == 'file') {
                $result[$column_name] = $value;
            } else {
                // we need to convert numeric strings
                if (($column_options['format'] ?? '') == 'id') {
                    $result[$column_name] = \Format::numberToFromNativeLanguage($value, [], true);
                } else {
                    $result[$column_name] = (string) $value;
                }
            }
        }
        return $result;
    }

    /**
     * Validate single column
     *
     * @param string $column_name
     * @param array $column_options
     * @param type $value
     * @param array $options
     * @return array
     */
    public static function validateSingleColumn(string $column_name, array $column_options, $value, array $options = []): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => null
        ];
        // perform validation
        $result['data'] = self::processSingleColumnType($column_name, $column_options, $value, ['process_datetime' => true]);
        if (array_key_exists($column_name, $result['data'])) {
            // validations
            $error = false;
            // perform validation
            if ($column_options['type'] == 'boolean') {
                if (!empty($value) && ($value . '' != $result['data'][$column_name] . '')) {
                    $result['error'][] = i18n(null, 'Wrong boolean value!');
                    $error = true;
                }
            } elseif (in_array($column_options['type'], ['date', 'time', 'datetime', 'timestamp'])) { // dates first
                if (!empty($value) && empty($result['data'][$column_name . '_strtotime_value'])) {
                    $result['error'][] = i18n(null, 'Invalid date, time or datetime!');
                    $error = true;
                }
            } elseif ($column_options['php_type'] == 'integer') {
                if (!is_array($value) && $value . '' !== '' && !\Format::readIntval($value, ['valid_check' => 1])) {
                    $result['error'][] = i18n(null, 'Wrong integer value!');
                    $error = true;
                }
                // null processing
                if (!$error) {
                    if (empty($result['data'][$column_name]) && !empty($column_options['null'])) {
                        $result['data'][$column_name] = null;
                    }
                }
            } elseif ($column_options['php_type'] == 'bcnumeric') { // accounting numbers
                if ($value . '' !== '' && !\Format::readBcnumeric($value, ['valid_check' => 1])) {
                    $result['error'][] = i18n(null, 'Wrong numeric value!');
                    $error = true;
                }
                // precision & scale validations
                if (!$error) {
                    // validate scale
                    $digits = explode('.', $result['data'][$column_name] . '');
                    if (!empty($column_options['scale'])) {
                        if (!empty($digits[1]) && strlen($digits[1]) > $column_options['scale']) {
                            $result['error'][] = i18n(null, 'Only [digits] fraction digits allowed!', ['replace' => ['[digits]' => i18n(null, $column_options['scale'])]]);
                            $error = true;
                        }
                    }
                    // validate precision
                    if (!empty($column_options['precision'])) {
                        $precision = $column_options['precision'] - $column_options['scale'] ?? 0;
                        if (strlen($digits[0]) > $precision) {
                            $result['error'][] = i18n(null, 'Only [digits] digits allowed!', ['replace' => ['[digits]' => i18n(null, $precision)]]);
                            $error = true;
                        }
                    }
                }
            } elseif ($column_options['php_type'] == 'float') { // regular floats
                if ($value . '' !== '' && !\Format::readFloatval($value, ['valid_check' => 1])) {
                    $result['error'][] = i18n(null, 'Wrong float value!');
                    $error = true;
                }
                // null processing
                if (!$error) {
                    if (empty($result['data'][$column_name]) && !empty($column_options['null'])) {
                        $result['data'][$column_name] = null;
                    }
                }
            } elseif ($column_options['php_type'] == 'string' && ($column_options['method'] ?? '') != 'file') {
                // we need to convert empty string to null
                if (!empty($column_options['multiple_column'])) {
                    if (empty($result['data'][$column_name])) {
                        $result['data'][$column_name] = [];
                    }
                } elseif ($result['data'][$column_name] . '' === '' && !empty($column_options['null'])) {
                    $result['data'][$column_name] = null;
                }
                // validate string length
                if (!empty($result['data'][$column_name])) {
                    $temp = is_array($result['data'][$column_name]) ? $result['data'][$column_name] : [$result['data'][$column_name]];
                    // validate length
                    foreach ($temp as $tv) {
                        if (!empty($column_options['type']) && $column_options['type'] == 'char' && strlen($tv) != $column_options['length']) {  // char
                            $result['error'][] = loc(Error::LENGTH_MUST_BE_LONG, '', ['length' => $column_options['length']]);
                            $error = true;
                        } elseif (!empty($column_options['length']) && strlen($tv) > $column_options['length']) { // varchar
                            $result['error'][] = loc(Error::STRING_IS_TOO_LONG, '', ['length' => $column_options['length']]);
                            $error = true;
                        }
                    }
                }
            }
            $result['data']['flag_error'] = $error;
        } elseif (!empty($result['data'][$column_name . '_is_serial'])) {
            if ($value . '' !== '' && !empty($result['data'][$column_name . '_is_serial_error'])) {
                $result['error'][] = i18n(null, 'Wrong sequence value!');
                $result['data']['flag_error'] = true;
            }
        } else {
            $result['error'][] = i18n(null, Messages::UNKNOWN_VALUE);
            $result['data']['flag_error'] = true;
        }
        if (empty($result['error'])) {
            $result['success'] = true;
        }
        return $result;
    }
}
