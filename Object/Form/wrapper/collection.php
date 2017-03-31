<?php

namespace Object\Form\Wrapper;
abstract class Collection {

	/**
	 * Collection link
	 *
	 * @var string
	 */
	public $collection_link;

	/**
	 * Current screen
	 *
	 * @var string
	 */
	public $collection_screen_link;

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Values
	 *
	 * @var array
	 */
	public $values = [];

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Processed
	 *
	 * @var array
	 */
	public $processed = [];

	/**
	 * Main screen
	 */
	const MAIN_SCREEN = '__collection_main_screen';

	/**
	 * Rows
	 */
	const ROWS = '__collection_rows';

	/**
	 * Forms
	 */
	const FORMS = '__collection_forms';

	/**
	 * Header row
	 */
	const HEADER_ROW = '__collection_header_row';

	/**
	 * Main row
	 */
	const MAIN_ROW = '__collection_main_row';

	/**
	 * Widgets row
	 */
	const WIDGETS_ROW = '__collection_widgets_row';

	/**
	 * Widgets row data
	 */
	const WIDGETS_ROW_DATA = [
		'options' => [
			'type' => 'tabs',
			'segment' => [
				'type' => 'info',
				'header' => [
					'icon' => ['type' => 'info'],
					'title' => 'Additional Information:'
				],
			],
			'its_own_segment' => true
		],
		'order' => PHP_INT_MAX - 1000,
		self::FORMS => [
			'__collection_widget_comments' => [
				'model' => null,
				'submodule' => 'flag.global.widgets.comments.submodule',
				'options' => [
					'label_name' => 'Comments',
				],
				'order' => 1
			],
			'__collection_widget_documents' => [
				'model' => null,
				'submodule' => 'flag.global.widgets.documents.submodule',
				'options' => [
					'label_name' => 'Documents',
				],
				'order' => 2
			],
			'__collection_widget_audit' => [
				'model' => null,
				'submodule' => 'flag.global.widgets.audit.submodule',
				'options' => [
					'label_name' => 'Audit',
				],
				'order' => 3
			]
		]
	];

	/**
	 * Current tab
	 *
	 * @var array
	 */
	public $current_tab = [];

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	public function __construct($options = []) {
		$this->values = $options['input'] ?? [];
		unset($options['input']);
		$this->options = $options;
		// create direct access array to form links
		$this->options['forms'] = [];
		foreach ($this->data as $screen_k => $screen_v) {
			$this->data[$screen_k]['order'] = $screen_v['order'] ?? 0;
			foreach ($screen_v[$this::ROWS] as $row_k => $row_v) {
				$this->data[$screen_k][$this::ROWS][$row_k]['order'] = $row_v['order'] ?? 0;
				foreach ($row_v[$this::FORMS] as $form_k => $form_v) {
					$this->data[$screen_k][$this::ROWS][$row_k][$this::FORMS][$form_k]['order'] = $form_v['order'] ?? 0;
					$this->options['forms'][$screen_k][$form_k] = & $this->data[$screen_k][$this::ROWS][$row_k][$this::FORMS][$form_k];
				}
			}
		}
	}

	/**
	 * Distribute
	 */
	abstract public function distribute();

	/**
	 * Render
	 */
	public function render() {
		// process form submition
		/*
		$bypass = [];
		if (!empty($this->values['__form_submitted']) && !empty($this->values['__form_link']) && !empty($this->options['links'][$this->values['__form_link']])) {
			$v2 = $this->options['links'][$this->values['__form_link']];
			$options = $v2['options'] ?? [];
			$options['input'] = $this->values;
			$class = $v2['model'];
			$model = new $class($options);
			$this->options['links'][$this->values['__form_link']]['__value'] = $model->render();
			// we need to process map
			foreach ($this->options['links'] as $k => $v) {
				if (empty($v['map'])) continue;
				foreach ($v['map'] as $k2 => $v2) {
					if ($k == $this->values['__form_link']) {
						if (array_key_exists($v2, $model->form_object->values)) {
							$this->values[$k2] = $bypass[$k2] = $model->form_object->values[$v2];
						}
					} else {
						if (array_key_exists($k2, $model->form_object->values)) {
							$this->values[$v2] = $bypass[$v2] = $model->form_object->values[$k2];
						}
					}
				}
			}
		}
		*/		
		// distribute
		$this->distribute();
		// determine current screen
		$this->collection_screen_link = $this->values['collection_screen_link'] ?? $this::main_screen;
		if (!isset($this->data[$this->collection_screen_link])) {
			// grab main row
			if (isset($this->data[$this::main_screen])) {
				$this->collection_screen_link = $this::main_screen;
			} else { // grab first screen
				$this->collection_screen_link = key($this->data);
			}
		}
		// render current screen
		$segment = null;
		$result = [];
		$index = 0;
		// sort rows in a screen
		$rows = $this->data[$this->collection_screen_link][$this::ROWS];
		array_key_sort($rows, ['order' => SORT_ASC]);
		foreach ($rows as $row_k => $row_v) {
			$forms = $row_v[$this::FORMS];
			array_key_sort($forms, ['order' => SORT_ASC]);
			// if its own segment
			if (!empty($row_v['options']['its_own_segment'])) {
				$index++;
				$result[$index]  = [
					'segment' => $row_v['options']['segment'] ?? null,
					'grid' => [],
					'html' => null
				];
			}
			if (!isset($result[$index])) {
				$result[$index]  = [
					'segment' => null,
					'grid' => [],
					'html' => null
				];
				if ($index == 0 && !empty($this->data[$this->collection_screen_link]['options']['segment'])) {
					$result[$index]['segment'] = $this->data[$this->collection_screen_link]['options']['segment'];
				}
			}
			// render forms
			if (($row_v['options']['type'] ?? 'forms') == 'forms') {
				foreach ($forms as $form_k => $form_v) {
					$class = $form_v['model'];
					$model_options = $form_v['options'];
					// we pass links to the form
					$model_options['collection_link'] = $this->collection_link;
					$model_options['collection_screen_link'] = $this->collection_screen_link;
					$model_options['form_link'] = $form_k;
					// input
					$model_options['input'] = $this->values;
					$model = \Factory::model($class, false, [$model_options]);
					// render to grid
					$result[$index]['grid']['options'][$row_k][$form_k][$form_k] = [
						'value' => $model->render(),
						'options' => $form_v['options'],
						'row_class' => $form_v['options']['row_class'] ?? null
					];
				}
			} else if ($row_v['options']['type'] == 'tabs') { // tabs
				$tab_id = "form_collection_tabs_{$this->collection_link}_{$row_k}";
				$tab_header = [];
				$tab_values = [];
				$tab_options = [];
				$have_tabs = false;
				foreach ($forms as $form_k => $form_v) {
					$this->current_tab[] = "{$tab_id}_{$form_k}";
					$labels = '';
					foreach (['records', 'danger', 'warning', 'success', 'info'] as $v78) {
						$labels.= \HTML::label2(['type' => ($v78 == 'records' ? 'primary' : $v78), 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '__' . $v78]);
					}
					$tab_header[$form_k] = i18n(null, $form_v['options']['label_name']) . $labels;
					$tab_values[$form_k] = 'test tab';
					$have_tabs = true;
					// process model
					//$class = $form_v['model'];
					// remove last element from an array
					array_pop($this->current_tab);
				}
				// if we do not have tabs
				if ($have_tabs) {
					$result[$index]['html'] = \HTML::tabs([
						'id' => $tab_id,
						'header' => $tab_header,
						'options' => $tab_values,
						'tab_options' => $tab_options
					]);
				}
			}
		}
		// todo handle separator
		//$result[] = \HTML::separator(['value' => $v2['separator']['title'], 'icon' => $v2['separator']['icon'] ?? '']);
		$html = '';
		foreach ($result as $k => $v) {
			if (!empty($v['grid'])) {
				$temp = \HTML::grid($v['grid']);
			} else {
				$temp = $v['html'] ?? '';
			}
			if (!empty($v['segment'])) {
				$v['segment']['value'] = $temp;
				$temp = \HTML::segment($v['segment']);
			}
			$html.= $temp;
		}
		return $html;
	}
}