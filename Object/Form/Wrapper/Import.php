<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Wrapper;

use Object\Content\ImportFormats;
use Object\Content\Messages;
use Object\Form\Base;
use Object\Form\Parent2;

class Import
{
    /**
     * Form object
     *
     * @var object
     */
    public $form_object;

    /**
     * Options
     *
     * @var array
     */
    public $options;

    /**
     * Constructor
     *
     * @param array $options
     *		input
     *		model
     */
    public function __construct($options = [])
    {
        $class = $options['input']['model'] = $options['model'];
        $this->options = $options;
        $form = new $class([
            'input' => [
                Parent2::BUTTON_SUBMIT_BLANK => true
            ],
            'skip_optimistic_lock' => true,
        ]);
        // if we need to export a sample
        if (!empty($options['input']['export_file_with_format'])) {
            $variables = $form->form_object->prepareExportFileVariables();
            $export_model = new \Numbers\Backend\IO\Common\Base();
            // filename
            $formats = ImportFormats::getStatic();
            $export_model->export($options['input']['export_file_with_format'], $variables['data'], ['output_file_name' => 'Import.' . $formats[$options['input']['export_file_with_format']]['extension']]);
        }
        $options['segment'] = Parent2::SEGMENT_IMPORT;
        $options['actions'] = [
            'refresh' => true,
            'back' => true,
            'new' => true
        ];
        $options['no_ajax_form_reload'] = true;
        // step 0: create form object
        $this->form_object = new Base('simple_import_form', $options);
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
        $this->form_object->container(Parent2::BUTTONS, [
            'default_row_type' => 'grid',
            'order' => PHP_INT_MAX
        ]);
        $this->form_object->element(Parent2::BUTTONS, 'row', Parent2::BUTTON_SUBMIT, Parent2::BUTTON_SUBMIT_DATA);
        // last step: process form
        $this->form_object->process();
    }

    /**
     * Render form
     *
     * @param string $format
     * @return string
     */
    public function render($format = 'text/html')
    {
        return $this->form_object->render($format);
    }

    /**
     * Export file samples
     *
     * @param object $form
     * @return string
     */
    public function exportFilesSamples(& $form)
    {
        $result = '';
        $formats = ImportFormats::getStatic();
        $temp = [];
        foreach ($formats as $k => $v) {
            $temp[] = \HTML::a(['href' => '?export_file_with_format=' . $k, 'value' => 'Import.' . $v['extension']]);
        }
        $result .= i18n(null, 'File Headers') . ': ' . implode(' | ', $temp);
        $result .= '<hr/>';
        return $result;
    }

    /**
     * Validate
     *
     * @param object $form
     */
    public function validate(& $form)
    {
        if (empty($_FILES['import_file']) || $_FILES['import_file']['error'] != UPLOAD_ERR_OK) {
            $form->error(DANGER, Messages::REQUIRED_FIELD, 'import_file');
            return;
        }
        // no limit when we import
        set_time_limit(0);
        \Debug::$debug = false;
        // process extension
        $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
        $format = ImportFormats::getStatic([
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
        // reset table
        if (!empty($this->options['reset_table'])) {
            $form->import_object->collection_object->primary_model->queryBuilder()->delete()->query();
        }
        // import data
        foreach ($result['data'] as $k => $v) {
            $v[Parent2::BUTTON_SUBMIT_SAVE] = true;
            // process pk
            $pk = $form->import_object->collection_object->primary_model->pk;
            if (!empty($form->import_object->collection_object->primary_model->tenant_column)) {
                unset($pk[array_search($form->import_object->collection_object->primary_model->tenant_column, $pk)]);
            }
            foreach ($pk as $v90) {
                if (isset($v[$v90]) && stripos($v[$v90], '_NEW_') !== false) {
                    unset($v[$v90]);
                }
            }
            $form->import_object->addInput($v);
            $form->import_object->process();
            $temp = $form->import_object->apiResult();
            if (!$temp['success']) {
                $messages['error']++;
                $messages['errors'] = array_merge($messages['errors'], $temp['error']);
            } else {
                $messages['success']++;
            }
            // gc
            unset($result['data'][$k], $v);
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
    public function save(& $form)
    {
        return true;
    }
}
