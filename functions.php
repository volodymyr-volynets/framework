<?php

/**
 * Concatenate if not empty
 * 
 * @param string $delimiter
 * @param mized $arg1
 * @return string
 */
function concat_ws($delimiter, $arg1) {
	$arrays = func_get_args();
	$delimiter = array_shift($arrays);
	$temp = array();
	foreach ($arrays as $v) if ($v . ''<> '') $temp[] = $v;
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
	foreach ($arr as $k=>$v) {
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
	$result = array();
	foreach ($options as $k=>$v) if (in_array($k, $option)) $result[$k] = $v['name'];
	return implode($implode, $result);
}

/**
 * Fix an array string
 * 
 * @param unknown_type $tokens
 * @return array
 */
function array_fix($tokens) {
	return is_array($tokens) ? $tokens : (!empty($tokens) ? [$tokens] : []);
}

/**
 * Add token to an array
 * 
 * @param array $tokens
 * @param string $token
 * @return array
 */
function array_add_token($tokens, $token) {
	$tokens = array_fix($tokens);
	if (!in_array($token, $tokens)) $tokens[] = $token;
	return $tokens;
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
	foreach ($tokens as $k=>$v) if ($v==$token) unset($tokens[$k]);
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
 * Natural sort
 * 
 * @param unknown_type $arr
 */
function natsort2(& $arr, $field = 'name') {
	$temp = array();
	foreach ($arr as $k=>$v) {
		$temp[$k] = $v[$field];
	}
	natsort($temp);
	$data = $arr;
	$arr = array();
	foreach ($temp as $k=>$v) {
		$arr[$k] = $data[$k];
	}
}

/**
 * Primary key
 * 
 * @param mixed $pk
 * @param array $data
 */
function pk($pk, & $data) {
	if (!is_array($pk)) $pk = array($pk);
	$result = array();
	if (!is_array($data)) $data = array();
	foreach ($data as $k=>$v) {
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
 * @return a
 */
function array_merge5($arr1, $arr2) {
	$arrays = func_get_args();
	$merged = array();
	while ($arrays) {
		$array = array_shift($arrays);
		if (!is_array($array) || !$array) {
			continue;
		}
		foreach ($array as $key => $value) {
			if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
				$merged[$key] = array_merge2($merged[$key], $value);
			} else {
				$merged[$key] = $value;
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
	$keys = is_array($keys) ? $keys : array($keys);
	// where clause
	$result = array();
	foreach ($keys as $key) {
		$result[$key] = @$data[$key];
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
 * Build a query string
 * 
 * @param array $arr
 * @return string 
 */
function http_build_query2($arr) {
	foreach ($arr as $k=>$v) {
		if (empty($v)) unset($arr[$k]);
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
	$result = array();
	foreach ($data as $k => $v) {
		foreach ($map as $k2 => $v2) {
			$k2 = str_replace('*', '', $k2);
			if (isset($result[$k][$v2])) {
				if (!empty($v[$k2])) $result[$k][$v2].= ', ' . $v[$k2];
			} else {
				$result[$k][$v2] = @$v[$k2];
			}
		}
	}
	return $result;
}

/**
 * Sort an array by column
 * 
 * @param array $arr
 * @param mixed $col
 * @param mixed $dir
 */
function array_key_sort(& $arr, $key, $dir = SORT_ASC, $function = '') {
	$sort_col = array();
	foreach ($arr as $k=>$v) {
		if ($function) $v[$key] = $function(@$v[$key]);
		$sort_col[$k] = $v[$key];
	}
	array_multisort($sort_col, $dir, $arr);
}

/**
 * Get value from array by keys
 * 
 * @param array $arr
 * @param mixed $keys - keys can be in this format: "1,2,3", "a", 1, array(1,2,3)
 * @return mixed
 */
function array_key_get(& $arr, $keys = null) {
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
		return isset($pointer[$last]) ? $pointer[$last] : null;
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
	if (!isset($arr)) $arr = array();
	if ($keys === null) {
		$arr = $value;
	} else {
		// processing keys
		if (!is_array($keys)) {
			$keys = str_replace('.', ',', $keys);
			$keys = explode(',', $keys . '');
		}
		$key = $keys;
		$pointer = & $arr;
		foreach ($key as $k2) {
			if (!isset($pointer[$k2])) $pointer[$k2] = array();
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
	if (!is_array($keys)) $keys = explode(',', $keys . '');
	// transform keys
	$temp = array();
	foreach ($keys as $k) $temp[] = $value[$k];
	// unsetting keys
	if (!empty($options['unset_keys'])) foreach ($temp as $k2) unset($value[$k2]);
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