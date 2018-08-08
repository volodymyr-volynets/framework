<?php

namespace Object;
class Collection extends \Object\Override\Data {

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
		parent::overrideHandle($this);
		// data can be passed in constructor
		if (!empty($options['data'])) {
			$this->data = $options['data'];
		}
		// primary model & pk
		$this->primary_model = \Factory::model($this->data['model']);
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
	 *		for_update - whether we need to lock rows
	 *		single_row - whether we need to return single row
	 * @return array
	 */
	public function get($options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => []
		];
		do {
			// if we have import from command line we need to intialize
			if (method_exists($this->primary_model->db_object->object, 'initialzeWhenNeeded')) {
				$this->primary_model->db_object->object->initialzeWhenNeeded(['import' => true]);
			}
			$this->primary_model->db_object->begin();
			// building query
			$query = $this->primary_model->queryBuilder([
				'initiator' => 'collection',
				'skip_acl' => true
			])->select()->columns('a.*');
			// acl datasource
			if (!empty($this->data['acl_datasource'])) {
				$acl_datasource = $this->data['acl_datasource'];
				$acl_pk = [];
				foreach (($this->data['pk'] ?? $this->primary_model->pk) as $v) {
					if ($v == $this->primary_model->tenant_column) continue;
					$acl_pk[] = ['a.' . $v, '=', 'inner_a.' . $v, true];
				}
				$acl_parameters = $this->data['acl_parameters'] ?? [];
				$query->where('AND', function (& $query) use ($acl_datasource, $acl_pk, $acl_parameters) {
					$model = new $acl_datasource();
					$query = $model->queryBuilder(['alias' => 'inner_a', 'where' => $acl_parameters])->select();
					$query->columns(1);
					foreach ($acl_pk as $v) {
						$query->where('AND', $v);
					}
				}, 'EXISTS');
			}
			// process column overrides
			$query->columnOverrides($this->primary_model->columns);
			// where
			if (!empty($options['where'])) {
				$query->whereMultiple('AND', $options['where']);
			}
			// for update
			if (!empty($options['for_update'])) {
				$query->forUpdate();
			}
			// single row
			if (!empty($options['single_row'])) {
				$query->limit(1);
			}
			$query_result = $query->query(null);
			if (!$query_result['success']) {
				$this->primary_model->db_object->rollback();
				$result['error'] = array_merge($result['error'], $query_result['error']);
				break;
			}
			// process data, convert keys
			if (!empty($query_result['rows'])) {
				$data = [];
				foreach ($query_result['rows'] as $k => $v) {
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
				$query_result['rows'] = $data;
				unset($data);
			}
			// processing details
			if (!empty($query_result['rows']) && !empty($this->data['details'])) {
				$detail_result = $this->processDetails($this->data['details'], $query_result['rows'], $options);
				if (!$detail_result['success']) {
					$result['error'] = array_merge($result['error'], $detail_result['error']);
					break;
				}
			}
			// single row
			if (!empty($options['single_row'])) {
				$result['data'] = current($query_result['rows']);
			} else {
				$result['data'] = $query_result['rows'];
			}
			// commit
			$this->primary_model->db_object->commit();
			$result['success'] = true;
		} while(0);
		return $result;
	}

	/**
	 * Get (static)
	 *
	 * @see $this::get()
	 */
	public static function getStatic(array $options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->get($options);
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
	private function getAllChildKeys($data, $maps, $parent_keys, $parent_types, & $result, & $keys, $current_key = [], $current_type = null) {
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
				$this->getAllChildKeys($v[$v2], $maps_temp, $parent_keys_temp, $parent_types_temp, $result, $keys, $new_key, $parent_types[0]);
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
	 * @return array
	 */
	private function processDetails(& $details, & $parent_rows, $options, $parent_keys = [], $parent_types = [], $parent_maps = [], $parent_settings = []) {
		$result = [
			'success' => false,
			'error' => []
		];
		foreach ($details as $k => $v) {
			// acl
			if (!empty($v['acl']) && !\Can::systemFeaturesExist($v['acl'])) continue;
			// initialize model
			$details[$k]['model_object'] = $model = \Factory::model($k);
			$pk = $v['pk'] ?? $model->pk;
			// generate keys from parent array
			$keys = [];
			$key_level = count($v['map']);
			if ($key_level == 1) {
				$k1 = key($v['map']);
				$v1 = $v['map'][$k1];
				$column = $v1;
			} else {
				$column = "concat_ws('::', " . implode(', ', $v['map']) . ")";
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
			$this->getAllChildKeys($parent_rows, $parent_maps2, $parent_keys2, $parent_types2, $result_keys, $keys);
			foreach ($result_keys as $k0 => $v0) {
				array_key_set($parent_rows, $v0, []);
			}
			// building query
			$query = new \Object\Query\Builder($model->db_link);
			$query->select()
				->columns(['a.*'])
				->from($model, 'a');
			// process column overrides
			$query->columnOverrides($model->columns);
			// where
			$query->where('AND', [$column, 'IN', $keys]);
			if (!empty($v['where'])) {
				$query->whereMultiple('AND', $v['where']);
			}
			// sql extensions
			if (!empty($v['sql']['where'])) {
				$query->whereMultiple('AND', $v['sql']['where']);
			}
			// orderby
			$orderby = $options['orderby'] ?? (!empty($model->orderby) ? $model->orderby : null);
			if (!empty($orderby)) {
				$query->orderby($orderby);
			}
			// for update
			if (!empty($options['for_update'])) {
				$query->forUpdate();
			}
			$query_result = $query->query(null);
			if (!$query_result['success']) {
				$this->primary_model->db_object->rollback();
				$result['error'] = array_merge($result['error'], $query_result['error']);
				return $result;
			}
			// if we got rows
			if (!empty($query_result['rows'])) {
				$reverse_map = array_reverse($parent_maps2, true);
				foreach ($query_result['rows'] as $k2 => $v2) {
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
						if (empty($v['__relation_pk'])) {
							foreach ($v3 as $k4 => $v4) {
								$previous[$k4] = $previous[$v4];
								$temp[] = $previous[$v4];
							}
						} else {
							foreach ($v['__relation_pk'] as $k4 => $v4) {
								$temp[] = $previous[$v4];
							}
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
					$detail_result = $this->processDetails($v['details'], $parent_rows, $options, $parent_keys2, $parent_types2, $parent_maps2, $v);
					if (!$detail_result['success']) {
						$result['error'] = array_merge($result['error'], $detail_result['error']);
						return $result;
					}
				}
			}
		}
		$result['success'] = true;
		return $result;
	}

	/**
	 * Convert collection to model
	 *
	 * @param mixed $collection
	 * @return boolean|\\Object\Collection
	 */
	public static function collectionToModel($collection) {
		if (is_string($collection)) {
			return \Factory::model($this->collection);
		} else if (!empty($collection['model'])) {
			return new \Object\Collection(['data' => $collection]);
		} else {
			return null;
		}
	}

	/**
	 * Merge data to database
	 *
	 * @param array $data
	 * @param array $options
	 *		original - original row from database, if not set it would be loaded from database
	 *		options_model - whether we need to validate provided options
	 *		flag_delete_row - if we are deleting
	 *		skip_optimistic_lock
	 * @return array
	 */
	public function merge($data, $options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'warning' => [],
			'deleted' => false,
			'inserted' => false,
			'updated' => false,
			'new_serials' => [],
			'count' => 0
		];
		do {
			if (empty($data)) {
				$result['error'][] = 'No data!';
				break;
			}
			// if we have import from command line we need to intialize
			if (method_exists($this->primary_model->db_object->object, 'initialzeWhenNeeded')) {
				$this->primary_model->db_object->object->initialzeWhenNeeded(['import' => true]);
			}
			// start transaction
			$this->primary_model->db_object->begin();
			// preset tenant
			if ($this->primary_model->tenant && !isset($data[$this->primary_model->tenant_column])) {
				$data[$this->primary_model->tenant_column] = \Tenant::id();
			}
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
					$original_result = $this->get(['where' => $pk, 'for_update' => true, 'single_row' => true]);
					if (!$original_result['success']) {
						$this->primary_model->db_object->rollback();
						$result['error'] = array_merge($result['error'], $original_result['error']);
						break;
					}
					$original = & $original_result['data'];
				}
			}
			// validate optimistic lock
			if ($this->primary_model->optimistic_lock && !empty($original) && empty($options['skip_optimistic_lock'])) {
				if (($data[$this->primary_model->optimistic_lock_column] ?? '') !== $original[$this->primary_model->optimistic_lock_column]) {
					$this->primary_model->db_object->rollback();
					$result['error'][] = \Object\Content\Messages::OPTIMISTIC_LOCK;
					break;
				}
			}
			// compare main row
			$this->timestamp = \Format::now('timestamp');
			$temp = $this->compareOneRow($data, $original, $this->data, [
				'flag_delete_row' => $options['flag_delete_row'] ?? false,
				'flag_main_record' => true,
				'skip_type_validation' => $options['skip_type_validation'] ?? false
			]);
			// if we goe an error
			if (!empty($temp['error'])) {
				$this->primary_model->db_object->rollback();
				$result['error'] = array_merge($result['error'], $temp['error']);
				break;
			}
			// we display warning if form has not been changed
			if (empty($temp['data']['total'])) {
				$result['warning'][] = \Object\Content\Messages::NO_CHANGES;
			} else { // number of changes
				$result['count'] = $temp['data']['total'];
			}
			// insert history
			if (!empty($temp['data']['history'])) {
				foreach ($temp['data']['history'] as $k => $v) {
					$temp2 = $this->primary_model->db_object->insert($k, $v);
					if (!$temp2['success']) {
						$this->primary_model->db_object->rollback();
						$result['error'] = array_merge($result['error'], $temp2['error']);
						goto error;
					}
				}
			}
			// audit
			$action = null;
			if (!empty($temp['data']['audit'])) {
				$action = $temp['data']['audit']['action'];
				// we need to put relation into pk
				if (!empty($this->primary_model->relation['field'])) {
					$temp['data']['audit']['pk'][$this->primary_model->relation['field']] = $temp['new_serials'][$this->primary_model->relation['field']] ?? $data[$this->primary_model->relation['field']] ?? $original[$this->primary_model->relation['field']];
				}
				// just in case we need to grap other pks
				foreach ($this->primary_model->pk as $v) {
					if (!isset($temp['data']['audit']['pk'][$v])) {
						$temp['data']['audit']['pk'][$v] = $temp['new_serials'][$v] ?? $data[$v] ?? $original[$v];
					}
				}
				// add form class
				$temp['data']['audit']['form_class'] = $options['form_class'] ?? null;
				// merge
				$temp2 = \Factory::model($this->primary_model->audit_model)->merge($temp['data']['audit'], ['changes' => $temp['data']['total']]);
				if (!$temp2['success']) {
					$result['error'] = array_merge($result['error'], $temp2['error']);
					break;
				}
			}
			// check for triggers only if we have changes
			if (!empty($this->primary_model->triggers) && !empty($temp['data']['total'])) {
				$data_combined = array_merge($data, $temp['new_pk']);
				foreach ($this->primary_model->triggers as $k => $v) {
					$method = \Factory::method($v, null, true);
					$trigger_result = call_user_func_array($method, [$action ?? '', $data_combined, $temp['data']['audit']]);
					if (!$trigger_result['success']) {
						$result['error'] = array_merge($result['error'], $trigger_result['error']);
						return $result;
					}
				}
			}
			// if we got here we can commit
			$this->primary_model->db_object->commit();
			$result['success'] = true;
			$result['deleted'] = $temp['data']['deleted'];
			$result['inserted'] = $temp['data']['inserted'];
			$result['updated'] = $temp['data']['updated'];
			$result['new_serials'] = $temp['new_serials'];
			$result['new_pk'] = $temp['new_pk'];
		} while(0);
		// we roll back on error
error:
		return $result;
	}

	/**
	 * Merge multiple
	 *
	 * @see \Object\Collection::merge()
	 */
	public function mergeMultiple($data, $options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'count' => 0
		];
		do {
			if (empty($data)) {
				$result['error'][] = 'No data to merge!';
				break;
			}
			// if we have import from command line we need to intialize
			if (method_exists($this->primary_model->db_object->object, 'initialzeWhenNeeded')) {
				$this->primary_model->db_object->object->initialzeWhenNeeded(['import' => true]);
			}
			// start transaction
			$this->primary_model->db_object->begin();
			// generate a list of primary keys to fetch data
			$data_pks = [];
			$data_pk_final = $this->data['pk'];
			if ($this->primary_model->tenant && empty($options['skip_tenant']) && !in_array($this->primary_model->tenant_column, $data_pk_final)) {
				array_unshift($data_pk_final, $this->primary_model->tenant_column);
			}
			foreach ($data as $k0 => $v0) {
				// injecting tenant
				if ($this->primary_model->tenant && empty($options['skip_tenant'])) {
					$data[$k0][$this->primary_model->tenant_column] = $v0[$this->primary_model->tenant_column] = \Tenant::id();
				}
				// assemble primary key
				$pk = [];
				$full_pk = true;
				foreach ($data_pk_final as $v) {
					if (isset($v0[$v])) {
						$pk[$v] = $v0[$v];
					} else {
						$full_pk = false;
					}
				}
				if (!empty($pk) && $full_pk) {
					$data_pks[$k0] = implode('::', $pk);
				}
			}
			// fetch data if we have pks
			if (!empty($data_pks)) {
				if (count($data_pk_final) == 1) {
					$column = current($data_pk_final);
				} else {
					$column = "concat_ws('::', " . implode(', ', $data_pk_final) . ")";
				}
				// fetch
				$original_result = $this->get([
					'where' => [
						$column => $data_pks
					],
					'for_update' => true
				]);
				if (!$original_result['success']) {
					$this->primary_model->db_object->rollback();
					$result['error'] = array_merge($result['error'], $original_result['error']);
					break;
				}
			} else {
				$original_result['data'] = [];
			}
			// merge one by one
			foreach ($data as $k0 => $v0) {
				$options2 = $options;
				if (isset($data_pks[$k0]) && !empty($original_result['data'][$data_pks[$k0]])) {
					$options2['original'] = $original_result['data'][$data_pks[$k0]];
				} else {
					// we must send empty array to avoid double quering
					$options2['original'] = [];
				}
				$merge_result = $this->merge($v0, $options2);
				if (!$merge_result['success']) {
					$result['error'] = array_merge($result['error'], $merge_result['error']);
					return $result;
				}
				$result['count']+= $merge_result['count'];
			}
			// commit transaction
			$this->primary_model->db_object->commit();
			$result['success'] = true;
		} while(0);
		return $result;
	}

	/**
	 * Compare single row
	 *
	 * @param array $data_row
	 * @param array $original_row
	 * @param array $collection
	 * @param array $options
	 *		flag_delete_row
	 *		flag_main_record
	 * @param array $parent_pk
	 * @param array $parent_row
	 * @return array
	 */
	final public function compareOneRow($data_row, $original_row, $collection, $options, $parent_pk = null, $parent_row = []) {
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
			'new_serials' => [],
			'new_pk' => []
		];
		$model = $collection['model_object'];
		// important to reset cache
		// todo - reset only if there's a change
		$model->resetCache();
		// process sql where
		if (!empty($collection['sql']['where']) && !empty($data_row)) {
			$data_row = array_merge_hard($data_row, $collection['sql']['where']);
		}
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
		// process colums
		$model->processColumns($data_row_final, [
			'ignore_not_set_fields' => true,
			'skip_type_validation' => $options['skip_type_validation'] ?? false
		]);
		// step 2 process row
		$delete = $update = $audit = $audit_details = $pk = [];
		$action = null;
		if (!empty($options['flag_delete_row']) || empty($data_row)) { // if we delete
			// if we have data
			if (!empty($original_row)) {
				$pk = extract_keys($collection['pk'], $original_row);
				$delete = [
					'table' => $model->full_table_name,
					'pk' => $pk
				];
				// audit
				$action = 'delete';
				$audit = $original_row;
			}
		} else if (empty($original_row)) { // if we insert
			// process who columns
			$model->processWhoColumns(['inserted', 'optimistic_lock'], $data_row_final, $this->timestamp);
			// handle serial types, empty only
			foreach ($model->columns as $k => $v) {
				if (strpos($v['type'], 'serial') !== false && empty($v['null']) && empty($data_row_final[$k])) {
					$tenant = $model->tenant ? \Tenant::id() : null;
					$module = $model->module ? $data_row_final[$model->module_column] : null;
					$result['new_serials'][$k] = $data_row_final[$k] = $model->sequence($k, 'nextval', $tenant, $module);
				}
			}
			$temp = $this->primary_model->db_object->insert($model->full_table_name, [$data_row_final], null);
			if (!$temp['success']) {
				$result['error'] = $temp['error'];
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
				// skip relation_id
				if ($k == $this->primary_model->column_prefix . 'relation_id') continue;
				// hard comparison
				if ($v !== $original_row[$k] && !(in_array($model->columns[$k]['php_type'], ['bcnumeric', 'float']) && \Math::isEqual($v, $original_row[$k]))) {
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
				// acl
				if (!empty($v['acl']) && !\Can::systemFeaturesExist($v['acl'])) continue;
				// we do not process readonly details
				if (!empty($v['readonly'])) continue;
				// create new object
				$v['model_object'] = \Factory::model($k);
				if ($v['type'] == '11') {
					$details_result = $this->compareOneRow($data_row[$k] ?? [], $original_row[$k] ?? [], $v, [
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
							$details_result = $this->compareOneRow($data_row[$k][$v2] ?? [], $original_row[$k][$v2] ?? [], $v, [
								'flag_delete_row' => !empty($delete),
								'skip_type_validation' => $options['skip_type_validation'] ?? false
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
			$model->processWhoColumns(['updated', 'optimistic_lock'], $update, $this->timestamp);
			if (!empty($update)) {
				// update record
				$temp = $this->primary_model->db_object->update($model->full_table_name, $update, [], ['where' => $pk]);
				if (!$temp['success']) {
					$result['error'] = $temp['error'];
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
			$model->processWhoColumns(['updated'], $temp, $this->timestamp);
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
		$result['new_pk'] = $pk;
		return $result;
	}
}