<?php

class numbers_framework_controller_dev {

	public $title = 'Development Portal';
	public $acl = array(
		'login' => false,
		'tokens' => array()
	);

	/**
	 * A list of available topics wuld be here
	 *
	 * @var array
	 */
	public static $topics = [
		'frontend' => [
			'name' => 'Frontend Framework',
			'href' => '/numbers/framework/controller/dev/~frontend'
		],
		'form_editor' => [
			'name' => 'Form Editor',
			'href' => '/numbers/frontend/assemblies/form/controller/editor/~edit'
		],
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
				$temp[] = html::a(['href' => $v['href'], 'value' => $v['name']]);
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
	 * Frontend action
	 */
	public function action_frontend() {
		$input = request::input();

		// legend
		echo self::render_topic('frontend');

		// processing submit
		$input['name'] = $input['name'] ?? 'numbers.frontend.html.class.base';
		$frontend_frameworks = [
			'numbers.frontend.html.class.base' => ['name' => 'Plain'],
			'numbers.frontend.html.semanticui.base' => ['name' => 'Semantic UI'],
			'numbers.frontend.html.bootstrap.base' => ['name' => 'Bootstrap']
		];
		if (!empty($input['submit_yes'])) {
			$settings = [];
			$libraries = [];
			if ($input['name'] == 'numbers.frontend.html.class.base') {
				$settings = [
					'submodule' => $input['name'],
					'options' => [
						'grid_columns' => 16
					],
					'calendar' => [
						'submodule' => 'numbers.frontend.components.calendar.numbers.base'
					]
				];
				$libraries['semanticui']['autoconnect'] = false;
				$libraries['bootstrap']['autoconnect'] = false;
			} else if ($input['name'] == 'numbers.frontend.html.semanticui.base') {
				$settings = [
					'submodule' => $input['name'],
					'options' => [
						'grid_columns' => 16
					],
					'calendar' => [
						'submodule' => 'numbers.frontend.components.calendar.numbers.base'
					]
				];
				$libraries['semanticui']['autoconnect'] = true;
				$libraries['bootstrap']['autoconnect'] = false;
			} else if ($input['name'] == 'numbers.frontend.html.bootstrap.base') {
				$settings = [
					'submodule' => $input['name'],
					'options' => [
						'grid_columns' => 12
					],
					'calendar' => [
						'submodule' => 'numbers.frontend.components.calendar.numbers.base'
					]
				];
				$libraries['semanticui']['autoconnect'] = false;
				$libraries['bootstrap']['autoconnect'] = true;
			}
			// we need to merge old and new values
			session::set('numbers.flag.global.html', array_merge_hard(session::get('numbers.flag.global.html'), $settings));
			session::set('numbers.flag.global.library', array_merge_hard(session::get('numbers.flag.global.library'), $libraries));
			header('Location: /numbers/framework/controller/dev/~frontend?name=' . $input['name']);
			exit;
		}

		// form
		$ms = 'Name: ' . html::select([
			'name' => 'name',
			'options' => $frontend_frameworks,
			'no_choose' => true,
			'value' => $input['name']
		]) . ' ';
		$ms.= html::submit(['name' => 'submit_yes']);
		echo html::form(['name' => 'db', 'action' => '#db_test', 'value' => $ms]);
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