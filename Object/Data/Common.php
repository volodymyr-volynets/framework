<?php

namespace Object\Data;
class Common {

	/**
	 * Attributes
	 *
	 * @var array
	 */
	public static $attributes = [
		'global' => ['domain', 'type'],
		'schema' => ['default', 'length', 'null', 'precision', 'scale', 'sequence'],
		'php' => ['php_type'],
		'misc' => ['format', 'format_options', 'align', 'validator_method', 'validator_params', 'placeholder', 'searchable', 'tree']
	];

	/**
	 * Domains
	 *
	 * @var array
	 */
	public static $domains;

	/**
	 * Types
	 *
	 * @var array
	 */
	public static $types;

	/**
	 * Process domains and types
	 *
	 * @param array $columns
	 * @param array $types
	 * @return array
	 */
	public static function processDomainsAndTypes($columns, $types = null) {	
		// cache domains and types
		if (empty(self::$domains)) {
			$object = new \Object\Data\Domains();
			// use unprocessed data - a must
			self::$domains = $object->data;
		}
		if (empty(self::$types)) {
			if (!empty($types)) {
				self::$types = $types;
			} else {
				$object = new \Object\Data\Types();
				// use unprocessed data - a must
				self::$types = $object->data;
			}
		}
		$domain_attributes = array_merge(['type'], self::$attributes['schema'], self::$attributes['php'], self::$attributes['misc']);
		$type_attributes = array_merge(self::$attributes['schema'], self::$attributes['php'], self::$attributes['misc']);
		foreach ($columns as $k => $v) {
			if (isset($v['domain'])) {
				// check if domain exists
				if (!isset(self::$domains[$v['domain']])) {
					Throw new \Exception('Domain: ' . $v['domain'] . '?');
				}
				// populate domain attributes
				foreach ($domain_attributes as $v2) {
					if (array_key_exists($v2, self::$domains[$v['domain']]) && !array_key_exists($v2, $columns[$k])) {
						$columns[$k][$v2] = self::$domains[$v['domain']][$v2];
					}
				}
			}
			// populate type attributes
			if (isset($columns[$k]['type']) && isset(self::$types[$columns[$k]['type']])) {
				foreach ($type_attributes as $v2) {
					if (array_key_exists($v2, self::$types[$columns[$k]['type']]) && !array_key_exists($v2, $columns[$k])) {
						$columns[$k][$v2] = self::$types[$columns[$k]['type']][$v2];
					}
				}
			}
			// we default to string
			if (empty($columns[$k]['php_type'])) {
				$columns[$k]['php_type'] = 'string';
			}
		}
		return $columns;
	}

	/**
	 * Cached options
	 *
	 * @var array
	 */
	public static $cached_options = [];

	/**
	 * Process options
	 *
	 * @param string $model_and_method - model::method
	 * @param object $existing_object
	 * @param array $where
	 * @param array $existing_values
	 * @param array $skip_values
	 * @param array $options
	 * @return array
	 */
	public static function processOptions($model_and_method, $existing_object = null, $where = [], $existing_values = [], $skip_values = [], $options = []) {
		// put changes into options
		$options['where'] = array_merge_hard($options['where'] ?? [], $where);
		$options['existing_values'] = $existing_values;
		$options['skip_values'] = $skip_values;
		// see if we have cached version
		$hash = sha1($model_and_method . serialize($options));
		if (isset(self::$cached_options[$hash])) {
			return self::$cached_options[$hash];
		} else {
			$temp = explode('::', $model_and_method);
			if (count($temp) == 1) {
				$model = $temp[0];
				$method = 'options';
			} else {
				$model = $temp[0];
				$method = $temp[1];
			}
			if ($model == 'this' && !empty($existing_object)) {
				$object = $existing_object;
			} else {
				$object = \Factory::model($model, true);
			}
			self::$cached_options[$hash] = $object->{$method}($options);
			return self::$cached_options[$hash];
		}
	}

	/**
	 * Options
	 *
	 * @param array $data
	 * @param array $options_map
	 * @param array $options
	 * @return array
	 */
	public static function options($data, $options_map, $options = []) {
		$i18n = [];
		$format = [];
		$options_map_new = [];
		$format_methods = [];
		foreach ($options_map as $k => $v) {
			if (is_array($v)) {
				$options_map_new[$k] = $v['field'];
				if (!empty($options['i18n']) && !empty($v['i18n']) && !array_key_exists('i18n', $v)) {
					$i18n[$k] = !empty($options['i18n']);
				}
				if (!empty($v['format'])) {
					$format[$k] = $v;
					$format_methods[$k] = \Factory::method($v['format'], 'format');
				}
			} else {
				$options_map_new[$k] = $v;
				if (!empty($options['i18n']) && $v != 'icon_class') {
					$i18n[$k] = true;
				}
			}
		}
		// we need to i18n and process formats
		if (!empty($i18n) || !empty($format)) {
			foreach ($data as $k => $v) {
				// localize
				if (!empty($i18n)) {
					foreach ($i18n as $k2 => $v2) {
						// we need to skip few things
						if (!isset($data[$k][$k2])) continue;
						$data[$k][$k2] = i18n(null, $data[$k][$k2]);
					}
				}
				// format
				if (!empty($format)) {
					foreach ($format as $k2 => $v2) {
						$data[$k][$k2] = call_user_func_array([$format_methods[$k2][0], $format_methods[$k2][1]], [$data[$k][$k2], $v2['format_options'] ?? []]);
					}
				}
			}
		}
		// inactive & icon_class
		$i18n_inactive = !empty($options['i18n']) ? i18n(null, \Object\Content\Messages::INFO_INACTIVE) : \Object\Content\Messages::INFO_INACTIVE;
		// mandatory column
		$mandatory_column = array_search('mandatory', $options_map_new);
		if (!empty($mandatory_column)) {
			$i18n_mandatory = i18n(null, \Object\Content\Messages::INFO_MANDATORY);
		}
		foreach ($data as $k => $v) {
			if (!empty($options['column_prefix']) && !empty($v[$options['column_prefix'] . 'inactive'])) {
				$options_map_new[$options['column_prefix'] . 'inactive'] = 'inactive';
				$options_map_new['__prepend'] = 'name';
				$data[$k]['__prepend'] = $i18n_inactive;
			}
			// mandatory column
			if (!empty($mandatory_column) && !empty($v[$mandatory_column])) {
				$options_map_new['__prepend2'] = 'name';
				$data[$k]['__prepend2'] = $i18n_mandatory;
			}
		}
		$data = remap($data, $options_map_new);
		// if we have photo #
		if (in_array('photo_id', $options_map_new)) {
			$generate_photo = \Object\ACL\Resources::getStatic('generate_photo');
			if (!empty($generate_photo)) {
				$photo_method_url = $generate_photo['generate_url']['method'] ?? null;
				$photo_method_icon = $generate_photo['generate_icon']['method'] ?? null;
			}
		}
		foreach ($data as $k => $v) {
			if (!empty($v['icon_class'])) {
				$data[$k]['icon_class'] = \HTML::icon(['type' => $v['icon_class'], 'class_only' => true]);
			}
			// picture/icon
			if (!empty($generate_photo)) {
				if (!empty($v['photo_id'])) {
					$data[$k]['photo_id'] = call_user_func_array(explode('::', $photo_method_url), [$v['photo_id']]);
				} else {
					$data[$k]['photo_id'] = call_user_func_array(explode('::', $photo_method_icon), [strip_tags($v['name']), 32, 32]);
				}
			}
		}
		return $data;
	}

	/**
	 * Build options based on parameters, this must be used to have
	 * consistencies in selects
	 *
	 * @param array $data
	 * @param array $options_map
	 * @param array $orderby
	 * @param array $options
	 * @return array
	 */
	public static function buildOptions($data, $options_map, $orderby, $options) {
		$data = \Object\Data\Common::options($data, $options_map, $options);
		// sorting
		if (!empty($options['i18n']) && $options['i18n'] !== 'skip_sorting') {
			array_key_sort($data, ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
		} else if (empty($orderby)) {
			array_key_sort($data, ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
		}
		return $data;
	}

	/**
	 * Filter active options
	 *
	 * @param array $data
	 * @param array $options_active
	 * @param array $existing_values
	 * @param array $skip_values
	 * @return array
	 */
	public static function filterActiveOptions($data, $options_active, $existing_values = [], $skip_values = []) {
		if (!empty($existing_values) && !is_array($existing_values)) {
			$existing_values = [$existing_values];
		}
		foreach ($data as $k => $v) {
			// existing values
			if (!empty($existing_values) && in_array($k, $existing_values)) {
				continue;
			}
			// skip values
			if (!empty($skip_values) && in_array($k, $skip_values)) {
				unset($data[$k]);
				continue;
			}
			// options active
			if (!empty($options_active)) {
				foreach ($options_active as $k2 => $v2) {
					if ($v[$k2] !== $v2) {
						unset($data[$k]);
						break;
					}
				}
			}
		}
		return $data;
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
	 * @param array $options
	 * @return array
	 */
	public static function optmultis($data, $optmultis_map, $options = []) {
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
			$level = -1; // a must
			foreach ($keys as $k2 => $v2) {
				$k2_alias = $optmultis_map[$v2]['alias'] ?? $v2;
				$k2_hash[$k2_alias] = $v[$v2];
				if (!empty($v[$v2])) {
					$level++;
					if ($k2 != 0) {
						$k2_hash2[] = 'options';
					}
					$k2_hash2[] = $v[$v2];
				}
				if ($k2 < $max_level) {
					if (!array_key_get($result, $k2_hash2)) {
						$k2_temp = [];
						$k2_temp['level'] = $level;
						$k2_temp['name'] = $models[$v2][$v[$v2]]['name'] ?? $v[$v2];
						if (!empty($options['i18n'])) {
							$k2_temp['name'] = i18n(null, $k2_temp['name']);
						}
						$k2_temp['json_key'] = json_encode($k2_hash);
						$k2_temp['disabled'] = $optmultis_map[$v2]['disabled'] ?? false;
						array_key_set($result, $k2_hash2, $k2_temp);
					}
				}
				// last key - we have items
				if ($k2 == $max_level) {
					$temp_result['level'] = $level;
					$name = '';
					if (isset($optmultis_map[$v2]['column'])) {
						$name = $v[$optmultis_map[$v2]['column']];
					} else {
						$name = $v[$k2_alias];
					}
					if (empty($options['i18n'])) {
						$temp_result['name'] = $name;
					} else {
						$temp_result['name'] = i18n(null, $name);
					}
					// icon
					$temp_result['icon_class'] = null;
					if (isset($optmultis_map[$v2]['icon_column']) && !empty($v[$optmultis_map[$v2]['icon_column']])) {
						$temp_result['icon_class'] = \HTML::icon(['type' => $v[$optmultis_map[$v2]['icon_column']], 'class_only' => true]);
					}
					// only this value flag
					if (!empty($optmultis_map[$v2]['only_this_value'])) {
						$temp_result['json_key'] = $v[$v2];
					} else {
						$temp_result['json_key'] = json_encode($k2_hash);
					}
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
				'level' => $v['level'],
				'icon_class' => $v['icon_class'] ?? null,
				'disabled' => $v['disabled'] ?? false
			];
			// level 1
			if (!empty($v['options'])) {
				array_key_sort($v['options'], ['name' => SORT_ASC]);
				foreach ($v['options'] as $v2) {
					$result2[$v2['json_key']] = [
						'name' => $v2['name'],
						'level' => $v2['level'],
						'icon_class' => $v2['icon_class'] ?? null,
						'disabled' => $v2['disabled'] ?? false
					];
					// level 2
					if (!empty($v2['options'])) {
						array_key_sort($v2['options'], ['name' => SORT_ASC]);
						foreach ($v2['options'] as $v3) {
							$result2[$v3['json_key']] = [
								'name' => $v3['name'],
								'level' => $v3['level'],
								'icon_class' => $v3['icon_class'] ?? null,
								'disabled' => $v3['disabled'] ?? false
							];
							// level 3
							if (!empty($v3['options'])) {
								array_key_sort($v3['options'], ['name' => SORT_ASC]);
								foreach ($v3['options'] as $v4) {
									$result2[$v4['json_key']] = [
										'name' => $v4['name'],
										'level' => $v4['level'],
										'icon_class' => $v4['icon_class'] ?? null,
										'disabled' => $v4['disabled'] ?? false
									];
								}
							}
						}
					}
				}
			}
		}
		return $result2;
	}
}