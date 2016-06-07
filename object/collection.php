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
		'optimistic_lock_column' => '[column]',
		'details' => [
			'[model]' => [
				'pk' => [],
				'type' => '1M',
				'map' => ['[parent key]' => '[child key]']
				'details' => []
			]
		]
		*/
	];

	/**
	 * Db object
	 *
	 * @var object 
	 */
	private $db_object;

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
		if (empty($this->data['pk'])) {
			$this->data['pk'] = $this->primary_model->pk;
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
		$this->db_object = new db($this->primary_model->db_link);
		// building SQL
		$sql = '';
		$sql.= !empty($options['where']) ? (' AND ' . $this->db_object->prepare_condition($options['where'])) : '';
		$sql_full = 'SELECT * FROM ' . $this->primary_model->name . ' WHERE 1=1' . $sql;
		// quering
		$result = $this->db_object->query($sql_full, $this->data['pk']);
		if (!$result['success']) {
			Throw new Exception(implode(", ", $result['error']));
		}
		// processing details
		if (!empty($result['rows']) && !empty($this->data['details'])) {
			$this->process_details($this->data['details'], $result['rows']);
		}
		// single row
		if (!empty($options['single_row'])) {
			return current($result['rows']);
		} else {
			return $result['rows'];
		}
	}

	/**
	 * Process details
	 *
	 * @param array $details
	 * @param array $parent_rows
	 * @param array $parent_keys
	 */
	private function process_details(& $details, & $parent_rows, $parent_keys = []) {
		foreach ($details as $k => $v) {
			$model = new $k();
			$details['model_object'] = $model;
			$pk = $v['pk'] ?? $model->pk;
			// generate keys from parent array
			$keys = [];
			$key_level = count($v['map']);
			if ($key_level == 1) {
				$k1 = key($v['map']);
				$v1 = $v['map'][$k1];
				$column = $v1;
				// important to unset keys from pk array
				unset($pk[array_search($v1, $pk)]);
			} else {
				// todo: add implementation
				Throw new Exception('Level?');
			}
			foreach ($parent_rows as $k2 => $v2) {
				if ($key_level == 1) {
					$keys[] = $v2[$k1];
				}
				// create empty arrays for children
				foreach ($v['map'] as $k3 => $v3) {
					$key = $parent_keys;
					$key[] = $k2;
					$key[] = $k;
					array_key_set($parent_rows, $key, []);
				}
			}
			// building SQL
			$sql = '';
			$sql.= ' AND ' . $this->db_object->prepare_condition([$column => $keys]);
			$sql_full = 'SELECT * FROM ' . $model->name . ' WHERE 1=1' . $sql;
			// quering
			$result = $this->db_object->query($sql_full, $pk);
			if (!$result['success']) {
				Throw new Exception(implode(", ", $result['error']));
			}
			// if we got rows
			if (!empty($result['rows'])) {
				// loop though child array
				foreach ($result['rows'] as $k2 => $v2) {
					foreach ($v['map'] as $k3 => $v3) {
						$key = $parent_keys;
						$key[] = $v2[$v3];
						$key[] = $k;
						if ($v['type'] == '1M') {
							$key[] = $k2;
						}
						array_key_set($parent_rows, $key, $v2);
					}
				}
			}
			// if we have more details
			if (!empty($v['details'])) {
				Throw new Exception('Details?');
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
	 * @return array
	 */
	public function merge($data, $options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'warning' => [],
			'deleted' => false,
			'inserted' => false,
			'new_pk' => []
		];
		do {
			// start transaction
			$db = $this->data['model_object']->db_object();
			$db->begin();
			// load data from database
			$original = [];
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
			// comapare main row
			$this->timestamp = format::now('timestamp');
			$temp = $this->compare_one_row($data, $original, $this->data, [
				'flag_delete_row' => $options['flag_delete_row'] ?? false,
				'optimistic_lock' => $options['optimistic_lock'] ?? false,
				'flag_main_record' => true
			], $db);
			// if we goe an error
			if (!empty($temp['error'])) {
				$result['error'] = $temp['error'];
				break;
			}
			// we display warning if form has not been changed
			if (empty($temp['data']['total'])) {
				$result['warning'][] = 'The form has not been changed, nothing to save!';
				$db->rollback();
				break;
			}
			// insert history
			if (!empty($temp['data']['history'])) {
				foreach ($temp['data']['history'] as $k => $v) {
					$temp2 = $db->insert($k, $v);
					if (!$temp2['success']) {
						$result['error'] = $temp2['error'];
						$db->rollback();
						break;
					}
				}
			}
			// if we got here we can commit
			$result['success'] = 1;
			$result['deleted'] = $temp['data']['deleted'];
			$result['inserted'] = $temp['data']['inserted'];
			$db->commit();
		} while(0);
		//print_r2($result);
		return $result;
	}

	/**
	 * Compare single row
	 *
	 * @param array $data_row
	 * @param array $original_row
	 * @param array $collection
	 * @param array $options
	 * @return array
	 */
	final public function compare_one_row($data_row, $original_row, $collection, $options, $db) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'history' => [],
				'delete' => [],
				'total' => 0,
				'deleted' => false,
				'inserted' => false
			],
			'new_pk' => []
		];
		$model = $collection['model_object'];
		// step 1, clenup data
		$data_row_final = $data_row;
		foreach ($data_row_final as $k => $v) {
			if (empty($model->columns[$k])) {
				unset($data_row_final[$k]);
			}
		}
		// step 2 process row
		$delete = [];
		if (!empty($options['flag_delete_row'])) {
			// if we have data
			if (!empty($original_row)) {
				$pk = extract_keys($collection['pk'], $original_row);
				$delete = [
					'table' => $model->name,
					'pk' => $pk
				];
				// history
				if ($model->history) {
					$original_row[$model->column_prefix . 'updated'] = $this->timestamp;
					$result['data']['history'][$model->history_name][] = $original_row;
				}
				$result['data']['total']++;
			}
		} else if (empty($original_row)) { // compare with original
			// adding optimistic lock if not set
			if (!empty($options['optimistic_lock'])) {
				$data_row_final[$options['optimistic_lock']['column']] = $this->timestamp;
			}
			// adding inserted column
			$inserted_column = $model->column_prefix . 'inserted';
			if (!empty($model->columns[$inserted_column]) && empty($data_row_final[$inserted_column])) {
				$data_row_final[$inserted_column] = $this->timestamp;
			}
			// insert record
			// todo: handle auto increment/serial types
			$temp = $db->insert($model->name, [$data_row_final]);
			if (!$temp['success']) {
				$result['error'] = $temp['error'];
				$db->rollback();
				return $result;
			}
			$result['data']['total']++;
			// flag for main record
			if (!empty($options['flag_main_record'])) {
				$result['data']['inserted'] = true;
				// todo: set new_pk
			}
		} else {
			// compare optimistic lock
			if (!empty($options['optimistic_lock'])) {
				if ($data_row_final[$options['optimistic_lock']['column']] != $original_row[$options['optimistic_lock']['column']]) {
					$result['error'][] = 'Someone has update the record while you were editing, please refresh!';
					return $result;
				}
			}
			$diff = [];
			$pk = [];
			foreach ($data_row_final as $k => $v) {
				// hard comparison
				if ($v !== $original_row[$k]) {
					$diff[$k] = $v;
				}
				if (in_array($k, $collection['pk'])) {
					$pk[$k] = $v;
				}
			}
			// if we have changes
			if (!empty($diff)) {
				// changing optimistic lock column
				if (!empty($options['optimistic_lock'])) {
					$diff[$options['optimistic_lock']['column']] = $this->timestamp;
				}
				// update record
				$temp = $db->update($model->name, $diff, [], ['where' => $pk]);
				if (!$temp['success']) {
					$result['error'] = $temp['error'];
					$db->rollback();
					return $result;
				}
				// history
				if ($model->history) {
					$original_row[$model->column_prefix . 'updated'] = $this->timestamp;
					$result['data']['history'][$model->history_name][] = $original_row;
				}
				$result['data']['total']++;
			}
		}
		// step 3 process details

		// todo add here

		// step 4 delete record after we deleted all childrens
		if (!empty($delete)) {
			$temp = $db->delete($delete['table'], [], [], ['where' => $delete['pk']]);
			if (!$temp['success']) {
				$result['error'] = $temp['error'];
				$db->rollback();
				return $result;
			}
			// flag for main record
			if (!empty($options['flag_main_record'])) {
				$result['data']['deleted'] = true;
			}
		}
		// success
		if (!empty($result['data']['total'])) {
			$result['success'] = true;
		}
		return $result;
	}
}