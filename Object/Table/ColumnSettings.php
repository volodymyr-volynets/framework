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

use Object\ActiveRecord;

trait ColumnSettings
{
    /**
     * To string
     *
     * @param array $column_settings
     * @param array $row
     * @param array $options
     * @return bool
     */
    public function processColumnSettingsForObjects(array $column_settings, ActiveRecord & $object, array $options = []): bool
    {
        $options['skip_column_settings'] ??= [];
        if (empty($column_settings) || in_array(ALL, $options['skip_column_settings'])) {
            return true;
        }
        foreach ($column_settings as $k => $v) {
            foreach ($v as $k2 => $v2) {
                // we skip action keys
                if (in_array($k2, ACTION_KEYS)) {
                    continue;
                }
                switch ($v2) {
                    case PASSWORDABLE:
                        if (!in_array(PASSWORDABLE, $options['skip_column_settings'])) {
                            if (isset($object->{$k})) {
                                $object->logChanges([$k => $object->{$k}]);
                                $object->{$k} = '**********';
                            }
                        }
                        break;
                    case MASKABLE:
                        if (!in_array(MASKABLE, $options['skip_column_settings'])) {
                            if (isset($object->{$k})) {
                                $object->{$k} = str_pad('', strlen($object->{$k}), 'A');
                            }
                        }
                        break;
                    case GENERABLE:
                        if (!in_array(GENERABLE, $options['skip_column_settings'])) {
                            if (isset($v['concat'])) {
                                $temp = [];
                                $separator = array_shift($v['concat']);
                                foreach ($v['concat'] as $v3) {
                                    $temp[] = $object->{$v3};
                                }
                                $object->{$k} = implode($separator, $temp);
                            } elseif (isset($v['method'])) {
                                $object->logChanges([$k => $object->{$k}]);
                                if (is_class_method_exists($object, $v['method'], 'public')) {
                                    $object->{$v['method']}($object);
                                } else {
                                    $object->getTableObject()->{$v['method']}($object);
                                }
                            }
                        }
                        break;
                    case CASTABLE:
                        if (!in_array(CASTABLE, $options['skip_column_settings'])) {
                            $temp = $object->{$k};
                            settype($temp, $v['php_type']);
                            $object->{$k} = $temp;
                        }
                        break;
                    case FORMATABLE:
                        if (!in_array(FORMATABLE, $options['skip_column_settings'])) {
                            if (strpos($v['format'], '::') === false) {
                                $object->{$k} = call_user_func_array(['\Format', $v['format']], [$object->{$k}, $v['options'] ?? []]);
                            } else {
                                $object->{$k} = \Factory::callMethod($v['format'], true, [$object->{$k}, $v['options'] ?? []]);
                            }
                        }
                        break;
                }
            }
        }
        return true;
    }
}
