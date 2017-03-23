<?php

namespace Helper;
class Tree {

	/**
	 * Convert array to a tree using parent field
	 *
	 * @param array $data
	 * @param string $parent_field
	 */
	public static function convertByParent($data, $parent_field) {
		$pointers = [];
		foreach ($data as $k => $v) {
			if (empty($v[$parent_field])) {
				continue;
			}
			// if parent is down the road
			if (!empty($data[$v[$parent_field]])) {
				$data[$v[$parent_field]]['options'][$k] = $data[$k];
				$pointers[$k] = & $data[$v[$parent_field]]['options'][$k];
				unset($data[$k]);
			} else {
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
	public static function findKeyInATree($data, $key, $hashes = []) {
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
	public static function convertTreeToOptionsMulti($data, $level = 0, $options = [], & $result) {
		// convert to array
		if (!empty($options['skip_keys']) && !is_array($options['skip_keys'])) {
			$options['skip_keys'] = [$options['skip_keys']];
		}
		foreach ($data as $k => $v) {
			// if we are skipping certain keys
			if (!empty($options['skip_keys']) && in_array($k, $options['skip_keys'])) {
				continue;
			}
			// assemble variable
			$value = $v;
			$value['name'] = !empty($options['i18n']) ? i18n(null, $v[$options['name_field']]) : $v[$options['name_field']];

			// todo : process inactive

			$value['level'] = $level;
			if (!empty($options['icon_field'])) {
				$value['icon_class'] = Html::icon(['type' => $v[$options['icon_field']], 'class_only' => true]);
			}
			if (!empty($options['disabled_field'])) {
				$value['disabled'] = !empty($v[$options['disabled_field']]);
			}
			$result[$k] = $value;
			// if we have options
			if (!empty($v['options'])) {
				self::convertTreeToOptionsMulti($v['options'], $level + 1, $options, $result);
			}
		}
	}
}