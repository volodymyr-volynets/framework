<?php

namespace Object\Form\Wrapper;
class Import {

	/**
	 * Form object
	 *
	 * @var object
	 */
	public $form_object;

	/**
	 * Constructor
	 *
	 * @param array $options
	 *		input
	 *		model
	 */
	public function __construct($options = []) {
		$class = $options['input']['model'] = $options['model'];
		$form = new $class([
			'input' => [
				\Object\Form\Parent2::BUTTON_SUBMIT_BLANK => true
			],
			'skip_optimistic_lock' => true
		]);
		// if we need to export a sample
		if (!empty($options['input']['export_file_with_format'])) {
			$variables = $form->form_object->prepareExportFileVariables();
			$export_model = new \Numbers\Backend\IO\Common\Base();
			// filename
			$formats = \Object\Content\ImportFormats::getStatic();
			$export_model->export($options['input']['export_file_with_format'], $variables['data'], ['output_file_name' => 'Import.' . $formats[$options['input']['export_file_with_format']]['extension']]);
		}
		$options['segment'] = \Object\Form\Parent2::SEGMENT_IMPORT;
		$options['actions'] = [
			'refresh' => true,
			'back' => true,
			'new' => true
		];
		// step 0: create form object
		$this->form_object = new \Object\Form\Base('simple_import_form', $options);
		// class
		$this->form_object->form_class = '\\' . get_called_class();
		$this->form_object->form_parent = & $this;
		$this->form_object->import_object = & $form->form_object;
		$this->form_object->initiator_class = 'import';
		// add collection
		$this->form_object->collection = $form->form_object->collection;
		$this->form_object->preloadCollectionObject(); // must initialize it before calls to container/row/element
		$this->form_object->column_prefix = $form->form_object->column_prefix;
		// title
		$this->form_object->title = 'Import: ' . $form->form_object->title;
		// step 1: methods
		$this->form_object->wrapper_methods['validate']['main'] = [& $this, 'validate'];
		$this->form_object->wrapper_methods['save']['main'] = [& $this, 'save'];
		// step 2: add container & elements
		$this->form_object->container('files', [
			'default_row_type' => 'grid',
			'order' => 900,
			'custom_renderer' => '\Object\Form\Wrapper\Import::exportFilesSamples'
		]);
		$this->form_object->container('top', [
			'default_row_type' => 'grid',
			'order' => 1000
		]);
		$this->form_object->element('top', 'row', 'import_file', [
			'label_name' => 'File',
			'type' => 'text',
			'required' => 'c',
			'method' => 'file',
		]);
		$this->form_object->container(\Object\Form\Parent2::BUTTONS, [
			'default_row_type' => 'grid',
			'order' => PHP_INT_MAX
		]);
		$this->form_object->element(\Object\Form\Parent2::BUTTONS, 'row', \Object\Form\Parent2::BUTTON_SUBMIT, \Object\Form\Parent2::BUTTON_SUBMIT_DATA);
		// last step: process form
		$this->form_object->process();
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
	 * Export file samples
	 *
	 * @param object $form
	 * @return string
	 */
	public function exportFilesSamples(& $form) {
		$result = '';
		$formats = \Object\Content\ImportFormats::getStatic();
		$temp = [];
		foreach ($formats as $k => $v) {
			$temp[] = \HTML::a(['href' => '?export_file_with_format=' . $k, 'value' => 'Import.' . $v['extension']]);
		}
		$result.= i18n(null, 'File Headers') . ': ' . implode(' | ', $temp);
		$result.= '<hr/>';
		return $result;
	}

	/**
	 * Validate
	 *
	 * @param object $form
	 */
	public function validate(& $form) {
		if (empty($_FILES['import_file']) || $_FILES['import_file']['error'] != UPLOAD_ERR_OK) {
			$form->error(DANGER, \Object\Content\Messages::REQUIRED_FIELD, 'import_file');
			return;
		}
		// process extension
		$extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
		$format = \Object\Content\ImportFormats::getStatic([
			'where' => [
				'extension' => $extension
			],
			'single_row' => true
		]);
		if (empty($format)) {
			$form->error(DANGER, 'Uknown file format!', 'import_file');
			return;
		}
		// import file
		$import_model = new \Numbers\Backend\IO\Common\Base();
		$temp = $import_model->import($format['format'], $_FILES['import_file']['tmp_name'], ['process_keys_and_values' => true]);
		if (!$temp['success']) {
			$form->error(DANGER, 'Could not read file!', 'import_file');
			return;
		}
		// process imported data
		$result = $form->import_object->processImportedSheets($temp['data'], $form->values);
		if (!$result['success']) {
			$form->error(DANGER, $result['error']);
			return;
		}
		// run imported data through form
		$messages = [
			'success' => 0,
			'error' => 0,
			'errors' => []
		];
		foreach ($result['data'] as $k => $v) {
			$v[\Object\Form\Parent2::BUTTON_SUBMIT_SAVE] = true;
			$form->import_object->addInput($v);
			$form->import_object->process();
			$temp = $form->import_object->apiResult();
			if (!$temp['success']) {
				$messages['error']++;
				$messages['errors'] = array_merge($messages['errors'], $temp['error']);
			} else {
				$messages['success']++;
			}
		}
		// print messages
		if (!empty($messages['success'])) {
			$form->error(SUCCESS, 'Successfully created/updated [number] records!', null, ['replace' => [
				'[number]' => i18n(null, $messages['success'])
			]]);
		}
		if (!empty($messages['error'])) {
			$form->error(DANGER, 'Could not import [number] records!', null, ['replace' => [
				'[number]' => i18n(null, $messages['error'])
			]]);
		}
		if (!empty($messages['errors'])) {
			foreach ($messages['errors'] as $v) {
				$v = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $v);
				$v = str_replace("\n", "<br/>", $v);
				$form->error(DANGER, $v, null, ['skip_i18n' => true]);
			}
		}
	}

	/**
	 * Save
	 *
	 * @param object $form
	 */
	public function save(& $form) {
		return true;
	}
}