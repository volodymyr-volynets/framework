<?php

/**
 * Concatenate params if not empty
 * 
 * @param string $delimiter
 * @param mized $arg1
 * @return string
 */
function concat_ws($delimiter, $arg1) {
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
function concat_ws_array($delimiter, $arr) {
	$temp = [];
	foreach ($arr as $v) {
		if ($v . '' != '') {
			$temp[] = $v;
		}
	}
	return implode($delimiter, $temp);
}

/**
 * Compane and swap two variables if needed, order can be different
 * 
 * @param mixed $a
 * @param mixed $b
 * @param boolean $min
 */
function swap(& $a, & $b, $min = true) {
	if ($min) {
		if ($a > $b) list($a, $b) = array($b, $a);
	} else {
		if ($a < $b) list($a, $b) = array($b, $a);
	}
}

/**
 * Merge values into an array
 * 
 * @param array $arr
 * @param array $val
 * @return array
 */
function array_merge_values($arr, $val) {
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
function array_options_to_string($options, $option, $implode = ',') {
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
function array_fix($tokens, $explode_on = null) {
	if (is_array($tokens)) {
		return array_values($tokens);
	} else {
		if ($tokens . '' == '') {
			return [];
		} else {
			$tokens.= '';
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
function array_add_token($tokens, $token, $explode_on = null) {
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
function array_remove_token($tokens, $token) {
	$tokens = array_fix($tokens);
	foreach ($tokens as $k => $v) {
		if ($v == $token) {
			unset($tokens[$k]);
		}
	}
	return $tokens;
}

/**
 * Render variable
 * 
 * @param mixed $data
 * @return string
 */
function print_r2($data, $return = false) {
	if ($return) {
		return '<pre>' . print_r($data, true) . '</pre>';
	} else {
		echo '<pre>' . print_r($data, true) . '</pre>';
	}
}

/**
 * Render as var_export
 *
 * @param mixed $data
 * @param string $return
 * @return string
 */
function var_export2($data, $return = false) {
	if ($return) {
		return '<pre>' . var_export($data, true) . '</pre>';
	} else {
		echo '<pre>' . var_export($data, true) . '</pre>';
	}
}

/**
 * Primary key
 * 
 * @param mixed $pk
 * @param array $data
 */
function pk($pk, & $data) {
	if (!is_array($pk)) {
		$pk = [$pk];
	}
	$result = [];
	if (!is_array($data)) {
		$data = [];
	}
	foreach ($data as $k => $v) {
		array_key_set_by_key_name($result, $pk, $v);
	}
	$data = $result;
}

/**
 * Merge multiple arrays recursivly
 * 
 * @param array $arr1
 * @param array $arr2
 * @return array
 */
function array_merge2($arr1, $arr2) {
	$arrays = func_get_args();
	$merged = array();
	while ($arrays) {
		$array = array_shift($arrays);
		if (!is_array($array) || !$array) {
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
function array_merge3(& $arr1, $arr2) {
	$arr1 = call_user_func_array('array_merge2', func_get_args());
}

/**
 * Hard merging
 * 
 * @param array $arr1
 * @param array $arr2
 * @return array
 */
function array_merge_hard($arr1, $arr2) {
	$arrays = func_get_args();
	$merged = array();
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
 * Extract primary key values from an array
 * 
 * @param mixed $keys
 * @param array $data
 * @return array
 */
function extract_keys($keys, $data) {
	$keys = is_array($keys) ? $keys : [$keys];
	$result = [];
	foreach ($keys as $k) {
		$result[$k] = $data[$k] ?? null;
	}
	return $result;
}

/**
 * Process function
 * 
 * @param string $function
 * @param mixed $value
 */
function function2($function, $value) {
	$result = null;
	switch ($function) {
		case 'date':
		case 'datetime':
		case 'time':
			// todo fix formating
			$result = (strtotime($value)!==false) ? format::date_format($result['value'], $temp_func, 'system') : '';
			break;
		default:
			$result = $function($value);
	}
	return $result;
}

/**
 * Build a query string and strip empty fields
 * 
 * @param array $arr
 * @return string 
 */
function http_build_query2($arr) {
	foreach ($arr as $k => $v) {
		if (is_scalar($v) && $v . '' == '') {
			unset($arr[$k]);
		}
	}
	return http_build_query($arr);
}

/**
 * Remap keys in an array
 * 
 * @param array $data
 * @param array $map
 * @return array
 */
function remap(& $data, $map) {
	$result = [];
	foreach ($data as $k => $v) {
		foreach ($map as $k2 => $v2) {
			$k2 = str_replace('*', '', $k2);
			if (isset($result[$k][$v2])) {
				if (isset($v[$k2])) {
					if ($v[$k2] . '' != '') {
						$result[$k][$v2].= ', ' . $v[$k2];
					}
				}
			} else {
				$result[$k][$v2] = $v[$k2] ?? null;
			}
		}
	}
	return $result;
}

/**
 * Sort an array by certain keys with certain methods
 * 
 * @param array $arr
 * @param array $keys
 *		['id' => SORT_ASC, 'name' => SORT_DESC]
 *		['id' => 'asc', 'name' => 'desc']
 * @param array $methods
 *		['id' => SORT_NUMERIC, 'name' => SORT_NATURAL]
 */
function array_key_sort(& $arr, $keys, $methods = []) {
	$keys = array_key_sort_prepare_keys($keys, false);
	// prepare a single array of parameters for multisort function
	$params = [];
	foreach ($keys as $k => $v) {
		$params[$k . '_column'] = [];
		$params[$k . '_order'] = $v;
		$params[$k . '_flags'] = $methods[$k] ?? SORT_REGULAR;
		foreach ($arr as $k2 => $v2) {
			$params[$k . '_column']['_' . $k2] = $v2[$k];
		}
	}
	// calling multisort function
	call_user_func_array('array_multisort', $params);
	// create final array
	$final = [];
	foreach ($keys as $k => $v) {
		foreach ($params[$k . '_column'] as $k2 => $v2) {
			$k2 = substr($k2, 1);
			if (!isset($final[$k2])) {
				$final[$k2] = $arr[$k2];
			}
		}
	}
	$arr = $final;
}

/**
 * Prefix and suffix string to keys in array
 *
 * @param array $arr
 * @param string $prefix
 * @param string $suffix
 */
function array_key_prefix_and_suffix(& $arr, $prefix, $suffix) {
	if ($prefix . '' != '' || $suffix . '' != '') {
		foreach ($arr as $k => $v) {
			$arr[$prefix . $k . $suffix] = $v;
			unset($arr[$k]);
		}
	}
}

/**
 * Prepare sort keys
 *
 * @param array $keys
 * @param boolean $flag_string
 * @return miced
 */
function array_key_sort_prepare_keys($keys, $flag_string = false) {
	foreach ($keys as $k => $v) {
		if (strtolower($v) == 'asc' || empty($v)) {
			$keys[$k] = SORT_ASC;
		} else if (strtolower($v) == 'desc') {
			$keys[$k] = SORT_DESC;
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
 * Perform math on an array
 *
 * @param array $arr
 * @param mixed $key
 * @param string $type - add, sub, mul, div
 * @return numeric
 */
function array_key_math(& $arr, $key, $type = 'add') {
	$result = 0;
	foreach ($arr as $v) {
		if ($type == 'add') {
			$result+= $v[$key];
		} else if ($type == 'sub') {
			$result-= $v[$key];
		} else if ($type == 'mul') {
			$result*= $v[$key];
		} else if ($type == 'div') {
			$result/= $v[$key];
		}
	}
	return $result;
}

/**
 * Get value from array by keys
 * 
 * @param array $arr
 * @param mixed $keys - keys can be in this format: "1,2,3", "a", 1, array(1,2,3)
 * @param array $options
 *		unset
 * @return mixed
 */
function array_key_get(& $arr, $keys = null, $options = []) {
	if ($keys === null) {
		return $arr;
	} else {
		if (!is_array($keys)) {
			$keys = str_replace('.', ',', $keys . '');
			$keys = explode(',', $keys);
		}
		$key = $keys;
		$last = array_pop($key);
		$pointer = & $arr;
		foreach ($key as $k2) {
			if (!isset($pointer[$k2])) return null;
			$pointer = & $pointer[$k2];
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
 * Set value in the array
 * 
 * @param array $arr
 * @param mixed $keys - keys can be in this format: "1,2,3", "a", 1, array(1,2,3)
 * @param mixed $value
 * @param array $options
 *		boolean append - whether to append value to array
 */
function array_key_set(& $arr, $keys = null, $value, $options = []) {
	if (!isset($arr)) {
		$arr = [];
	}
	if ($keys === null) {
		$arr = $value;
	} else {
		// processing keys
		if (!is_array($keys)) {
			$keys = str_replace('.', ',', $keys . '');
			$keys = explode(',', $keys);
		}
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
			$pointer[] = $value;
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
function array_key_set_by_key_name(& $arr, $keys = null, $value, $options = array()) {
	// transform keys
	if (!is_array($keys)) {
		$keys = explode(',', $keys . '');
	}
	$temp = [];
	foreach ($keys as $k) {
		$temp[] = $value[$k];
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
function array_diff_assoc_recursive_by_keys($arr1, $arr2, $options = array()) {
	// if we are dealing with arrays
	if (is_array($arr1) || empty($arr1)) {
		if (is_array($arr2) || empty($arr2)) {
			if (empty($arr1)) $arr1 = array();
			if (empty($arr2)) $arr2 = array();
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
	if (isset($difference) && !is_array($difference) && floatval($difference) == 0) unset($difference);
	return !isset($difference) ? false : $difference;
}

/**
 * Convert multi-dimentional array to array of keys
 *
 * @param array $data
 * @param array $result
 * @param array $keys
 */
function array_keys_to_string($data, & $result, $keys = []) {
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
function array_compare_level1($arr1, $arr2) {
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
 * Extract values out of array by key prefix
 *
 * @param array $arr
 * @param string $key_prefix
 * @param boolean $unset
 * @return array
 */
function array_key_extract_by_prefix(& $arr, $key_prefix, $unset = true) {
	$result = [];
	foreach ($arr as $k => $v) {
		if (strpos($k, $key_prefix) === 0) {
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
 *		boolean preserve - keep only these
 */
function array_key_unset(& $arr, $keys, $options = []) {
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
 * Short alias to i18n class
 */
function i18n($i18n, $text, $options = []) {
	return i18n::get($i18n, $text, $options);
}

/**
 * Short alias to i18n class
 */
function i18n_if($text, $translate) {
	return i18n::get(null, $text);
}

/**
 * Merge vars into object
 *
 * @param object $object
 * @param array $vars
 */
function object_merge_values(& $object, $vars) {
	foreach ($vars as $k => $v) {
		if (property_exists($object, $k) && is_array($object->{$k})) {
			foreach ($v as $k2 => $v2) {
				if ($v2 === null) {
					unset($object->{$k}[$k2]);
				} else if (isset($object->{$k}[$k2]) && is_array($object->{$k}[$k2])) {
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
function chance($percent) {
	return (mt_rand(0, 99) < $percent);
}