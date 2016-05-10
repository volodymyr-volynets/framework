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
		// processing one by one
		foreach ($this->import_data as $k => $v) {
			$class = $v['options']['model'];
			$object = new $class();
			$counter = 0;
			foreach ($v['data'] as $k2 => $v2) {
				// import methods
				switch ($v['options']['method'] ?? 'save') {
					case 'save_insert_new':
						$result_insert = $object->save($v2, ['pk' => $v['options']['pk'], 'flag_insert_only' => true]);
						break;
					case 'save':
					default:
						$result_insert = $object->save($v2, ['pk' => $v['options']['pk']]);
				}
				if (!$result_insert['success']) {
					$result['error'] = $result_insert['error'];
					return $result;
				}
				$counter++;
			}
			$result['hint'][] = ' * Imported ' . $counter . ' rows into ' . $object->name . ', db link: ' .  $object->db_link;
		}
		$result['success'] = true;
		return $result;
	}
}