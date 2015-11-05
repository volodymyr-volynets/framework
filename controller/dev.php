<?php

class numbers_framework_controller_dev {

	/**
	 * A list of available topics wuld be here
	 *
	 * @var array
	 */
	public static $topics = [
		'names' => [
			'name' => 'Naming Conventions',
			'href' => '/numbers/framework/controller/dev/~names',
			'options' => [
				'code' => ['name' => 'Code', 'href' => '/numbers/framework/controller/dev/~names#code'],
				'code_test' => ['name' => 'Code Test Name', 'href' => '/numbers/framework/controller/dev/~names#code_test'],
				'db' => ['name' => 'Database', 'href' => '/numbers/framework/controller/dev/~names#db'],
				'db_test' => ['name' => 'Database Test Name', 'href' => '/numbers/framework/controller/dev/~names#db_test']
			]
		]
	];

	/**
	 * Render legend
	 *
	 * @param string $topic
	 */
	public static function render_topic($topic = null) {
		if (empty($topic)) {
			$data = self::$topics;
		} else {
			$data = [$topic => self::$topics[$topic]];
		}
		$temp = [];
		foreach ($data as $k => $v) {
			if (isset($v['options'])) {
				$value = html::a(['href' => $v['href'], 'value' => $v['name']]);
				$temp2 = [];
				foreach ($v['options'] as $k2 => $v2) {
					$temp2[] = html::a(['href' => $v2['href'], 'value' => $v2['name']]);
				}
				$value.= html::ul(['options' => $temp2]);
				$temp[] = $value;
			} else {
				$temp[] = html::a(['href' => $v2['href'], 'value' => $v2['name']]);
			}
		}
		echo html::ul(['options' => $temp]);
	}

	/**
	 * Index action
	 */
	public function action_index() {
		// rendering
		self::render_topic();
	}

	/**
	 * Names action
	 */
	public function action_names() {
		$input = request::input();

		// legend
		echo self::render_topic('names');

		// code naming conventions
		echo html::a(['name' => 'code']);
		echo '<h3>Naming Conventions: Code</h3>';
		echo object_name_code::explain(null, ['html' => true]);

		// testing form
		echo html::a(['name' => 'code_test']);
		echo '<h3>Test name</h3>';
		$input['name'] = isset($input['name']) ? $input['name'] : null;
		$input['type'] = isset($input['type']) ? $input['type'] : null;
		if (!empty($input['submit_yes'])) {
			$result = object_name_code::check($input['type'], $input['name']);
			if (!$result['success']) {
				echo html::message(['options' => $result['error'], 'type' => 'error']);
			} else {
				echo html::message(['options' => 'Name if good!', 'type' => 'success']);
			}
		}
		$ms = 'Name: ' . html::input(['name' => 'name', 'value' => $input['name']]) . ' ';
		$ms.= 'Type: ' . html::select(['name' => 'type', 'options' => object_name_code::$types, 'value' => $input['type']]) . ' ';
		$ms.= html::submit(['name' => 'submit_yes']);
		echo html::form(['name' => 'code', 'action' => '#code_test', 'value' => $ms]);

		// database naming convention
		echo '<br/><br/><hr/>';
		echo html::a(['name' => 'db']);
		echo '<h3>Naming Conventions: Database</h3>';
		echo object_name_db::explain(null, ['html' => true]);

		// testing form
		echo html::a(['name' => 'db_test']);
		echo '<h3>Test name</h3>';
		$input['name'] = isset($input['name']) ? $input['name'] : null;
		$input['type'] = isset($input['type']) ? $input['type'] : null;
		if (!empty($input['submit_yes'])) {
			$result = object_name_db::check($input['type'], $input['name']);
			if (!$result['success']) {
				echo html::message(['options' => $result['error'], 'type' => 'error']);
			} else {
				echo html::message(['options' => 'Name if good!', 'type' => 'success']);
			}
		}
		$ms = 'Name: ' . html::input(['name' => 'name', 'value' => $input['name']]) . ' ';
		$ms.= 'Type: ' . html::select(['name' => 'type', 'options' => object_name_db::$types, 'value' => $input['type']]) . ' ';
		$ms.= html::submit(['name' => 'submit_yes']);
		echo html::form(['name' => 'db', 'action' => '#db_test', 'value' => $ms]);
	}
}