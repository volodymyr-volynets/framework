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

use Object\Content\Messages;

class Tree
{
    /**
     * Convert array to a tree using parent field
     *
     * @param array $data
     * @param string $parent_field
     * @param array $options
     *		boolean disable_parents
     */
    public static function convertByParent($data, $parent_field, $options = [])
    {
        $pointers = [];
        foreach ($data as $k => $v) {
            if (empty($v[$parent_field])) {
                continue;
            }
            // if parent is down the road
            if (!empty($data[$v[$parent_field]])) {
                $data[$v[$parent_field]]['options'][$k] = $data[$k];
                // disable parents
                if (!empty($options['disable_parents'])) {
                    $data[$v[$parent_field]]['disabled'] = 1;
                }
                $pointers[$k] = & $data[$v[$parent_field]]['options'][$k];
                unset($data[$k]);
            } elseif (!empty($pointers[$v[$parent_field]])) {
                $pointer = & $pointers[$v[$parent_field]];
                $pointer['options'][$k] = $data[$k];
                $pointers[$k] = & $pointer['options'][$k];
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * Find a key in a tree
     *
     * @param array $data
     * @param mixed $key
     * @param array $hashes
     * @return mixed
     */
    public static function findKeyInATree($data, $key, $hashes = [])
    {
        if (!empty($data[$key])) {
            $hashes[] = $key;
            return $hashes;
        } else {
            foreach ($data as $k => $v) {
                if (empty($v['options'])) {
                    continue;
                }
                $hashes2 = $hashes;
                $hashes2[] = $k;
                $hashes2[] = 'options';
                $result = self::find_key_in_a_tree($v['options'], $key, $hashes2);
                if ($result !== false) {
                    return $result;
                }
            }
        }
        return false;
    }

    /**
     * Convert tree to options multi
     *
     * @param array $data
     * @param int $level
     * @param array $options
     * @param array $result
     */
    public static function convertTreeToOptionsMulti($data, $level = 0, $options = [], & $result = [], $parent_keys = [])
    {
        if (empty($options['name_field'])) {
            $options['name_field'] = 'name';
        }
        if (!isset($options['i18n'])) {
            $options['i18n'] = true;
        }
        // skip_keys - convert to array
        if (!empty($options['skip_keys']) && !is_array($options['skip_keys'])) {
            $options['skip_keys'] = [$options['skip_keys']];
        }
        $inactive = i18n(null, Messages::INFO_INACTIVE);
        // translate name column
        $order_present = false;
        foreach ($data as $k => $v) {
            if (is_array($options['name_field'])) {
                $temp = [];
                foreach ($options['name_field'] as $k2 => $v2) {
                    if (!isset($v[$v2])) {
                        continue;
                    }
                    $temp[] = !empty($options['i18n']) ? i18n(null, $v[$v2]) : $v[$v2];
                }
                $data[$k]['name'] = implode(\Format::$symbol_comma . ' ', $temp);
            } else {
                $data[$k]['name'] = !empty($options['i18n']) ? i18n(null, $v[$options['name_field']]) : $v[$options['name_field']];
            }
            // handle inactive
            if (!empty($v['inactive']) && strpos($data[$k]['name'], \Format::$symbol_comma . ' ' . $inactive) !== false) {
                $data[$k]['name'] .= \Format::$symbol_comma . ' ' . $inactive;
            }
            // order
            if (isset($v['order'])) {
                $order_present = true;
            }
        }
        // sorting
        if ($order_present) {
            $options['orderby'] = ['order' => SORT_ASC];
        }
        if (!empty($options['orderby'])) {
            array_key_sort($data, $options['orderby']);
        } elseif (!empty($options['i18n']) && $options['i18n'] !== 'skip_sorting') {
            array_key_sort($data, ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
        }
        // assemble
        foreach ($data as $k => $v) {
            // if we are skipping certain keys
            if (!empty($options['skip_keys']) && in_array($k, $options['skip_keys'])) {
                continue;
            }
            // assemble variable
            $value = $v;
            $value['level'] = $level;
            if (!empty($options['icon_field'])) {
                $value['icon_class'] = \HTML::icon(['type' => $v[$options['icon_field']], 'class_only' => true]);
            }
            if (!empty($options['disabled_field'])) {
                $value['disabled'] = !empty($v[$options['disabled_field']]);
            }
            if (!empty($options['prepend_parent_keys'])) {
                $parent_keys2 = $parent_keys;
                $parent_keys2[] = $k;
                $result[implode('::', $parent_keys2)] = $value;
            } else {
                $result[$k] = $value;
            }
            // if we have options
            if (!empty($v['options'])) {
                $parent_keys2 = $parent_keys;
                $parent_keys2[] = $k;
                self::convertTreeToOptionsMulti($v['options'], $level + 1, $options, $result, $parent_keys2);
            }
        }
    }
}
