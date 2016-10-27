<?php

class object_collection extends object_override_data {

	/**
	 * Relationship types
	 *
	 * @var array
	 */
	private $relationship_types = [
		'11' => ['name' => 'One to One'],
		'1M' => ['name' => 'One to Many'],
		//'MM' => ['name' => 'Many to Many']
	];

	/**
	 * Details
	 *
	 * @var array
	 */
	public $data = [
		/*
		'model' => '[model]',
		'pk' => [],
		'details' => [
			'[model]' => [
				'pk' => [],
				'type' => '1M',
				'map' => ['[parent key]' => '[child key]'],
				'details' => [],
				// widgets
				'attributes' => [boolean],
				'addresses' => [boolean]
			]
		]
		*/
	];

	/**
	 * Primary model
	 *
	 * @var object
	 */
	public $primary_model;

	/**
	 * Timestamp
	 *
	 * @var string
	 */
	public $timestamp;

	/**
	 * Constructing object
	 *
	 * @param $options
	 */
	public function __construct($options = []) {
		// we need to handle overrrides
		parent::override_handle($this);
		// data can be passed in constructor
		if (!empty($options['data'])) {
			$this->data = $options['data'];
		}
		// primary model & pk
		$this->primary_model = factory::model($this->data['model']);
		$this->data['model_object'] = & $this->primary_model;
		$this->data['serial'] = false;
		if (empty($this->data['pk'])) {
			$this->data['pk'] = $this->primary_model->pk;
		}
		// if we have serial type in pk
		foreach ($this->data['pk'] as $v) {
			if (strpos($this->primary_model->columns[$v]['type'], 'serial') !== false) {
				$this->data['serial'] = true;
			}
		}
	}

	/**
	 * Get data
	 *
	 * @param array $options
	 *		where - array of conditions
	 *		lock_rows - whether we need to lock rows
	 *		single_row - whether we need to return single row
	 * @return array
	 */
	public function get($options = []) {
		// building SQL
		$sql = '';
		$sql.= !empty($options['where']) ? (' AND ' . $this->primary_model->db_object->prepare_condition($options['where'])) : '';
		$sql_full = 'SELECT * FROM ' . $this->primary_model->name . ' a WHERE 1=1' . $sql;
		// if we need to lock rows
		if (!empty($options['for_update'])) {
			$sql_full.= ' FOR UPDATE';
		}
		// quering
		$result = $this->primary_model->db_object->query($sql_full, null);
		if (!$result['success']) {
			Throw new Exception(implode(", ", $result['error']));
		}
		// process data, convert key
		if (!empty($result['rows'])) {
			$data = [];
			foreach ($result['rows'] as $k => $v) {
				if (count($this->data['pk']) == 1) {
					$temp_pk = $v[$this->data['pk'][0]];
				} else {
					$temp_pk = [];
					foreach ($this->data['pk'] as $v2) {
						$temp_pk[] = $v[$v2];
					}
					$temp_pk = implode('::', $temp_pk);
				}
				$data[$temp_pk] = $v;
			}
			$result['rows'] = $data;
			unset($data);
		}
		// processing details
		if (!empty($result['rows']) && !empty($this->data['details'])) {
			$this->process_details($this->data['details'], $result['rows'], $options);
		}
		// clear out for update flag
		// single row
		if (!empty($options['single_row'])) {
			return current($result['rows']);
		} else {
			return $result['rows'];
		}
	}

	/**
	 * Get all child keys
	 *
	 * @param array $data
	 * @param array $maps
	 * @param array $parent_keys
	 * @param array $parent_types
	 * @param array $result
	 * @param array $keys
	 * @param array $current_key
	 * @param string $current_type
	 */
	private function get_all_child_keys($data, $maps, $parent_keys, $parent_types, & $result, & $keys, $current_key = [], $current_type = null) {
		if ($current_type == '11') {
			$data = ['__11__' => $data];
		}
		foreach ($data as $k => $v) {
			$new_key = $current_key;
			if ($current_type != '11') {
				$new_key[] = $k;
			}
			if (count($parent_keys) == 1) {
				$new_key[] = $parent_keys[0];
				// put values into result
				$result[] = $new_key;
				// generate keys
				$temp = [];
				foreach ($maps[0] as $k3 => $v3) {
					$temp[] = $v[$k3];
				}
				// we need to preserve a data type if its just one column key
				if (count($temp) == 1) {
					$keys[] = $temp[0];
				} else {
					$keys[] = implode('::', $temp);
				}
			} else {
				// remove extra level
				$parent_keys_temp = $parent_keys;
				$v2 = array_shift($parent_keys_temp);
				$parent_types_temp = $parent_types;
				array_shift($parent_types_temp);
				$maps_temp = $maps;
				array_shift($maps_temp);
				// generate key
				$new_key[] = $v2;
				$this->get_all_child_keys($v[$v2], $maps_temp, $parent_keys_temp, $parent_types_temp, $result, $keys, $new_key, $parent_types[0]);
			}
		}
	}

	/**
	 * Process details
	 *
	 * @param array $details
	 * @param array $parent_rows
	 * @param array $options
	 * @param array $parent_keys
	 * @param array $parent_types
	 * @param array $parent_settings
	 */
	private function process_details(& $details, & $parent_rows, $options, $parent_keys = [], $parent_types = [], $parent_maps = [], $parent_settings = []) {
		foreach ($details as $k => $v) {
			$details[$k]['model_object'] = $model = factory::model($k, true);
			$pk = $v['pk'] ?? $model->pk;
			// generate keys from parent array
			$keys = [];
			$key_level = count($v['map']);
			if ($key_level == 1) {
				$k1 = key($v['map']);
				$v1 = $v['map'][$k1];
				$column = $v1;
			} else {
				$column = "concat_ws('::'[comma] " . implode('[comma] ', $v['map']) . ")";
			}
			// special array for keys
			$parent_keys2 = $parent_keys;
			$parent_keys2[] = $k;
			$parent_types2 = $parent_types;
			$parent_types2[] = $v['type'];
			$parent_maps2 = $parent_maps;
			$parent_maps2[] = $v['map'];
			// create empty arrays
			$result_keys = [];
			$this->get_all_child_keys($parent_rows, $parent_maps2, $parent_keys2, $parent_types2, $result_keys, $keys);
			foreach ($result_keys as $k0 => $v0) {
				array_key_set($parent_rows, $v0, []);
			}
			// sql extensions
			$v['sql']['where'] = $v['sql']['where'] ?? null;
			// building SQL
			$sql = ' AND ' . $this->primary_model->db_object->prepare_condition([$column => $keys]);
			$sql_full = 'SELECT * FROM ' . $model->name . ' WHERE 1=1' . $sql . ($v['sql']['where'] ? (' AND ' . $v['sql']['where']) : '');
			// order by
			$orderby = $options['orderby'] ?? (!empty($model->orderby) ? $model->orderby : null);
			if (!empty($orderby)) {
				$sql_full.= ' ORDER BY ' . array_key_sort_prepare_keys($orderby, true);
			}
			// if we need to lock rows
			if (!empty($options['for_update'])) {
				$sql_full.= ' FOR UPDATE';
			}
			// quering
			$result = $this->primary_model->db_object->query($sql_full, null); // important not to set pk
			if (!$result['success']) {
				Throw new Exception(implode(", ", $result['error']));
			}
			// if we got rows
			if (!empty($result['rows'])) {
				$reverse_map = array_reverse($parent_maps2, true);
				foreach ($result['rows'] as $k2 => $v2) {
					$master_key = [];
					// entry itself
					if ($v['type'] == '1M') {
						$temp = [];
						foreach ($pk as $v0) {
							$temp[] = $v2[$v0];
						}
						$master_key[] = implode('::', $temp);
					}
					$previous = $v2;
					foreach ($reverse_map as $k3 => $v3) {
						$temp = [];
						foreach ($v3 as $k4 => $v4) {
							$previous[$k4] = $previous[$v4];
							$temp[] = $previous[$v4];
						}
						array_unshift($master_key, $parent_keys2[$k3]);
						if (($parent_types2[$k3 - 1] ?? '') != '11') {
							array_unshift($master_key, implode('::', $temp));
						}
					}
					array_key_set($parent_rows, $master_key, $v2);
				}
				// if we have more details
				if (!empty($v['details'])) {
					$this->process_details($v['details'], $parent_rows, $options, $parent_keys2, $parent_types2, $parent_maps2, $v);
				}
			}
		}
	}

	/**
	 * Convert collection to model
	 *
	 * @param mixed $collection
	 * @return boolean|\object_collection
	 */
	public static function collection_to_model($collection) {
		if (is_string($collection)) {
			return factory::model($this->collection);
		} else if (!empty($collection['model'])) {
			return new object_collection(['data' => $collection]);
		} else {
			return null;
		}
	}

	/**
	 * Merge data to database
	 *
	 * @param array $data
	 * @param array $options
	 * @param object $form
	 * @return array
	 */
	public function merge($data, $options = [], & $form = null) {
		$result = [
			'success' => false,
			'error' => [],
			'warning' => [],
			'deleted' => false,
			'inserted' => false,
			'new_serials' => [],
			'options_model' => []
		];
		do {
			// start transaction
			$this->primary_model->db_object->begin();
			// load data from database
			$original = [];
			if (array_key_exists('original', $options)) {
				$original = $options['original'];
			} else { // load data from database
				// assemble primary key
				$pk = [];
				$full_pk = true;
				foreach ($this->data['pk'] as $v) {
					if (isset($data[$v])) {
						$pk[$v] = $data[$v];
					} else {
						$full_pk = false;
					}
				}
				// load data
				if (!empty($pk) && $full_pk) {
					$original = $this->get(['where' => $pk, 'single_row' => true]);
				}
			}
			// validate optimistic lock
			if ($this->primary_model->optimistic_lock && !empty($original)) {
				if (($data[$this->primary_model->optimistic_lock_column] ?? '') !== $original[$this->primary_model->optimistic_lock_column]) {
					$result['error'][] = object_content_messages::optimistic_lock;
					break;
				}
			}
			// we need to validate options_model
			if (!empty($options['options_model'])) {
				// get existing values
				foreach ($options['options_model'] as $k => $v) {
					// current values
					$value = array_key_get($data, $v['key']);
					if ($value !== null && (is_string($value) && $value !== '')) {
						if (is_array($value)) {
							$value = array_keys($value);
						} else {
							$value = [$value];
						}
						$options['options_model'][$k]['current_values'] = $value;
					} else {
						$options['options_model'][$k]['current_values'] = null;
					}
					// we skip if we have no values
					if (empty($options['options_model'][$k]['current_values'])) {
						unset($options['options_model'][$k]);
						continue;
					}
					// existing values
					$value = array_key_get($original, $v['key']);
					if ($value !== null) {
						if (is_array($value)) {
							$value = array_keys($value);
						} else {
							$value = [$value];
						}
						$options['options_model'][$k]['existing_values'] = $value;
					} else {
						$options['options_model'][$k]['existing_values'] = null;
					}
				}
				// validate object_data
				$sql_options = [];
				foreach ($options['options_model'] as $k => $v) {
					// we skip inactive model validations
					if ($v['options_model'] == 'object_data_model_inactive') continue;
					// process models
					$temp = explode('::', $v['options_model']);
					$model = factory::model($temp[0], true);
					if (empty($temp[1])) $temp[1] = 'options';
					if ($model->initiator_class == 'object_data' || ($model->initiator_class == 'object_table' && !in_array($temp[1], ['options', 'options_active']))) {
						$temp_options = array_keys(object_data_common::process_options($v['options_model'], null, $v['options_params'], $v['existing_values']));
						// difference between arrays
						$diff = array_diff($v['current_values'], $temp_options);
						if (!empty($diff)) {
							$result['options_model'][$k] = 1;
						}
					} else if ($model->initiator_class == 'object_table' && in_array($temp[1], ['options', 'options_active'])) {
						// last element in the pk is a field
						$pk = $model->pk;
						$last = array_pop($pk);
						// handling inactive
						$options_active = [];
						if ($temp[1] == 'options_active') {
							$options_active = $model->options_active ? $model->options_active : [$model->column_prefix . 'inactive' => 0];
						}
						$sql_options[$k] = [
							'model' => $temp[0],
							'field' => $last,
							'params' => $v['options_params'],
							'values' => $v['current_values'],
							'existing_values' => $v['existing_values'],
							'options_active' => $options_active
						];
					}
				}
				// validating options
				if (!empty($sql_options)) {
					$sql_model = new object_table_validator();
					$sql_result = $sql_model->validate_options_multiple($sql_options);
					if (!empty($sql_result['discrepancies'])) {
						foreach ($sql_result['discrepancies'] as $k => $v) {
							$result['options_model'][$k] = 1;
						}
					}
				}
				// we roll back if we have errors
				if (!empty($result['options_model'])) {
					break;
				}
			}
			// comapare main row
			$this->timestamp = format::now('timestamp');
			$temp = $this->compare_one_row($data, $original, $this->data, [
				'flag_delete_row' => $options['flag_delete_row'] ?? false,
				'flag_main_record' => true
			]);
			// if we goe an error
			if (!empty($temp['error'])) {
				$result['error'] = $temp['error'];
				break;
			}
			// we display warning if form has not been changed
			if (empty($temp['data']['total'])) {
				$result['warning'][] = object_content_messages::no_changes;
				break;
			}
			// insert history
			if (!empty($temp['data']['history'])) {
				foreach ($temp['data']['history'] as $k => $v) {
					$temp2 = $this->primary_model->db_object->insert($k, $v);
					if (!$temp2['success']) {
						$result['error'] = $temp2['error'];
						goto error;
					}
				}
			}
			// audit
			if (!empty($temp['data']['audit'])) {
				$temp2 = factory::model($this->primary_model->audit_model, true)->merge($temp['data']['audit'], ['changes' => $temp['data']['total']]);
				if (!$temp2['success']) {
					$result['error'] = $temp2['error'];
					break;
				}
			}
			// if we got here we can commit
			$result['success'] = true;
			$result['deleted'] = $temp['data']['deleted'];
			$result['inserted'] = $temp['data']['inserted'];
			$result['updated'] = $temp['data']['updated'];
			$result['new_serials'] = $temp['new_serials'];
			// commit transaction
			$this->primary_model->db_object->commit();
			return $result;
		} while(0);
		// we roll back on error
error:
		$this->primary_model->db_object->rollback();
		return $result;
	}

	/**
	 * Compare single row
	 *
	 * @param array $data_row
	 * @param array $original_row
	 * @param array $collection
	 * @param array $options
	 * @param array $parent_pk
	 * @param array $parent_row
	 * @return array
	 */
	final public function compare_one_row($data_row, $original_row, $collection, $options, $parent_pk = null, $parent_row = []) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'history' => [],
				'audit' => [],
				'total' => 0,
				'updated' => false,
				'deleted' => false,
				'inserted' => false
			],
			'new_serials' => []
		];
		$model = $collection['model_object'];
		// important to reset cache
		$model->reset_cache();
		// step 1, clenup data
		$data_row_final = $data_row;
		// we need to manualy inject parents keys
		if (!empty($parent_pk)) {
			foreach ($collection['map'] as $k => $v) {
				// if we are dealing with relations
				if (strpos($k, 'relation_id') !== false) {
					$data_row_final[$v] = $parent_row[$k];
				} else {
					$data_row_final[$v] = $parent_pk[$k];
				}
			}
		}
		$model->process_columns($data_row_final, ['ignore_not_set_fields' => true, 'skip_type_validation' => true]);
		// step 2 process row
		$delete = $update = $audit = $audit_details = $pk = [];
		$action = null;
		if (!empty($options['flag_delete_row']) || empty($data_row)) { // if we delete
			// if we have data
			if (!empty($original_row)) {
				$pk = extract_keys($collection['pk'], $original_row);
				$delete = [
					'table' => $model->name,
					'pk' => $pk
				];
				// audit
				$action = 'delete';
				$audit = $original_row;
			}
		} else if (empty($original_row)) { // if we insert
			// process who columns
			$model->process_who_columns(['inserted', 'optimistic_lock'], $data_row_final, $this->timestamp);
			// handle serial types
			foreach ($model->columns as $k => $v) {
				if (strpos($v['type'], 'serial') !== false && empty($v['null'])) {
					$temp = $this->primary_model->db_object->sequence($model->name . '_' . $k . '_seq');
					$result['new_serials'][$k] = $data_row_final[$k] = $temp['rows'][0]['counter'];
				}
			}
			$temp = $this->primary_model->db_object->insert($model->name, [$data_row_final], null);
			if (!$temp['success']) {
				$result['error'] = $temp['error'];
				$this->primary_model->db_object->rollback();
				return $result;
			}
			$result['data']['total']++;
			// flag for main record
			if (!empty($options['flag_main_record'])) {
				$result['data']['inserted'] = true;
			}
			// pk
			$pk = extract_keys($collection['pk'], $data_row_final);
			// audit
			$action = 'insert';
			$audit = $data_row_final;
		} else { // if we update
			foreach ($data_row_final as $k => $v) {
				// hard comparison
				if ($v !== $original_row[$k]) {
					$update[$k] = $v;
				}
				if (in_array($k, $collection['pk'])) {
					$pk[$k] = $v;
				}
			}
			// audit
			$action = 'update';
		}
		// step 3 process details
		if (!empty($collection['details'])) {
			foreach ($collection['details'] as $k => $v) {
				// create new object
				$v['model_object'] = factory::model($k, true);
				if ($v['type'] == '11') {
					$details_result = $this->compare_one_row($data_row[$k] ?? [], $original_row[$k] ?? [], $v, [
						'flag_delete_row' => !empty($delete)
					], $pk, $data_row_final);
					if (!empty($details_result['error'])) {
						$result['error'] = $details_result['error'];
						return $result;
					} else {
						$result['data']['total']+= $details_result['data']['total'];
					}
					// audit
					if (!empty($details_result['data']['audit'])) {
						$audit_details[$k] = $details_result['data']['audit'];
					}
				} else if ($v['type'] == '1M') {
					$keys = [];
					if (isset($original_row[$k]) && is_array($original_row[$k])) {
						$keys = array_keys($original_row[$k]);
					}
					if (isset($data_row[$k]) && is_array($data_row[$k])) {
						$keys = array_merge($keys, array_keys($data_row[$k]));
					}
					$keys = array_unique($keys);
					if (!empty($keys)) {
						foreach ($keys as $v2) {
							$details_result = $this->compare_one_row($data_row[$k][$v2] ?? [], $original_row[$k][$v2] ?? [], $v, [
								'flag_delete_row' => !empty($delete)
							], $pk, $data_row_final);
							if (!empty($details_result['error'])) {
								$result['error'] = $details_result['error'];
								return $result;
							} else {
								$result['data']['total']+= $details_result['data']['total'];
							}
							// audit
							if (!empty($details_result['data']['audit'])) {
								$audit_details[$k][$v2] = $details_result['data']['audit'];
							}
						}
					}
				}
			}
		}
		// step 4 update record
		if (!empty($update) || ($action == 'update' && $result['data']['total'] > 0)) {
			// process who columns
			$model->process_who_columns(['updated', 'optimistic_lock'], $update, $this->timestamp);
			if (!empty($update)) {
				// update record
				$temp = $this->primary_model->db_object->update($model->name, $update, [], ['where' => $pk]);
				if (!$temp['success']) {
					$result['error'] = $temp['error'];
					$this->primary_model->db_object->rollback();
					return $result;
				}
				$result['data']['total']++;
			}
			// flag for main record
			if (!empty($options['flag_main_record'])) {
				$result['data']['updated'] = true;
			}
			// audit
			$audit = $update;
		}
		// step 5 delete record after we deleted all childrens
		if (!empty($delete)) {
			$temp = $this->primary_model->db_object->delete($delete['table'], [], [], ['where' => $delete['pk']]);
			if (!$temp['success']) {
				$result['error'] = $temp['error'];
				$this->primary_model->db_object->rollback();
				return $result;
			}
			$result['data']['total']++;
			// flag for main record
			if (!empty($options['flag_main_record'])) {
				$result['data']['deleted'] = true;
			}
		}
		// step 6 history only if we updated or deleted
		if ($model->history && (!empty($delete) || !empty($update))) {
			$temp = $original_row;
			$model->process_who_columns(['updated'], $temp, $this->timestamp);
			$result['data']['history'][$model->history_name][] = $temp;
		}
		// step 7 audit
		if ($this->primary_model->audit && !empty($audit)) {
			$result['data']['audit'] = [
				'action' => $action,
				'pk' => $pk,
				'columns' => []
			];
			foreach ($audit as $k => $v) {
				$old = $original_row[$k] ?? null;
				if ($v !== $old) {
					if (($model->columns[$k]['domain'] ?? '') == 'password') $v = '*** *** ***';
					$result['data']['audit']['columns'][$k] = [$v, $old];
				}
			}
			// details
			if (!empty($audit_details)) {
				$result['data']['audit']['details'] = $audit_details;
			}
		}
		// success
		if (!empty($result['data']['total'])) {
			$result['success'] = true;
		}
		return $result;
	}
}