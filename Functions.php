<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Cmd;

/**
 * Constants
 */
define('DANGER', 'danger');
define('WARNING', 'warning');
define('SUCCESS', 'success');
define('GENERAL', 'general');
define('INFO', 'info');
define('DEF', 'default');
define('DEF2', 'default2');
define('DEF3', 'default3');
define('DEF4', 'default4');
define('DEF5', 'default5');
define('CHART', 'chart');
define('CHART2', 'chart2');
define('CHART3', 'chart3');
define('CHART4', 'chart4');
define('CHART5', 'chart5');
define('NONE', 0);
define('ODD', 1);
define('EVEN', 2);
// table / active record constants
define('MASKABLE', 'MASKABLE');
define('PASSWORDABLE', 'PASSWORDABLE');
define('GENERABLE', 'GENERABLE');
define('READ_ONLY', 'READ_ONLY');
define('READ_IF_SET', 'READ_IF_SET');
define('CASTABLE', 'CASTABLE');
define('FORMATABLE', 'FORMATABLE');
define('ACTION_ALL', [MASKABLE, PASSWORDABLE, GENERABLE, READ_ONLY, READ_IF_SET, CASTABLE, FORMATABLE]);
define('ACTION_KEYS', ['concat', 'method', 'php_type', 'format', 'options']);
define('ALL', 'ALL');
// date constants
define('YEAR', 'YEAR');
define('YEAR_AND_MONTH', 'YEAR_AND_MONTH');
define('MONTH', 'MONTH');
define('PERIOD', 'PERIOD');
define('DAY', 'DAY');
// result
define('RESULT_BLANK', ['success' => false, 'error' => [], 'data' => []]);
define('RESULT_SUCCESS', ['success' => true, 'error' => [], 'data' => []]);

/**
 * Concatenate parameters if not empty
 *
 * @param string $delimiter
 * @param mized $arg1
 * @return string
 */
function concat_ws($delimiter, $arg1)
{
    $arrays = func_get_args();
    $delimiter = array_shift($arrays);
    $temp = [];
    foreach ($arrays as $v) {
        if ($v . '' != '') {
            $temp[] = $v;
        }
    }
    return implode($delimiter, $temp);
}

/**
 * Concatenate array if not empty
 *
 * @param string $delimiter
 * @param array $arr
 * @return string
 */
function concat_ws_array($delimiter, $arr)
{
    $temp = [];
    foreach ($arr as $v) {
        if ($v . '' != '') {
            $temp[] = $v;
        }
    }
    return implode($delimiter, $temp);
}

/**
 * Compare and swap two variables if needed, order can be different
 *
 * @param mixed $a
 * @param mixed $b
 * @param boolean $min
 */
function swap(& $a, & $b, $min = true)
{
    if ($min) {
        if ($a > $b) {
            list($a, $b) = array($b, $a);
        }
    } else {
        if ($a < $b) {
            list($a, $b) = array($b, $a);
        }
    }
}

/**
 * Merge values into an array
 *
 * @param array $arr
 * @param array $val
 * @return array
 */
function array_merge_values($arr, $val)
{
    foreach ($arr as $k => $v) {
        $arr[$k] = array_merge2($v, $val);
    }
    return $arr;
}

/**
 * Transform options to string
 *
 * @param array $options
 * @param mixed $option
 * @param string $implode
 * @return string
 */
function array_options_to_string($options, $option, $implode = ',')
{
    $option = array_fix($option);
    $result = [];
    foreach ($options as $k => $v) {
        if (in_array($k, $option)) {
            $result[$k] = $v['name'];
        }
    }
    return implode($implode, $result);
}

/**
 * Fix an array string
 *
 * @param mixed $tokens
 * @param string $explode_on
 * @return array
 */
function array_fix($tokens, $explode_on = null)
{
    if (is_array($tokens)) {
        return array_values($tokens);
    } else {
        if ($tokens . '' == '') {
            return [];
        } else {
            $tokens .= '';
            if ($explode_on !== null) {
                return explode($explode_on, $tokens);
            } else {
                return [$tokens];
            }
        }
    }
}

/**
 * Add token to an array
 *
 * @param array $tokens
 * @param string $token
 * @param string $explode_on
 * @return array
 */
function array_add_token($tokens, $token, $explode_on = null)
{
    $tokens = array_fix($tokens, $explode_on);
    $token = array_fix($token, $explode_on);
    return array_unique(array_merge($tokens, $token));
}

/**
 * Remove a token from array
 *
 * @param array $tokens
 * @param string $token
 * @return array
 */
function array_remove_token($tokens, $token)
{
    $tokens = array_fix($tokens);
    foreach ($tokens as $k => $v) {
        if ($v == $token) {
            unset($tokens[$k]);
        }
    }
    return $tokens;
}

/**
 * Print variable
 *
 * @param mixed $data
 * @param string $name
 * @param boolean $return
 * @param array $options
 * 		width
 * @return string
 */
function print_r2($data, string $name = '', bool $return = false, array $options = [])
{
    if (!empty($name)) {
        $name = "\n[" . $name . "]\n\n";
    }
    $style = '';
    if (isset($options['width'])) {
        $style = 'width: ' . $options['width'];
    }
    // for command line we just print
    if (Cmd::isCli()) {
        $result = $name . print_r($data, true) . "\n";
    } else { // HTML
        $result = '<pre style="' . $style . '">' . $name . print_r($data, true) . '</pre>' . "\n";
        // line where it was called
        if (Debug::$debug) {
            $temp = debug_backtrace();
            $caller = array_shift($temp);
            $result .= '<br/>' . $caller['file'] . ':' . $caller['line'] . '<hr/>' . "\n";
        }
    }
    if ($return) {
        return $result;
    } else {
        echo $result;
    }
}

/**
 * Export variable
 *
 * @param mixed $data
 * @param string $return
 * @return string
 */
function var_export2($data, $return = false)
{
    if ($return) {
        return '<pre>' . var_export($data, true) . '</pre>';
    } else {
        $result = '<pre>' . var_export($data, true) . '</pre>';
        // line where it was called
        if (Debug::$debug) {
            $temp = debug_backtrace();
            $caller = array_shift($temp);
            $result .= '<br/>' . $caller['file'] . ':' . $caller['line'] . '<hr/>' . "\n";
        }
        echo $result;
    }
}

/**
 * Export variable in condensed format
 *
 * @param mixed $data
 * @param array $options
 * 		boolean skip_objects
 * @return string
 */
function var_export_condensed($data, $options = [])
{
    if (is_array($data)) {
        $buffer = [];
        $is_numeric_key_array = is_numeric_key_array($data);
        foreach ($data as $k => $v) {
            $temp = '';
            if (!$is_numeric_key_array) {
                $temp .= var_export($k, true) . '=>';
            }
            $temp .= var_export_condensed($v, $options);
            $buffer[] = $temp;
        }
        return '[' . implode(',', $buffer) . ']';
    } else {
        if (!empty($options['skip_objects']) && gettype($data) == 'object') {
            return '(object) ' . get_class($data);
        } else {
            return var_export($data, true);
        }
    }
}

/**
 * Primary key
 *
 * @param mixed $pk
 * @param array $data
 * @param boolean $return_extracted
 * @return array
 */
function pk($pk, & $data, $return_extracted = false)
{
    $pk = array_fix($pk);
    if (!is_array($data)) {
        $data = [];
    }
    if (!$return_extracted) {
        $result = [];
        foreach ($data as $k => $v) {
            array_key_set_by_key_name($result, $pk, $v);
        }
        $data = $result;
    } else {
        $result = [];
        foreach ($pk as $k) {
            $result[$k] = array_key_exists($k, $data) ? $data[$k] : null;
            unset($data[$k]);
        }
        return $result;
    }
}

/**
 * Merge multiple arrays recursively
 *
 * @param array $arr1
 * @param array $arr2
 * @return array
 */
function array_merge2($arr1, $arr2)
{
    $arrays = func_get_args();
    $merged = array();
    while ($arrays) {
        $array = array_shift($arrays);
        if (!is_array($array) || empty($array)) {
            continue;
        }
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                    $merged[$key] = array_merge2($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            } else {
                $merged[] = $value;
            }
        }
    }
    return $merged;
}

/**
 * Merge multiple arrays, result is first array in parameters
 *
 * @param array $arr1
 * @param array $arr2
 */
function array_merge3(& $arr1, $arr2)
{
    $arr1 = call_user_func_array('array_merge2', func_get_args());
}

/**
 * Hard merging
 *
 * @param array $arr1
 * @param array $arr2
 * @return array
 */
function array_merge_hard($arr1, $arr2)
{
    $arrays = func_get_args();
    $merged = [];
    while ($arrays) {
        $array = array_shift($arrays);
        if (!is_array($array) || empty($array)) {
            continue;
        }
        foreach ($array as $k => $v) {
            if (is_array($v) && array_key_exists($k, $merged) && is_array($merged[$k])) {
                $merged[$k] = array_merge_hard($merged[$k], $v);
            } else {
                $merged[$k] = $v;
            }
        }
    }
    return $merged;
}

/**
 * Merging with skipping
 *
 * @param array $arr1
 * @param array $arr2
 * @return array
 */
function array_merge_skip($arr1, $arr2)
{
    $arrays = func_get_args();
    $merged = [];
    while ($arrays) {
        $array = array_shift($arrays);
        if (!is_array($array) || empty($array)) {
            continue;
        }
        foreach ($array as $k => $v) {
            if (is_array($v) && array_key_exists($k, $merged) && is_array($merged[$k])) {
                $merged[$k] = array_merge_skip($merged[$k], $v);
            } else {
                if (!array_key_exists($k, $merged)) {
                    $merged[$k] = $v;
                }
            }
        }
    }
    return $merged;
}

/**
 * Extract primary key values from an array
 *
 * @param mixed $keys
 * @param array $data
 * @return array
 */
function extract_keys($keys, $data)
{
    $keys = is_array($keys) ? $keys : [$keys];
    $result = [];
    foreach ($keys as $k) {
        $result[$k] = $data[$k] ?? null;
    }
    return $result;
}

/**
 * Extract values out of array by key
 *
 * @param array $array
 * @param string $key
 * @param array $options
 * 		boolean unique
 *		type string
 * @return array
 */
function array_extract_values_by_key(array $array, string $key, array $options = []): array
{
    $result = [];
    foreach ($array as $v) {
        if (!empty($options['where'])) {
            $found = true;
            foreach ($options['where'] as $k2 => $v2) {
                if ($v[$k2] !== $v2) {
                    $found = false;
                    break;
                }
            }
            if (!$found) {
                continue;
            }
        }
        if (isset($options['type'])) {
            if ($options['type'] == 'varchar') {
                if (is_array($v[$key])) {
                    $result[] = current($v[$key]) . '';
                } else {
                    $result[] = $v[$key] . '';
                }
            } else {
                $result[] = $v[$key];
            }
        } else {
            $result[] = $v[$key];
        }
    }
    // if unique
    if (!empty($options['unique'])) {
        $result = array_unique($result);
    }
    return $result;
}

/**
 * Build a query string and strip empty fields
 *
 * @param array $arr
 * @return string
 */
function http_build_query2($arr)
{
    foreach ($arr as $k => $v) {
        if (is_scalar($v) && $v . '' == '') {
            unset($arr[$k]);
        }
    }
    return http_build_query($arr);
}

/**
 * Add parameters to URL
 *
 * @param string $url
 * @param array $parameters
 * @return string
 */
function http_append_to_url(string $url, array $parameters): string
{
    if (!strpos($url, '?')) {
        $url .= '?';
    }
    foreach ($parameters as $k => $v) {
        $url .= '&' . $k . '=' . ($v . '');
    }
    return $url;
}

/**
 * Strip tags
 *
 * @param array|string $arr
 * @param array $options
 *		array skip_xss_on_keys
 *		boolean trim_empty_html_input
 *		boolean remove_script_tag
 * @return array
 */
function strip_tags2($arr, array $options = [])
{
    if (is_array($arr)) {
        $result = [];
        foreach ($arr as $k => $v) {
            if (is_string($k)) {
                $k = strip_tags($k);
            }
            // when we need to skip some keys
            if (!empty($options['skip_xss_on_keys'])) {
                foreach ($options['skip_xss_on_keys'] as $v2) {
                    if (strpos($k, $v2) !== false) {
                        // remove javascript tags
                        if (!empty($options['remove_script_tag'])) {
                            $v = sanitize_string_tags($v, 'script_only');
                        }
                        // sanitize empty string
                        if (!empty($options['trim_empty_html_input'])) {
                            $temp = sanitize_string_tags($v, 'all');
                            $temp = trim($temp, "'\n\t\" ");
                            if ($temp == '') {
                                $v = null;
                            }
                        }
                        $result[$k] = $v;
                        goto end_of_loop;
                    }
                }
            }
            if (strpos($k, '_wysiwyg') !== false) {
                $result[$k] = sanitize_string_tags($v, 'script_only');
                $temp = sanitize_string_tags($result[$k], 'all');
                $temp = trim($temp, "'\n\t\" ");
                if ($temp == '') {
                    $result[$k] = null;
                }
            } else {
                $result[$k] = strip_tags2($v, $options);
            }
            end_of_loop:
        }
        return $result;
    } elseif (is_string($arr)) {
        return sanitize_string_tags($arr, 'all');
    }
    return $arr;
}

/**
 * Check is string has tags
 *
 * @param string $input
 * @param array $tags
 * @return bool
 */
function has_tags(string $input, array $tags): bool
{
    foreach ($tags as $v) {
        if (stripos($input, $v) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Sanitize tags
 *
 * @param type $str
 * @param string $type
 * @param array $options
 *	boolean remove_white_spaces
 * @return string
 */
function sanitize_string_tags($str, string $type = 'all', array $options = []): string
{
    switch ($type) {
        case 'script_only':
            return preg_replace('/<script[^>]*?.*?<\/script>/siu', ' ', $str . '');
        case 'all':
        default:
            $str = preg_replace(['/<head[^>]*?>.*?<\/head>/siu', '/<style[^>]*?>.*?<\/style>/siu', '/<script[^>]*?.*?<\/script>/siu', '/<object[^>]*?.*?<\/object>/siu', '/<embed[^>]*?.*?<\/embed>/siu', '/<applet[^>]*?.*?<\/applet>/siu', '/<noframes[^>]*?.*?<\/noframes>/siu', '/<noscript[^>]*?.*?<\/noscript>/siu', '/<noembed[^>]*?.*?<\/noembed>/siu'], ' ', $str . '');
            $str = preg_replace(['/<((br)|(hr))/iu', '/<\/?((address)|(blockquote)|(center)|(del))/iu', '/<\/?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))/iu', '/<\/?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))/iu', '/<\/?((table)|(th)|(td)|(caption))/iu', '/<\/?((form)|(button)|(fieldset)|(legend)|(input))/iu', '/<\/?((label)|(select)|(optgroup)|(option)|(textarea))/iu', '/<\/?((frameset)|(frame)|(iframe))/iu'], "\n\$0", $str);
            $str = str_replace('&nbsp;', ' ', $str);
            $str = str_replace('&amp;', '&', $str);
    }
    if (empty($options['remove_white_spaces'])) {
        return strip_tags($str);
    } else {
        $result = strip_tags($str);
        $result = str_replace(["\n", "\t"], ' ', $result);
        $result = preg_replace('/\s\s+/', ' ', $result);
        return $result;
    }
}

/**
 * Remap keys in an array
 *
 * @param array $data
 * @param array $map
 * @param boolean $unique
 * @return array
 */
function remap(& $data, $map, $unique = false)
{
    $result = [];
    $lock = [];
    foreach ($data as $k => $v) {
        foreach ($map as $k2 => $v2) {
            $k2 = str_replace('*', '', $k2);
            if (isset($result[$k][$v2])) {
                if (isset($v[$k2])) {
                    if ($v[$k2] . '' !== '') {
                        if ($unique) {
                            if (!isset($lock[$k][$v2])) {
                                $result[$k][$v2] .= Format::$symbol_semicolon . ' ' . $v[$k2];
                                $lock[$k][$v2] = [$v[$k2]];
                            } elseif (!in_array($v[$k2], $lock[$k][$v2])) {
                                $result[$k][$v2] .= Format::$symbol_semicolon . ' ' . $v[$k2];
                                $lock[$k][$v2][] = $v[$k2];
                            }
                        } else {
                            $result[$k][$v2] .= Format::$symbol_semicolon . ' ' . $v[$k2];
                        }
                    }
                }
            } else {
                if ($unique && isset($v[$k2])) {
                    $lock[$k][$v2] = [$v[$k2]];
                }
                $result[$k][$v2] = $v[$k2] ?? null;
            }
        }
    }
    return $result;
}

/**
 * Array to field conversion
 *
 * @param array $arr
 * @return string
 */
function array_to_field(array $arr): string
{
    $first = array_shift($arr);
    $result = $first;
    foreach ($arr as $v) {
        $result .= '[' . $v . ']';
    }
    return $result;
}

/**
 * Change key name in array
 *
 * @param array $arr
 * @param int|string $old_key
 * @param int|string $new_key
 * @return array
 */
function array_change_key_name(array $arr, $old_key, $new_key): array
{
    if (!array_key_exists($old_key, $arr)) {
        return $arr;
    }
    $keys = array_keys($arr);
    $keys[array_search($old_key, $keys)] = $new_key;
    return array_combine($keys, $arr);
}

/**
 * Sort an array by certain keys with certain methods
 *
 * @param array $arr
 * @param array $keys
 * 		['id' => SORT_ASC, 'name' => SORT_DESC]
 * 		['id' => 'asc', 'name' => 'desc']
 * @param array $methods
 * 		['id' => SORT_NUMERIC, 'name' => SORT_NATURAL]
 */
function array_key_sort(& $arr, $keys, $methods = [])
{
    // null, [], 0, etc
    if (empty($arr)) {
        return;
    }
    // prepare keys
    $keys = array_key_sort_prepare_keys($keys, false);
    // prepare a single array of parameters for multisort function
    $params = [];
    foreach ($keys as $k => $v) {
        $params[$k . '_column'] = [];
        $params[$k . '_order'] = $v;
        $params[$k . '_flags'] = $methods[$k] ?? SORT_REGULAR;
        foreach ($arr as $k2 => $v2) {
            $params[$k . '_column']['_' . $k2] = $v2[$k] ?? null;
        }
    }
    // calling multisort function
    $final = [];
    foreach ($arr as $k => $v) {
        $final['_' . $k] = $v;
    }
    $params = array_values($params);
    $params[] = & $final;
    call_user_func_array('array_multisort', $params);
    // convert keys back
    $arr = [];
    foreach ($params[array_key_last($params)] as $k => $v) {
        $arr[substr($k, 1)] = $v;
    }
}

/**
 * Prepare orderby
 *
 * @param array $keys
 * 		['id' => SORT_ASC, 'name' => SORT_DESC]
 * 		['id' => 'asc', 'name' => 'desc']
 * @param boolean $flag_string
 * 		set this and you can use result in order by clauses
 * @return mixed
 */
function array_key_sort_prepare_keys($keys, $flag_string = false)
{
    // after this loop we would have proper keys
    foreach ($keys as $k => $v) {
        if (in_array($v, [SORT_ASC, SORT_DESC])) {
            // we accept those as is
        } elseif (strtolower($v) == 'desc') {
            $keys[$k] = SORT_DESC;
        } else {
            $keys[$k] = SORT_ASC;
        }
    }
    // if we need to generate string for ORDER BY clause
    if ($flag_string) {
        $str = [];
        foreach ($keys as $k => $v) {
            $str[] = $k . ' ' . ($v == SORT_ASC ? 'ASC' : 'DESC');
        }
        return implode(', ', $str);
    } else {
        return $keys;
    }
}

/**
 * Prefix and suffix string to keys in array
 *
 * @param array $arr
 * @param string $prefix
 * @param string $suffix
 * @param boolean $strip
 */
function array_key_prefix_and_suffix(& $arr, $prefix = null, $suffix = null, $strip = false, $existing_check = false)
{
    if ($prefix . '' != '' || $suffix . '' != '') {
        foreach ($arr as $k => $v) {
            // appending / prepending
            if (!$strip) {
                $new_key = $k;
                if ($existing_check) {
                    if ($prefix !== null && !str_starts_with($k, $prefix)) {
                        $new_key = $prefix . $new_key;
                    }
                    if ($suffix !== null && !str_ends_with($k, $suffix)) {
                        $new_key = $new_key . $suffix;
                    }
                } else {
                    $new_key = $prefix . $new_key . $suffix;
                }
                $arr[$new_key] = $v;
            } else { // stripping
                $arr[str_replace([$prefix, $suffix], '', $k)] = $v;
            }
            unset($arr[$k]);
        }
    }
}

/**
 * Prefix and suffix string to keys in multi-dimentional array
 *
 * @param array $arr
 * @param string $prefix
 * @param string $suffix
 * @param boolean $strip
 */
function array_multiple_prefix_and_suffix(& $arr, $prefix = null, $suffix = null, $strip = false, $existing_check = false)
{
    foreach ($arr as $k => $v) {
        array_key_prefix_and_suffix($v, $prefix, $suffix, $strip, $existing_check);
        $arr[$k] = $v;
    }
}


/**
 * Perform math on an array
 *
 * @param array $arr
 * @param mixed $key
 * @param string $type - add, sub, mul, div
 * @return numeric
 */
function array_key_math(& $arr, $key, $type = 'add')
{
    $result = 0;
    foreach ($arr as $v) {
        if ($type == 'add') {
            $result += $v[$key];
        } elseif ($type == 'sub') {
            $result -= $v[$key];
        } elseif ($type == 'mul') {
            $result *= $v[$key];
        } elseif ($type == 'div') {
            $result /= $v[$key];
        }
    }
    return $result;
}

/**
 * Prepare and convert keys to used in other functions
 *
 * @param mixed $keys
 * @return array
 */
function array_key_convert_key($keys): array
{
    if (!is_array($keys)) {
        $keys = str_replace('.', ',', $keys . '');
        $keys = explode(',', $keys);
    }
    return $keys;
}

/**
 * Get value from array by keys
 *
 * @param array $arr
 * @param mixed $keys - keys can be in this format: "1,2,3", "a", 1, array(1,2,3)
 * @param array $options
 * 		unset - if we need to unset the key
 *      present - if value is present it would return true
 * @return mixed
 */
function array_key_get(& $arr, $keys = null, $options = [])
{
    if ($keys === null || (is_array($keys) && empty($keys))) {
        return $arr;
    } else {
        $keys = array_key_convert_key($keys);
        $key = $keys;
        $last = array_pop($key);
        $pointer = & $arr;
        foreach ($key as $k2) {
            if (!isset($pointer[$k2])) {
                if (!empty($options['present'])) {
                    return false;
                }
                return null;
            }
            $pointer = & $pointer[$k2];
        }
        // present
        if (!empty($options['present'])) {
            return array_key_exists($last, $pointer);
        }
        if (isset($pointer[$last])) {
            if (empty($options['unset'])) {
                return $pointer[$last];
            } else { // if we need to unset
                $temp = $pointer[$last];
                unset($pointer[$last]);
                return $temp;
            }
        }
        return null;
    }
}

/**
 * Check if key exists in an array
 *
 * @param array $arr
 * @param mixed $keys
 * @return bool
 */
function array_key_check_if_key_exists(& $arr, $keys): bool
{
    $keys = array_key_convert_key($keys);
    $key = $keys;
    $last = array_pop($key);
    $pointer = & $arr;
    foreach ($key as $k2) {
        if (!array_key_exists($k2, $pointer)) {
            return false;
        }
        $pointer = & $pointer[$k2];
    }
    if (array_key_exists($last, $pointer)) {
        return true;
    }
    return false;
}

/**
 * Set value in the array
 *
 * @param array $arr
 * @param mixed $keys - keys can be in this format: "1,2,3", "a", 1, array(1,2,3)
 * @param mixed $value
 * @param array $options
 * 		boolean append - whether to append value to array
 *		boolean append_unique
 */
function array_key_set(& $arr, $keys = null, $value = null, $options = [])
{
    if (!isset($arr)) {
        $arr = [];
    }
    if ($keys === null) {
        $arr = $value;
    } else {
        $keys = array_key_convert_key($keys);
        $key = $keys;
        $pointer = & $arr;
        foreach ($key as $k2) {
            if (!isset($pointer[$k2])) {
                $pointer[$k2] = [];
            }
            $pointer = & $pointer[$k2];
        }
        if (!empty($options['append'])) {
            if (!is_array($pointer)) {
                $pointer = [];
            }
            if (!empty($options['append_unique']) && in_array($value, $pointer)) {
                // nothing
            } else {
                $pointer[] = $value;
            }
        } else {
            $pointer = $value;
        }
    }
}

/**
 * Set array values based on keys in the array
 *
 * @param array $arr
 * @param mixed $keys
 * @param mixed $value
 * @param array $options
 */
function array_key_set_by_key_name(& $arr, $keys = null, $value = null, $options = array())
{
    // transform keys
    if (!is_array($keys)) {
        $keys = explode(',', $keys . '');
    }
    $temp = [];
    foreach ($keys as $k) {
        if (is_object($value)) {
            $temp[] = $value->{$k};
        } else {
            $temp[] = $value[$k];
        }
    }
    // unsetting keys
    if (!empty($options['unset_keys'])) {
        foreach ($temp as $k2) {
            unset($value[$k2]);
        }
    }
    array_key_set($arr, $temp, $value, $options);
}

/**
 * Difference between two variables
 *
 * @param array $arr1
 * @param array $arr2
 * @param array $options
 * @return mixed
 */
function array_diff_assoc_recursive_by_keys($arr1, $arr2, $options = array())
{
    // if we are dealing with arrays
    if (is_array($arr1) || empty($arr1)) {
        if (is_array($arr2) || empty($arr2)) {
            if (empty($arr1)) {
                $arr1 = array();
            }
            if (empty($arr2)) {
                $arr2 = array();
            }
            // merging to get union of two sets
            $full = Core::array_merge($arr1, $arr2);
            foreach ($full as $k => $v) {
                $diff = array_diff_assoc_recursive_by_keys(@$arr1[$k], @$arr2[$k], $options);
                if ($diff !== false) {
                    $difference[$k] = $diff;
                }
            }
        } else {
            if ($arr1 !== $arr2) {
                $difference = $arr2;
            }
        }
    } else {
        if ($arr1 !== $arr2) {
            // if we are in subtract mode
            if ((is_numeric($arr1) || empty($arr1)) && (is_numeric($arr2) || empty($arr2)) && @$options['subtract']) {
                if (@$options['bcmath']) {
                    $difference = bcsub($arr1 . '', $arr2 . '');
                } else {
                    $difference = $arr1 - $arr2;
                }
            } else {
                $difference = $arr2;
            }
        }
    }
    if (isset($difference) && !is_array($difference) && floatval($difference) == 0) {
        unset($difference);
    }
    return !isset($difference) ? false : $difference;
}

/**
 * Convert multi-dimentional array to array of keys
 *
 * @param array $data
 * @param array $result
 * @param array $keys
 */
function array_keys_to_string($data, & $result, $keys = [])
{
    foreach ($data as $k => $v) {
        $temp = $keys;
        $temp[] = $k;
        if (is_array($v)) {
            array_keys_to_string($v, $result, $temp);
        } else {
            $temp2 = implode('.', $temp);
            $result[$temp2] = $v;
        }
    }
}

/**
 * Compares two array by key and value
 *
 * @param array $arr1
 * @param array $arr2
 * @return boolean
 */
function array_compare_level1($arr1, $arr2)
{
    if (count($arr1) <> count($arr2)) {
        return false;
    }
    foreach ($arr1 as $k => $v) {
        if (!isset($arr2[$k]) || $v != $arr2[$k]) {
            return false;
        }
    }
    foreach ($arr2 as $k => $v) {
        if (!isset($arr1[$k]) || $v != $arr1[$k]) {
            return false;
        }
    }
    return true;
}

/**
 * Compares two array in intelligent way
 *
 * @param array $arr1
 * @param array $arr2
 * @param array $arr1a
 * @param array $arr2a
 * @return boolean
 */
function array_compare_inteligent($arr1, $arr2, $arr1a, $arr2a)
{
    if (count($arr1) <> count($arr2) || count($arr1a) <> count($arr2a)) {
        return false;
    }
    $temp1 = [];
    $temp2 = [];
    foreach ($arr1 as $k => $v) {
        $hashed1 = sha1($arr1[$k] . '::' . ($arr1a[$k] ?? 0));
        $hashed2 = sha1($arr2[$k] . '::' . ($arr2a[$k] ?? 0));
        $temp1[$hashed1] = $hashed1;
        $temp2[$hashed2] = $hashed2;
    }
    foreach ($temp1 as $v) {
        if (!isset($temp2[$v])) {
            return false;
        }
    }
    return true;
}

/**
 * Extract values out of array by key prefix
 *
 * @param array $arr
 * @param string $key_prefix
 * @param boolean $unset
 * @param boolean $not_empty
 * @return array
 */
function array_key_extract_by_prefix(& $arr, $key_prefix, $unset = true, $not_empty = false)
{
    $result = [];
    foreach ($arr as $k => $v) {
        if (strpos($k, $key_prefix) === 0) {
            if ($not_empty) {
                if (empty($v)) {
                    continue;
                }
            }
            $result[str_replace($key_prefix, '', $k)] = $v;
            if ($unset) {
                unset($arr[$k]);
            }
        }
    }
    return $result;
}

/**
 * Unset a set of keys in the array
 *
 * @param array $arr
 * @param array $keys
 * @param array $options
 * 		boolean preserve - keep only these
 */
function array_key_unset(& $arr, $keys, $options = [])
{
    if (empty($options['preserve'])) {
        foreach ($keys as $v) {
            unset($arr[$v]);
        }
    } else {
        foreach (array_keys($arr) as $v) {
            if (!in_array($v, $keys)) {
                unset($arr[$v]);
            }
        }
    }
}

/**
 * Unset a set of keys in the array recursively
 *
 * @param array $arr
 * @param array $keys
 */
function array_key_unset_recursively(& $arr, $keys)
{
    foreach ($arr as $k => $v) {
        if (in_array($k, $keys)) {
            unset($arr[$k]);
        } else {
            if (is_array($arr[$k])) {
                array_key_unset_recursively($arr[$k], $keys);
            }
        }
    }
}

/**
 * Mixed to lowercase
 *
 * @param mixed $mixed
 * @return mixed
 */
function mixedtolower($mixed)
{
    if (is_array($mixed)) {
        return array_map('strtolower', $mixed);
    } else {
        return strtolower($mixed . '');
    }
}

/**
 * I18n, alias
 *
 * @param int $i18n
 * @param mixed $text
 * @param array $options
 * @return string
 */
function i18n($i18n, $text, $options = [])
{
    return I18n::get($i18n, $text, $options);
}

/**
 * i18n if
 *
 * @param type $text
 * @param type $translate
 * @return string
 */
function i18n_if($text, $translate)
{
    if ($translate) {
        return I18n::get(null, $text);
    } else {
        return $text;
    }
}

/**
 * Localize
 *
 * @param string|array $key
 * @param mixed $text
 * @param array $options
 */
function loc(string|array $key, mixed $text = '', array $options = []): string
{
    // sometimes we use empty labels
    if (trim($text ?? '') === '' && empty($key)) {
        return '';
    }
    return I18n::loc($key, $text, $options);
}

/**
 * Is loc
 *
 * @param string|array|null $key
 * @return bool
 */
function is_loc(string|array|null $key): bool
{
    if (is_null($key)) {
        return false;
    } elseif (is_string($key)) {
        $temp = explode('.', $key);
        return $temp[0] == 'NF' && count($temp) == 3;
    } else {
        $first_key = array_key_first($key);
        if (is_string($first_key)) {
            return is_loc($first_key);
        } else {
            return false;
        }
    }
}

/**
 * Registry, alias
 *
 * @param string $key
 * @return mixed
 */
function registry(string $key)
{
    return Registry::get($key);
}

/**
 * Merge variables into object
 *
 * @param object $object
 * @param array $vars
 */
function object_merge_values(& $object, $vars)
{
    foreach ($vars as $k => $v) {
        if (property_exists($object, $k) && is_array($object->{$k})) {
            foreach ($v as $k2 => $v2) {
                if ($v2 === null) {
                    unset($object->{$k}[$k2]);
                } elseif (isset($object->{$k}[$k2]) && is_array($object->{$k}[$k2])) {
                    $object->{$k}[$k2] = array_merge_hard($object->{$k}[$k2], $v2);
                } else {
                    $object->{$k}[$k2] = $v2;
                }
            }
        } else {
            $object->{$k} = $v;
        }
    }
}

/**
 * Chance
 *
 * @param integer $percent
 * @return boolean
 */
function chance($percent)
{
    return (mt_rand(0, 99) < $percent);
}

if (!function_exists('mb_str_split')) {
    /**
     * Split multi-byte strings
     *
     * @param string $string
     * @param int $limit
     * @param string $pattern
     * @return array
     */
    function mb_str_split($string, $limit = -1, $pattern = null)
    {
        if (isset($pattern)) {
            return mb_split($pattern, $string, $limit);
        } else {
            $result = [];
            $counter = 0;
            $strlen = mb_strlen($string);
            while ($strlen) {
                $counter++;
                if ($limit != -1 && $counter > $limit) {
                    break;
                }
                $result[] = mb_substr($string, 0, 1, 'UTF-8');
                $string = mb_substr($string, 1, $strlen, 'UTF-8');
                $strlen = mb_strlen($string);
            }
            return $result;
        }
    }
}

if (!function_exists('mb_str_pad')) {
    /**
     * Pad multi-byte string
     *
     * @param string $input
     * @param int $length
     * @param string $string
     * @param const $type
     * @return string
     */
    function mb_str_pad($input, $length, $string = ' ', $type = STR_PAD_LEFT, $encoding = 'UTF-8')
    {
        if ($type == STR_PAD_RIGHT) {
            while (mb_strlen($input, $encoding) < $length) {
                $input .= $string;
            }
        } elseif ($type == STR_PAD_LEFT) {
            while (mb_strlen($input, $encoding) < $length) {
                $input = $string . $input;
            }
        } elseif ($type == STR_PAD_BOTH) {
            // if not an even number, the right side gets the extra padding
            $counter = 1;
            while (mb_strlen($input, $encoding) < $length) {
                if ($counter % 2) {
                    $input .= $string;
                } else {
                    $input = $string . $input;
                }
                $counter++;
            }
        }
        return $input;
    }
}

/**
 * Check if its a valid JSON string
 *
 * @param mixed $input
 * @return boolean
 */
function is_json($input)
{
    if (is_string($input) && $input !== '') {
        json_decode($input);
        return (json_last_error() == JSON_ERROR_NONE);
    } else {
        return false;
    }
}

/**
 * Check if its a HTML string
 *
 * @param string $input
 * @return bool
 */
function is_html($input)
{
    return !(strip_tags($input . '') == $input . '');
}

/**
 * Check if string is base64 encoded
 *
 * @param string $input
 * @return boolean
 */
function is_base64($input)
{
    if (base64_encode(base64_decode($input, true)) === $input) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check if string is UTF-8
 *
 * @param string $input
 * @return boolean
 */
function is_utf8($input)
{
    return (utf8_encode(utf8_decode($input)) === $input);
}

/**
 * Check if its a valid XML string
 *
 * @param mixed $input
 * @return boolean
 */
function is_xml($input)
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadXML($input);
    $errors = libxml_get_errors();
    return empty($errors);
}

/**
 * XML to array
 *
 * @param SimpleXMLElement $input
 * @return array
 */
function xml2array(SimpleXMLElement $input)
{
    $string = json_encode($input);
    return json_decode($string, true);
}

/**
 * Array to xml
 *
 * @param array $arr
 * @param SimpleXMLElement $xml
 * @return type
 */
function array2xml($arr, $xml = false)
{
    if ($xml === false) {
        $xml = new SimpleXMLElement('<root/>');
    }
    foreach ($arr as $k => $v) {
        if (is_numeric($k)) {
            $k = 'index_' . $k;
        }
        if (is_array($v)) {
            array2xml($v, $xml->addChild($k));
        } else {
            $xml->addChild($k, $v);
        }
    }
    return $xml->asXML();
}

/**
 * Hex to RGB
 *
 * @param string $hex
 * @return array
 */
function hex2rgb(string $hex): array
{
    $hex = str_replace('#', '', $hex);
    $result = [];
    if (strlen($hex) == 3) {
        $result[0] = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $result[1] = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $result[2] = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $result[0] = hexdec(substr($hex, 0, 2));
        $result[1] = hexdec(substr($hex, 2, 2));
        $result[2] = hexdec(substr($hex, 4, 2));
    }
    return $result;
}

/**
 * Trim
 *
 * @param string $str
 * @param string $what
 *   "\0" - Null Character
 *   "\t" - Tab
 *   "\n" - New line
 *   "\x0B" - Vertical Tab
 *   "\r" - New Line in Mac
 *   " " - Space
 * 	 "[str]$" Replace from end of string
 * 	 "^[str]" Replace from start of string
 * @param string $with
 * @return string
 */
function trim2($str, $what = null, $with = ' ')
{
    if ($what === null) {
        $what = "\\x00-\\x20";	//all white-spaces and control chars
    } elseif (strpos($what, '$') !== false) { // string from the end
        $what = rtrim($what, '$');
        return preg_replace('/' . preg_quote($what, '/') . '$/', $with, $str);
    } elseif (strpos($what, '^') !== false) {
        $what = ltrim($what, '^');
        return preg_replace('/^' . preg_quote($what, '/') . '/', $with, $str);
    }
    return trim(preg_replace("/[" . $what . "]+/", $with, $str), $what);
}

/**
 * nl2br
 *
 * @param string $str
 * @return string
 */
function nl2br2($str)
{
    $str = str_replace("\t", '&nbsp;&nbsp;&nbsp;', $str . '');
    return nl2br($str);
}

/**
 * Check if array has string keys
 *
 * @param array $arr
 * @return bool
 */
function array_has_string_keys(array $arr): bool
{
    return count(array_filter(array_keys($arr), 'is_string')) > 0;
}

/**
 * Array get all keys recursively
 *
 * @param array $arr
 * @param array $result
 * @param array $path
 * @param array $options
 */
function array_iterate_recursive_get_keys(array $arr, array & $result, array $path = [], array $options = [])
{
    $prefix = $options['prefix'] ?? '';
    foreach ($arr as $k => $v) {
        $path2 = $path;
        $path2[] = $k;
        if (is_scalar($v) || is_null($v) || (is_array($v) && !array_has_string_keys($v))) {
            // if we need to convert to uppercase and prefix it
            if (!empty($options['uppercase'])) {
                $path2 = $prefix . strtoupper(implode('_', $path2));
            }
            array_key_set($result, $path2, $v);
        } elseif (is_array($v)) {
            array_iterate_recursive_get_keys($v, $result, $path2, $options);
        }
    }
}

/**
 * Split on upper case
 *
 * @param string $str
 * @return array
 */
function split_on_uppercase(string $str): array
{
    return preg_split('/(?=[A-Z])/', $str, -1, PREG_SPLIT_NO_EMPTY);
}

/**
 * Opposite to nl2br
 *
 * @param string $str
 * @return string
 */
function br2nl($str, bool $oposite = false): string
{
    if ($oposite) {
        return str_replace(["\n", "\r"], ['<br />', ''], $str . '');
    } else {
        return str_replace(['<br />', '<br/>', '<br>'], "\n", $str . '');
    }
}

/**
 * Count nested level of first element
 *
 * @param array $arr
 * @param int $level
 * @return int
 */
function array_nested_levels_count(array & $arr, int $level = 1): int
{
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $level++;
            return array_nested_levels_count($v, $level);
        }
    }
    return $level;
}

/**
 * Increment version
 *
 * @param string $version
 * @return string
 */
function version_increment(string $version): string
{
    if (empty($version)) {
        return '1.0.0';
    }
    $parts = explode('.', $version);
    if ($parts[2] + 1 < 99) {
        $parts[2]++;
    } else {
        $parts[2] = 0;
        if ($parts[1] + 1 < 99) {
            $parts[1]++;
        } else {
            $parts[1] = 0;
            $parts[0]++;
        }
    }
    return implode('.', $parts);
}

/**
 * Print options array
 *
 * @param array $arr
 * @return string
 */
function print_options_array(array $arr): string
{
    $result = [];
    foreach ($arr as $k => $v) {
        $temp = $k . ' - ';
        if (is_array($v)) {
            $temp .= $v['name'];
        } else {
            $temp .= $v;
        }
        $result[] = $temp;
    }
    return implode(', ', $result);
}

/**
 * Run through an array and find by key.
 * @param array $options
 * @param mixed $value
 * @return mixed
 */
function array_walk_recursive_find_by_key($options, $value)
{
    foreach ($options ?? [] as $k => $v) {
        if ($k == $value) {
            return $v;
        }
        if (isset($v['options'])) {
            return array_walk_recursive_find_by_key($v['options'], $value);
        }
    }
    return null;
}

/**
 * Array column unique
 *
 * @param array $arr
 * @param string $column
 * @return array
 */
function array_column_unique(array $arr, string $column): array
{
    return array_unique(array_column($arr, $column));
}

/**
 * Array2 helper function
 *
 * @param array|JsonSerializable|Traversable|string $data
 * @return Array2
 */
function array2(array|JsonSerializable|Traversable|string $data = []): Array2
{
    return new Array2($data);
}

/**
 * String2 helper function
 *
 * @param mixed $data
 * @return String2
 */
function string2(mixed $data): String2
{
    return new String2($data);
}

/**
 * Object cast
 *
 * @param object $destination
 * @param object $source
 * @return bool
 */
function object_cast(& $destination, $source): bool
{
    $reflection = new ReflectionObject($source);
    $properties = $reflection->getProperties();
    foreach ($properties as $v) {
        $name = $v->getName();
        if (gettype($source->{$name}) == "object") {
            object_cast($destination->{$name}, $source->{$name});
        } else {
            $destination->{$name} = $source->{$name};
        }
    }
    return true;
}

/**
 * Class method exists
 *
 * @param mixed $class
 * @param string $method
 * @param string $type
 */
function is_class_method_exists($class, string $method, string $type = 'any'): bool
{
    try {
        $reflection = new ReflectionMethod($class, $method);
        switch ($type) {
            case 'static':
                return $reflection->isStatic();
            case 'public':
                return $reflection->isPublic();
            case 'protected':
                return $reflection->isProtected();
            case 'private':
                return $reflection->isProtected();
        }
        return method_exists($class, $method);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Is numeric key array
 *
 * @param mixed $arr
 * @return bool
 */
function is_numeric_key_array($arr): bool
{
    if (!is_array($arr)) {
        return false;
    }
    if ($arr === []) {
        return true;
    }
    return array_keys($arr) === range(0, count($arr) - 1);
}

/**
 * Array arguments
 *
 * @param array $arguments
 * @return array
 */
function array_arguments(array $arguments): array
{
    foreach ($arguments as $k => $v) {
        if (is_string($v)) {
            $arguments[$k] = [$v];
        }
    }
    if (count($arguments) == 1) {
        return $arguments[0];
    } else {
        return array_merge(...$arguments);
    }
}

/**
 * Define if not set
 *
 * @param string $name
 * @param mixed $value
 */
function define_if_not_set(string $name, $value): void
{
    if (!defined($name)) {
        define($name, $value);
    }
}

/**
 * Require if exists
 *
 * @param string $filename
 * @param bool $once
 * @return bool
 */
function require_if_exists(string $filename, bool $once = true, & $var = null): bool
{
    if (file_exists($filename)) {
        if ($once) {
            require_once($filename);
        } else {
            require($filename);
        }
        // when we injecting php variable into scope
        if (isset($var) && isset($object_override_blank_object)) {
            $var = $object_override_blank_object;
        }
        return true;
    } else {
        return false;
    }
}

/**
 * Assemble string until characters are not met
 *
 * @param string $str
 * @param array $until
 * @return string
 */
function str_assemble_until(string $str, array $until = ["\n", "\r", "\t", ' ']): string
{
    $result = '';
    foreach (str_split($str) as $v) {
        if (in_array($v, $until)) {
            break;
        }
        $result .= $v;
    }
    return $result;
}

/**
 * Print nicely
 *
 * @param mixed $arr
 * @param array $options
 * @return string
 */
function print_r_nicely($arr, array $options = []): string
{
    $options['remove_system_fields'] = $options['remove_system_fields'] ?? true;
    $options['remove_empty_fields'] = $options['remove_empty_fields'] ?? false;
    $options['width'] = $options['width'] ?? null;
    if (is_json($arr)) {
        $arr = json_decode($arr, true);
    } if (is_string($arr)) {
        return print_r2($arr, '', true, ['width' => $options['width']]);
    }
    foreach ($arr as $k => $v) {
        if ($options['remove_system_fields'] && str_starts_with($k, '__')) {
            unset($arr[$k]);
            continue;
        }
        if ($options['remove_empty_fields'] && empty($v)) {
            unset($arr[$k]);
            continue;
        }
    }
    return print_r2($arr, '', true, ['width' => $options['width']]);
}

if (!function_exists('getallheaders')) {
    /**
     * Get all headers
     */
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * Array set column
 *
 * @param array $arr
 * @param string $column
 * @param mixed $value
 */
function array_set_column(array & $arr, string $column, $value): void
{
    foreach ($arr as $k => $v) {
        $arr[$k][$column] = $value;
    }
}

/**
 * Decode a string with URL-safe Base64.
 *
 * @param string $input A Base64 encoded string
 * @return string A decoded string
 */
function base64_decode_url_safe($input)
{
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

/**
 * Encode a string with URL-safe Base64.
 *
 * @param string $input The string you want encoded
 * @return string The base64 encode of what you passed in
 */
function base64_encode_url_safe($input)
{
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
}

/**
 * UTF-8 convert
 *
 * @param string $string
 * @return string
 */
function utf8_convert(string $string): string
{
    $utf8 = mb_detect_encoding($string, ["UTF-8"], true);
    if ($utf8 !== false) {
        return $string;
    }
    $encoding = mb_detect_encoding($string, mb_detect_order(), true);
    if ($encoding === false) {
        throw new Exception("String encoding cannot be detected!");
    }
    return mb_convert_encoding($string, "UTF-8", $encoding);
}

/**
 * Remove empty values from the array
 *
 * @param array $arr
 * @return array
 */
function array_remove_empty_values(array $arr): array
{
    $result = [];
    if (empty($arr)) {
        return [];
    }
    foreach ($arr as $k => $v) {
        if ($v === null) {
            continue;
        }
        if (is_string($v) && $v === '') {
            continue;
        }
        if (is_array($v) && !empty($v)) {
            $temp = array_remove_empty_values($v);
            if (!empty($temp)) {
                $result[$k] = $temp;
            }
            continue;
        }
        $result[$k] = $v;
    }
    return $result;
}

/**
 * Array key column search
 *
 * @param array $arr
 * @param mixed $keys
 * @param mixed $values
 * @return array
 */
function array_key_column_search(array $arr, mixed $keys, mixed $values): array
{
    $result = [];
    $keys = array_key_convert_key($keys);
    if (!is_array($values)) {
        $values = [$values];
    }
    foreach ($arr as $k => $v) {
        $temp = array_key_get($v, $keys);
        if (in_array($temp, $values)) {
            $result[$k] = $v;
        }
    }
    return $result;
}

/**
 * Array key compare
 *
 * @param mixed $arr1
 * @param mixed $arr2
 * @return bool
 */
function array_key_compare(mixed $arr1, mixed $arr2): bool
{
    if (is_scalar($arr1)) {
        $arr1 = [$arr1];
    }
    if (is_scalar($arr2)) {
        $arr2 = [$arr2];
    }
    $result = array_intersect($arr1, $arr2);
    return !empty($result);
}

/**
 * Array 2 ini
 *
 * @param array $arr
 * @param string $key
 * @return array
 */
function array2ini(array $arr, string $key = ''): array
{
    $result = [];
    foreach ($arr as $k => $v) {
        $key2 = ($key ? ($key . '.') : '') . $k;
        if (is_array($v)) {
            $temp = array2ini($v, $key2);
            $result = array_merge_hard($result, $temp);
        } else {
            $result[$key2] = $v;
        }
    }
    return $result;
}

/**
 * Array flatten
 *
 * @param array $arr
 * @return array
 */
function array_flatten(array $arr): array
{
    return array_values(array2ini($arr));
}
