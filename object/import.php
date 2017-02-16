<?php

class object_import {

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [
		/*
		'name' => [
			'options' => [
				'pk' => ['column'],
				'model' => 'model',
				'method' => 'save', // save, save_insert_new
				'form' => true, // whether its a form model
			],
			'data' => [
				// associative array goes here
			]
		]
		*/
	];

	/**
	 * Process import object
	 *
	 * @return array
	 */
	public function process() {
		$result = [
			'success' => false,
			'error' => [],
			'count' => 0,
			'legend' => [],
		];
		if (empty($this->data)) {
			Throw new Exception('You must pecify "data" parameter.');
		}
		// if we have fixes to the data
		if (method_exists($this, 'overrides')) {
			$this->overrides();
		}
		// initialize alias & crypt objects
		$alias_object = new object_data_aliases();
		$alias_data = $alias_object->get();
		gc_enable();
		// processing one by one
		foreach (array_keys($this->data) as $k) {
			// we continue if we have no rows
			if (count($this->data[$k]['data']) == 0) continue;
			// object
			$model = $this->data[$k]['options']['model'];
			// we exit if primary model does not exists
			
			
			// todo: if not form
			
			
			$object = new $model();
			if (!$object->db_present()) continue;
			// collection options
			$collection_options = [];
			if (!empty($this->data[$k]['options']['pk'])) {
				$collection_options['pk'] = $this->data[$k]['options']['pk'];
			}
			// counter & buffer
			$counter = 0;
			$buffer = [];
			do {
				// grab first element from the array
				$v2 = array_shift($this->data[$k]['data']);
				// we need to process overrides
				foreach ($v2 as $k3 => $v3) {
					if (!is_string($v3)) {
						continue;
					}
					// if we need id
					if (strpos($v3, '~id~') === 0) {
						$value = substr($v3, 4);
						$alias = null;
						foreach ($alias_data as $k4 => $v4) {
							// todo: maybe need column prefix with alias
							if (strpos($k3, $k4) !== false) {
								$alias = $k4;
							}
						}
						$v2[$k3] = $alias_object->get_id_by_code($alias, substr($v3, 4));
						continue;
					}
				}
				$buffer[] = $v2;
				// if buffer has 250 rows or we have no data
				if (count($buffer) > 249 || (count($buffer) > 0 && count($this->data[$k]['data']) == 0)) {
					// merge
					$result_insert = $model::collection($collection_options)->merge_multiple($buffer, [
						'skip_optimistic_lock' => true
					]);
					if (!$result_insert['success']) {
						$result['error'] = $result_insert['error'];
						return $result;
					}
					// # of records and number of changes
					$counter+= $result_insert['count'];
					$buffer = [];
					// free up memory
					gc_collect_cycles();
				}
			} while (count($this->data[$k]['data']) > 0);
			// legend
			$result['legend'][] = '         * Process ' . $k . ' changes ' . $counter;
			$result['count']+= $counter;
		}
		if (!empty($result['count'])) {
			array_unshift($result['legend'], '       * import');
		}
		$result['success'] = true;
		return $result;
	}
}