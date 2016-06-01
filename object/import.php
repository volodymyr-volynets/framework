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
		// processing one by one
		foreach ($this->import_data as $k => $v) {
			$class = $v['options']['model'];
			$object = new $class();
			$counter = 0;
			foreach ($v['data'] as $k2 => $v2) {
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
				// final array
				$final = [];
				// if we have multiple
				if (!empty($v['options']['multiple'])) {
					foreach ($v2[$v['options']['multiple'][0]] as $mv0) {
						$temp = $v2;
						$temp[$v['options']['multiple'][0]] = $mv0;
						if (!empty($v['options']['multiple'][1])) {
							foreach ($v2[$v['options']['multiple'][1]] as $mv1) {
								$temp[$v['options']['multiple'][1]] = $mv1;
								// todo: add third level
							}
						}
						$final[] = $temp;
					}
				} else {
					$final[] = $v2;
				}
				// import methods
				foreach ($final as $v10) {
					switch ($v['options']['method'] ?? 'save') {
						case 'save_insert_new':
							$result_insert = $object->save($v10, ['pk' => $v['options']['pk'], 'flag_insert_only' => true, 'ignore_not_set_fields' => true]);
							break;
						case 'save':
						default:
							$result_insert = $object->save($v10, ['pk' => $v['options']['pk'], 'ignore_not_set_fields' => true]);
					}
					if (!$result_insert['success']) {
						$result['error'] = $result_insert['error'];
						return $result;
					}
					$counter++;
				}
			}
			$result['hint'][] = ' * Imported ' . $counter . ' rows into ' . $object->name . ', db link: ' .  $object->db_link;
		}
		$result['success'] = true;
		return $result;
	}
}