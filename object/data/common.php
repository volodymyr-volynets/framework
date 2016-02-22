<?php

class object_data_common {

	/**
	 * Options
	 *
	 * @param array $data
	 * @param array $options_map
	 * @return array
	 */
	public static function options($data, $options_map) {
		return remap($data, $options_map);
	}

	/**
	 * Optgroups
	 *
	 * @param array $data
	 * @param array $optgroups_map
	 * @param array $options_map
	 * @return array
	 */
	public static function optgroups($data, $optgroups_map, $options_map) {
		$column = $optgroups_map['column'];
		if (!empty($optgroups_map['model'])) {
			$model = $optgroups_map['model'];
			$object = new $model();
			$model_names = $object->options();
		} else {
			$model_names = [];
		}
		$result = [];
		foreach ($data as $k => $v) {
			if (!isset($result[$v[$column]])) {
				$result[$v[$column]] = [
					'name' => $model_names[$v[$column]]['name'] ?? $v[$column],
					'options' => []
				];
			}
			$result[$v[$column]]['options'][$k] = $v;
		}
		// sorting and remapping
		foreach ($result as $k => $v) {
			$result[$k]['options'] = self::options($result[$k]['options'], $options_map);
		}
		array_key_sort($result, ['name' => SORT_ASC]);
		return $result;
	}

	/**
	 * Multi level options
	 *
	 * @param array $data
	 * @param array $optmultis_map
	 * @return array
	 */
	public static function optmultis($data, $optmultis_map) {
		$keys = array_keys($optmultis_map);
		$max_level = count($keys) - 1;
		$result = [];
		// process models
		$models = [];
		foreach ($optmultis_map as $k => $v) {
			if (!empty($v['model'])) {
				$model = $v['model'];
				$object = new $model();
				$models[$k] = $object->options();
			}
		}
		// generating all items in one run
		foreach ($data as $k => $v) {
			$temp_result = $k2_hash2 = $k2_hash = [];
			foreach ($keys as $k2 => $v2) {
				$k2_alias = $optmultis_map[$v2]['alias'] ?? $v2;
				$k2_hash[$k2_alias] = $v[$v2];
				if ($k2 != 0) {
					$k2_hash2[] = 'options';
				}
				$k2_hash2[] = $v[$v2];
				if ($k2 < $max_level) {
					if (!array_key_get($result, $k2_hash2)) {
						$k2_temp = [];
						$k2_temp['level'] = $k2;
						$k2_temp['name'] = $models[$v2][$v[$v2]]['name'] ?? $v[$v2];
						$k2_temp['json_key'] = json_encode($k2_hash);
						array_key_set($result, $k2_hash2, $k2_temp);
					}
				}
				// last key - we have items
				if ($k2 == $max_level) {
					$temp_result['level'] = $k2;
					$temp_result['name'] = $models[$v2][$v[$v2]]['name'] ?? $v[$v2];
					$temp_result['json_key'] = json_encode($k2_hash);
					array_key_set($result, $k2_hash2, $temp_result);
				}
			}
		}
		// sorting & generating final array
		array_key_sort($result, ['name' => SORT_ASC]);
		$result2 = [];
		foreach ($result as $v) {
			// level 0
			$result2[$v['json_key']] = [
				'name' => $v['name'],
				'level' => $v['level']
			];
			// level 1
			if (!empty($v['options'])) {
				array_key_sort($v['options'], ['name' => SORT_ASC]);
				foreach ($v['options'] as $v2) {
					$result2[$v2['json_key']] = [
						'name' => $v2['name'],
						'level' => $v2['level']
					];
					// level 2
					if (!empty($v2['options'])) {
						array_key_sort($v2['options'], ['name' => SORT_ASC]);
						foreach ($v2['options'] as $v3) {
							$result2[$v3['json_key']] = [
								'name' => $v3['name'],
								'level' => $v3['level']
							];
							// level 3
							// todo: add level 3 here
						}
					}
				}
			}
		}
		return $result2;
	}
}