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
	public $details = [
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
	 * Get data
	 *
	 * @param array $options
	 *		where - array of conditions
	 *		lock_rows - whether we need to lock rows
	 *		single_row - whether we need to return single row
	 * @return array
	 */
	public function get($options = []) {
		// create primary model
		$primary_class = $this->details['model'];
		$primary_model = new $primary_class();
		// grab pk from the model if not set
		$pk = $this->details['pk'] ?? $primary_model->pk;
		$this->db_object = new db($primary_model->db_link);
		// building SQL
		$sql = '';
		$sql.= !empty($options['where']) ? (' AND ' . $this->db_object->prepare_condition($options['where'])) : '';
		$sql_full = 'SELECT * FROM ' . $primary_model->name . ' WHERE 1=1' . $sql;
		// quering
		$result = $this->db_object->query($sql_full, $pk);
		if (!$result['success']) {
			Throw new Exception(implode(", ", $result['error']));
		}
		// processing details
		if (!empty($result['rows']) && !empty($this->details['details'])) {
			$this->process_details($this->details['details'], $result['rows']);
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
	private function process_details($details, & $parent_rows, $parent_keys = []) {
		foreach ($details as $k => $v) {
			$model = new $k();
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
}