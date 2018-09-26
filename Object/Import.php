<?php

namespace Object;
class Import {

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
			],
			'data' => [
				// associative array goes here
			]
		]
		*/
	];

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * ALias object
	 *
	 * @var object
	 */
	private $alias_object;

	/**
	 * Alias data
	 *
	 * @var array
	 */
	private $alias_data;

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	public function __construct(array $options = []) {
		$this->options = $options;
		$this->alias_object = new \Object\Data\Aliases();
		$this->alias_data = $this->alias_object->get();
	}

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
			Throw new Exception('You must specify "data" parameter.');
		}
		// if we have fixes to the data
		if (method_exists($this, 'overrides')) {
			$this->overrides();
		}
		// enable gc
		gc_enable();
		// processing one by one
		foreach (array_keys($this->data) as $k) {
			// we continue if we have no rows
			if (count($this->data[$k]['data']) == 0) continue;
			// object
			$model = $this->data[$k]['options']['model'];
			// we exit if primary model does not exists
			$object = new $model();
			$db_object = null;
			$collection_object = null;
			$flag_collection = false;
			// regular model
			$primary_model_name = null;
			$primary_model_object = null;
			if (is_a($object, '\Object\Table')) {
				if (!$object->dbPresent()) continue;
				$db_object = $object->db_object;
				// collection options
				$collection_options = [];
				if (!empty($this->data[$k]['options']['pk'])) {
					$collection_options['pk'] = $this->data[$k]['options']['pk'];
				}
				$collection_object = $model::collectionStatic($collection_options);
			} else if (is_a($object, '\Object\Collection')) { // collections
				if (!$object->primary_model->dbPresent()) continue;
				$db_object = $object->primary_model->db_object;
				$collection_object = $object;
				$flag_collection = true;
			}
			// start transaction
			if (!empty($db_object)) {
				$db_object->begin();
			}
			// counter & buffer
			$counter = 0;
			$buffer = [];
			do {
				// grab first element from the array
				$v2 = array_shift($this->data[$k]['data']);
				// we need to process overrides
				foreach ($v2 as $k3 => $v3) {
					// if we have a detail
					if (is_array($v3)) {
						foreach ($v3 as $k4 => $v4) {
							foreach ($v4 as $k5 => $v5) {
								if (!is_string($v5)) continue;
								if (is_numeric($k5)) continue;
								// if we need id
								if (strpos($v5, '::id::') === 0) {
									$temp = $this->findAliasedValue($k5, $v5);
									if ($temp !== false) $v2[$k3][$k4][$k5] = $temp;
								}
							}
						}
					} else if (is_string($v3)) {
						// primary model
						if (strpos($v3, '::primary_model::') === 0) {
							$primary_model_name = str_replace('::primary_model::', '', $v3);
							$primary_model_object = new $primary_model_name();
							$v3 = '::id::' . $primary_model_name;
						}
						// if we need id
						if (strpos($v3, '::id::') === 0) {
							$temp = $this->findAliasedValue($k3, $v3);
							if ($temp !== false) $v2[$k3] = $temp;
							continue;
						}
						// from columns
						if (strpos($v3, '::from::') === 0) {
							$v3 = explode('::', str_replace('::from::', '', $v3));
							$first_type = trim(array_shift($v3));
							$temp = $primary_model_object->{$first_type};
							end($v3);
							$last_type = $v3[key($v3)];
							$v3 = array_key_get($temp, $v3);
							// some overrides
							if ($first_type == 'columns') {
								if ($last_type == 'domain') {
									$v3 = \Object\Data\Domains::getNonSequenceDomain($v3);
								}
								if ($last_type == 'type') {
									$v3 = \Object\Data\Types::getNonSequenceType($v3);
								}
							}
							$v2[$k3] = $v3;
							continue;
						}
					}
				}
				// we need to fix primary keys for details
				if ($flag_collection && !empty($collection_object->data['details'])) {
					foreach ($collection_object->data['details'] as $k25 => $v25) {
						if (empty($v2[$k25])) continue;
						if ($v25['type'] != '1M') continue;
						$temp_key = [];
						foreach ($v25['map'] as $k26 => $v26) {
							$temp_key[$v26] = $v2[$k26];
						}
						foreach ($v2[$k25] as $k27 => $v27) {
							$temp_key2 = [];
							foreach ($v25['pk'] as $v26) {
								$temp_key2[] = $temp_key[$v26] ?? $v27[$v26] ?? null;
							}
							$temp_key2 = implode('::', $temp_key2);
							unset($v2[$k25][$k27]);
							$v2[$k25][$temp_key2] = $v27;
						}
					}
				}
				// add value to buffer
				$buffer[] = $v2;
				// if buffer has 250 rows or we have no data
				if (count($buffer) > 249 || (count($buffer) > 0 && count($this->data[$k]['data']) == 0)) {
					// merge
					$result_insert = $collection_object->mergeMultiple($buffer, [
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
			// commit transaction
			if (!empty($db_object)) {
				$db_object->commit();
			}
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

	/**
	 * Find aliased value
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return mixed
	 */
	private function findAliasedValue(string $column, $value) {
		$alias = null;
		foreach ($this->alias_data as $k => $v) {
			// todo: maybe need column prefix with alias
			if (strpos($column, $k) !== false) {
				$alias = $k;
			}
		}
		if (!empty($alias)) {
			return $this->alias_object->getIdByCode($alias, str_replace('::id::', '', $value));
		} else {
			return false;
		}
	}
}