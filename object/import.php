<?php

class object_import {

	/**
	 * Data and settings for import
	 *
	 * @var array
	 */
	public $import_data = [
		/*
		'name' => [
			'options' => [
				'pk' => ['column'],
				'model' => 'model',
				'method' => 'save' // save, save_insert_new
			],
			'data' => [
				// associative array goes here
			]
		]
		*/
	];

	/**
	 * Indicator when to use max import
	 */
	const mass_import_rows = 500;

	/**
	 * Process import object
	 *
	 * @return array
	 */
	public function process() {
		$result = [
			'success' => false,
			'error' => [],
			'hint' => []
		];
		if (empty($this->import_data)) {
			Throw new Exception('You must pecify import_data parameter.');
		}
		// if we have fixes to the data
		if (method_exists($this, 'overrides')) {
			$this->overrides();
		}
		// initialize alias & crypt objects
		$alias_object = new object_data_aliases();
		$alias_data = $alias_object->get();
		$crypt = new crypt();
		gc_enable();
		// processing one by one
		foreach (array_keys($this->import_data) as $k) {
			// we continue if we have no rows
			if (count($this->import_data[$k]['data']) == 0) {
				continue;
			}
			// object
			$object = factory::model($this->import_data[$k]['options']['model'], true);
			// a short cut to skip updating large datasets
			if (!empty($this->import_data[$k]['options']['quick_pk_comparison'])) {
				// data from an array
				$groupped = [];
				foreach ($this->import_data[$k]['data'] as $k12 => $v12) {
					$keys = [];
					foreach ($this->import_data[$k]['options']['quick_pk_comparison'] as $v13) {
						$keys[] = $v12[$v13];
					}
					$keys = implode('::', $keys);
					$temp = array_key_get($groupped, $keys);
					if (empty($temp)) {
						$temp = 0;
					}
					$temp++;
					array_key_set($groupped, $keys, $temp);
				}
				// get data from database
				$sql = "SELECT concat_ws('::', " . implode(', ', $this->import_data[$k]['options']['quick_pk_comparison']) . ") groupped, count(*) count FROM {$object->name} GROUP BY groupped";
				$db = $object->db_object();
				$result_compare = $db->query($sql);
				$groupped2 = [];
				foreach ($result_compare['rows'] as $v12) {
					$groupped2[$v12['groupped']] = $v12['count'];
				}
				// compare
				$discrepancies = false;
				foreach ($groupped as $k12 => $v12) {
					if (!isset($groupped2[$k12]) || $groupped2[$k12] != $v12) {
						$discrepancies = true;
					}
				}
				// we continue loop if there's no discrepancies
				if (!$discrepancies) {
					$result['hint'][] = ' * Skipping ' . $object->name . ', db link: ' .  $object->db_link;
					continue;
				}
			}
			// if we need mass import
			$total_rows = count($this->import_data[$k]['data']);
			if ($total_rows >= self::mass_import_rows) {
				$db = $object->db_object();
				$db->create_temp_table('temp_' . $object->name, $object->columns, $this->import_data[$k]['options']['pk'], ['skip_serials' => true]);
			}
			$counter = 0;
			$buffer = [];
			do {
				// grab first element from the array
				$v2 = array_shift($this->import_data[$k]['data']);
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
					// password
					if (strpos($v3, '~password~') === 0) {
						$v2[$k3] = $crypt->password_hash(substr($v3, 10));
					}
				}
				// if we have multiple
				if (!empty($this->import_data[$k]['options']['multiple'])) {
					foreach ($v2[$this->import_data[$k]['options']['multiple'][0]] as $mv0) {
						$temp = $v2;
						$temp[$this->import_data[$k]['options']['multiple'][0]] = $mv0;
						if (!empty($this->import_data[$k]['options']['multiple'][1])) {
							foreach ($v2[$this->import_data[$k]['options']['multiple'][1]] as $mv1) {
								$temp[$this->import_data[$k]['options']['multiple'][1]] = $mv1;
								// todo: add third level
							}
						}
						$buffer[] = $temp;
					}
				} else {
					$buffer[] = $v2;
				}
				// if buffer has 100 rows or we have no data
				if (count($buffer) > 249 || (count($buffer) > 0 && count($this->import_data[$k]['data']) == 0)) {
					if ($total_rows >= self::mass_import_rows) {
						// insert all rows
						$result_insert = $db->insert('temp_' . $object->name, $buffer);
						if (!$result_insert['success']) {
							$result['error'] = $result_insert['error'];
							return $result;
						}
						$counter+= count($buffer);
						// doing this might take some time
						echo ".";
					} else {
						// less than 1000 records
						foreach ($buffer as $v10) {
							switch ($this->import_data[$k]['options']['method'] ?? 'save') {
								case 'save_insert_new':
									$result_insert = $object->save($v10, ['pk' => $this->import_data[$k]['options']['pk'], 'flag_insert_only' => true, 'ignore_not_set_fields' => true]);
									break;
								case 'save':
								default:
									$result_insert = $object->save($v10, ['pk' => $this->import_data[$k]['options']['pk'], 'ignore_not_set_fields' => true]);
							}
							if (!$result_insert['success']) {
								$result['error'] = $result_insert['error'];
								return $result;
							}
							$counter++;
						}
					}
					$buffer = [];
					// free up memory
					gc_collect_cycles();
				}
			} while (count($this->import_data[$k]['data']) > 0);
			// we need to run few queries for mass import
			if ($total_rows >= self::mass_import_rows) {
				$type = $this->import_data[$k]['options']['method'] ?? 'save';
				$columns = [];
				$columns_mysql = [];
				$where = [];
				$where_mysql = [];
				$where_delete = [];
				$serials = false;
				foreach ($object->columns as $k12 => $v12) {
					if (in_array($k12, $this->import_data[$k]['options']['pk'])) {
						$where[] = "a.{$k12} = temp_{$object->name}.{$k12}";
						$where_mysql[] = "{$object->name}.{$k12} = temp_{$object->name}.{$k12}";
						$where_delete[] = "a.{$k12} = b.{$k12}";
					} else {
						if (strpos($v12['type'], 'serial') !== false) {
							$serials = true;
							continue;
						}
						$columns[] = "{$k12} = temp_{$object->name}.{$k12}";
						$columns_mysql[] = "{$object->name}.{$k12} = temp_{$object->name}.{$k12}";
					}
				}
				if ($type == 'save') {
					// update existing rows
					if ($db->backend == 'pgsql') {
						$sql = "UPDATE {$object->name} AS a SET " . implode(', ', $columns) . " FROM temp_{$object->name} AS temp_{$object->name} WHERE " . implode(' AND ', $where);
					} else {
						$sql = "UPDATE {$object->name}, temp_{$object->name} SET " . implode(', ', $columns_mysql) . " WHERE " . implode(' AND ', $where_mysql);
					}
					$result_insert = $db->query($sql);
					if (!$result_insert['success']) {
						$result['error'] = $result_insert['error'];
						return $result;
					}
				}
				// delete existing rows
				if ($db->backend == 'pgsql') {
					$sql = "DELETE FROM temp_{$object->name} a WHERE EXISTS (SELECT 1 FROM {$object->name} b WHERE " . implode(' AND ', $where_delete) . ")";
				} else {
					$sql = "DELETE FROM a USING temp_{$object->name} AS a INNER JOIN {$object->name} AS b WHERE " . implode(' AND ', $where_delete);
				}
				$result_insert = $db->query($sql);
				if (!$result_insert['success']) {
					$result['error'] = $result_insert['error'];
					return $result;
				}
				// we need to update serial columns
				if (!empty($serials)) {
					$columns = [];
					foreach ($object->columns as $k12 => $v12) {
						if (strpos($v12['type'], 'serial') !== false) {
							$columns[] = "{$k12} = nextval('{$object->name}_{$k12}_seq')";
						}
					}
					$sql = "UPDATE temp_{$object->name} SET " . implode(', ', $columns);
					$result_insert = $db->query($sql);
					if (!$result_insert['success']) {
						$result['error'] = $result_insert['error'];
						return $result;
					}
				}
				// insert the rest of the rows
				$sql = "INSERT INTO {$object->name} SELECT * FROM temp_{$object->name}";
				$result_insert = $db->query($sql);
				if (!$result_insert['success']) {
					$result['error'] = $result_insert['error'];
					return $result;
				}
				// drop temp table
				$sql = "DROP TABLE temp_{$object->name}";
				$result_insert = $db->query($sql);
				if (!$result_insert['success']) {
					$result['error'] = $result_insert['error'];
					return $result;
				}
			}
			$result['hint'][] = ' * Imported ' . $counter . ' rows into ' . $object->name . ', db link: ' .  $object->db_link;
			// after we are done importing we need to reset sequences
			foreach ($object->columns as $k45 => $v45) {
				if (strpos($v45['type'], 'serial') !== false) {
					$object->synchronize_sequence($k45);
				}
			}
		}
		$result['success'] = true;
		return $result;
	}
}