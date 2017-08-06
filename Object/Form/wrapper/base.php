<?php

namespace Object\Form\Wrapper;
class Base extends \Object\Form\Parent2 {

	/**
	 * Form link
	 *
	 * @var string
	 */
	public $form_link;

	/**
	 * Form object
	 *
	 * @var object
	 */
	public $form_object;

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Containers
	 *
	 * @var array
	 */
	public $containers = [];

	/**
	 * Rows
	 *
	 * @var array
	 */
	public $rows = [];

	/**
	 * Elements
	 *
	 * @var array
	 */
	public $elements = [];

	/**
	 * Collection
	 *
	 * @var mixed
	 */
	public $collection;

	/**
	 * A list of wrapper methods
	 *
	 * @var array
	 */
	public $wrapper_methods = [];

	/**
	 * Master options
	 *
	 * @var array
	 */
	public $master_options = [];

	/**
	 * Acl
	 *
	 * @var boolean
	 */
	public $acl = true;

	/**
	 * Column prefix
	 *
	 * @var string
	 */
	public $column_prefix;

	/**
	 * Constructor
	 *
	 * @param array $options
	 *		input - form input
	 *		form - form options
	 *		segment - segment options
	 *			type
	 *			header
	 *			footer
	 */
	public function __construct($options = []) {
		// we need to handle overrrides
		parent::overrideHandle($this);
		// step 0: apply data fixes
		if (method_exists($this, 'overrides')) {
			$this->overrides($this);
		}
		// we need to merge override input
		if (!empty($this->values)) {
			$options['input'] = array_merge_hard($options['input'] ?? [], $this->values);
		}
		if (isset($options['form_link'])) {
			$this->form_link = $options['form_link'];
		}
		// step 0: create form object
		$this->form_object = new \Object\Form\Base($this->form_link, array_merge_hard($this->options, $options));
		// class
		$this->form_object->form_class = '\\' . get_called_class();
		$this->form_object->initiator_class = $options['initiator_class'] ?? 'form';
		$this->form_object->form_parent = & $this;
		// buttons model
		if (!empty($this->buttons_model)) {
			$this->form_object->buttons_model = new $this->buttons_model();
		}
		// add collection
		$this->form_object->collection = $this->collection;
		$this->form_object->preloadCollectionObject(); // must initialize it before calls to container/row/element
		$this->form_object->column_prefix = $this->column_prefix ?? $this->form_object->collection_object->primary_model->column_prefix ?? null;
		// report object
		// todo		
		//$this->form_object->report_object = new numbers_frontend_html_form_report($this->form_object);
		// title
		if (!empty($this->title)) {
			$this->form_object->title = $this->title;
		} else {
			// we generate a title based on class name
			$temp = explode('\Form\\', get_called_class());
			$temp = explode('\\', $temp[1]);
			$this->title = $this->form_object->title = ucwords(implode(' ', $temp));
		}
		// step 1: methods
		foreach (['refresh', 'validate', 'save', 'post', 'success', 'finalize', 'owners', 'overrideFieldValue', 'overrideTabs', 'processDefaultValue', 'processOptionsModels', 'processAllValues', 'listQuery'] as $v) {
			if (method_exists($this, $v)) {
				$this->form_object->wrapper_methods[$v]['main'] = [& $this, $v];
			}
		}
		// extensions can have their own verify methods
		if (!empty($this->wrapper_methods)) {
			foreach ($this->wrapper_methods as $k => $v) {
				$index = 1;
				foreach ($v as $k2 => $v2) {
					$this->form_object->wrapper_methods[$k][$index] = [new $k2, $v2];
					$index++;
				}
			}
		}
		// step 2: create all containers
		foreach ($this->containers as $k => $v) {
			if ($v === null) {
				continue;
			}
			$this->form_object->container($k, $v);
		}
		// step 3: create all rows
		foreach ($this->rows as $k => $v) {
			foreach ($v as $k2 => $v2) {
				if ($v2 === null) {
					continue;
				}
				$this->form_object->row($k, $k2, $v2);
			}
		}
		// step 4: create all elements
		foreach ($this->elements as $k => $v) {
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if ($v3 === null) {
						continue;
					}
					$this->form_object->element($k, $k2, $k3, $v3);
				}
			}
		}
		// last step: process form
		if (empty($options['skip_processing'])) {
			$this->form_object->process();
		}
	}

	/**
	 * Render form
	 *
	 * @param string $format
	 * @return string
	 */
	public function render($format = 'text/html') {
		return $this->form_object->render($format);
	}

	/**
	 * API result
	 *
	 * @return array
	 */
	public function apiResult() : array {
		return $this->form_object->apiResult();
	}
}