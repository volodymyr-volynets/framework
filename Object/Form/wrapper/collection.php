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
	const MAIN_SCREEN = 'main';

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
		// determine current screen
		$this->collection_screen_link = $this->values['__collection_screen_link'] ?? self::MAIN_SCREEN;
		if (!isset($this->data[$this->collection_screen_link])) {
			// grab main row
			if (isset($this->data[self::MAIN_SCREEN])) {
				$this->collection_screen_link = self::MAIN_SCREEN;
			} else { // grab first screen
				$this->collection_screen_link = key($this->data);
			}
		}
		// render submitted form
		$submitted_form_cached = [];
		$submitted_bypass_values = [];
		if (!empty($this->values['__form_link'])) {
			if (isset($this->options['forms'][$this->collection_screen_link][$this->values['__form_link']])) {
				$model_options = $this->options['forms'][$this->collection_screen_link][$this->values['__form_link']]['options'];
				// we pass links to the form
				$model_options['collection_link'] = $this->collection_link;
				$model_options['collection_screen_link'] = $this->collection_screen_link;
				$model_options['form_link'] = $this->values['__form_link'];
				// input
				$model_options['input'] = $this->values;
				$model = \Factory::model($this->options['forms'][$this->collection_screen_link][$this->values['__form_link']]['model'], false, [$model_options]);
				$submitted_form_cached[$this->values['__form_link']] = $model->render();
				// bypass values
				if (isset($this->options['forms'][$this->collection_screen_link][$this->values['__form_link']]['bypass_values'])) {
					foreach ($this->options['forms'][$this->collection_screen_link][$this->values['__form_link']]['bypass_values'] as $v0) {
						if (isset($model->form_object->values[$v0])) {
							$submitted_bypass_values[$v0] = $model->form_object->values[$v0];
						}
					}
				}
				// bypass input
				if (isset($this->options['forms'][$this->collection_screen_link][$this->values['__form_link']]['bypass_input'])) {
					foreach ($this->options['forms'][$this->collection_screen_link][$this->values['__form_link']]['bypass_input'] as $v0) {
						if (isset($model->form_object->options['input'][$v0])) {
							$submitted_bypass_values[$v0] = $model->form_object->options['input'][$v0];
						}
					}
				}
				$this->values = $submitted_bypass_values;
			} else {
				$submitted_bypass_values = $this->values;
			}
		} else {
			$submitted_bypass_values = $this->values;
		}
		// distribute
		$this->distribute();
		// render current screen
		$segment = null;
		$result = [];
		$index = 0;
		// sort rows in a screen
		$rows = $this->data[$this->collection_screen_link][self::ROWS];
		array_key_sort($rows, ['order' => SORT_ASC]);
		foreach ($rows as $row_k => $row_v) {
			$forms = $row_v[self::FORMS];
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
					if (isset($submitted_form_cached[$form_k])) {
						// render to grid
						$result[$index]['grid']['options'][$row_k][$form_k][$form_k] = [
							'value' => $submitted_form_cached[$form_k],
							'options' => $form_v['options'],
							'row_class' => $form_v['options']['row_class'] ?? null
						];
					} else {
						$model_options = $form_v['options'];
						// we pass links to the form
						$model_options['collection_link'] = $this->collection_link;
						$model_options['collection_screen_link'] = $this->collection_screen_link;
						$model_options['form_link'] = $form_k;
						// input
						$model_options['input'] = $submitted_bypass_values;
						$model = \Factory::model($form_v['model'], false, [$model_options]);
						// render to grid
						$result[$index]['grid']['options'][$row_k][$form_k][$form_k] = [
							'value' => $model->render(),
							'options' => $form_v['options'],
							'row_class' => $form_v['options']['row_class'] ?? null
						];
						//$model->form_object->pk
					}
				}
			} else if ($row_v['options']['type'] == 'tabs') { // tabs
				$tab_id = "form_collection_tabs_{$this->collection_link}_{$row_k}";
				$tab_header = [];
				$tab_values = [];
				$tab_options = [];
				$have_tabs = false;
				foreach ($forms as $form_k => $form_v) {
					if ((!empty($form_v['submodule']) && !\Can::submoduleExists($form_v['submodule'])) || empty($form_v['model'])) continue;
					$this->current_tab[] = "{$tab_id}_{$form_k}";
					$labels = '';
					foreach (['records', 'danger', 'warning', 'success', 'info'] as $v78) {
						$labels.= \HTML::label2(['type' => ($v78 == 'records' ? 'primary' : $v78), 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '__' . $v78]);
					}
					$tab_header[$form_k] = i18n(null, $form_v['options']['label_name']) . $labels;
					// render form
					if (isset($submitted_form_cached[$form_k])) {
						$tab_values[$form_k] = $submitted_form_cached[$form_k];
						$have_tabs = true;
					} else {
						$model_options = $form_v['options'];
						// we pass links to the form
						$model_options['collection_link'] = $this->collection_link;
						$model_options['collection_screen_link'] = $this->collection_screen_link;
						$model_options['form_link'] = $form_k;
						// input
						$model_options['input'] = $submitted_bypass_values;
						$model = \Factory::model($form_v['model'], false, [$model_options]);
						$tab_values[$form_k] = $model->render();
						$have_tabs = true;
					}
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