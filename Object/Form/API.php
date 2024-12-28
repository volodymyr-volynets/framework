<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form;

class API
{
    /**
     * Form
     *
     * @var object
     */
    public $form;

    /**
     * Constructor
     *
     * @param object $form
     */
    public function __construct($form)
    {
        $this->form = $form;
        $this->form->form_object->is_api = true;
    }

    /**
     * Begin
     */
    public function begin()
    {
        $this->form->form_object->collection_object->primary_model->db_object->begin();
    }

    /**
     * Commit
     */
    public function commit()
    {
        $this->form->form_object->collection_object->primary_model->db_object->commit();
    }

    /**
     * Rollback
     */
    public function rollback()
    {
        $this->form->form_object->collection_object->primary_model->db_object->rollback();
    }

    /**
     * Get
     *
     * @param array $input
     * @param array $options
     * @return array
     */
    public function get(array $input, array $options = []): array
    {
        $input = array_merge($input, ['__is_api_get' => true]);
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult($options);
    }

    /**
     * Save
     *
     * @param array $input
     * @param array $options
     * @return array
     */
    public function save(array $input, array $options = []): array
    {
        $input[Parent2::BUTTON_SUBMIT_SAVE] = true;
        $input[Parent2::BUTTON_SUBMIT] = true;
        // we must set entry method
        if (isset($this->form->form_object->collection_object->primary_model->column_prefix)) {
            $input[$this->form->form_object->collection_object->primary_model->column_prefix . 'entry_method'] = 'G';
        }
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult($options);
    }

    /**
     * Insert
     *
     * @param array $input
     * @return array
     */
    public function insert(array $input): array
    {
        if (\Application::get('flag.numbers.framework.api.enforce_rest')) {
            $temp2 = $this->get($input);
            if (!$temp2['values_loaded']) {
                return $this->save($input);
            } else {
                return [
                    'success' => false,
                    'error' => ['Can not create, record exists!'],
                    'values' => []
                ];
            }
        } else {
            return $this->save($input);
        }
    }

    /**
     * Update
     *
     * @param array $input
     * @return array
     */
    public function update(array $input): array
    {
        if (\Application::get('flag.numbers.framework.api.enforce_rest')) {
            $temp2 = $this->get($input);
            if ($temp2['values_loaded']) {
                return $this->save($input);
            } else {
                return [
                    'success' => false,
                    'error' => ['Can not update, record does not exists!'],
                    'values' => []
                ];
            }
        } else {
            return $this->save($input);
        }
    }

    /**
     * Ready to post
     *
     * @param array $input
     * @return array
     */
    public function readyToPost(array $input): array
    {
        $input[Parent2::BUTTON_SUBMIT_SAVE] = true;
        $input[Parent2::BUTTON_SUBMIT_READY_TO_POST] = true;
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult();
    }

    /**
     * Temporary post
     *
     * @param array $input
     * @return array
     */
    public function temporaryPost(array $input): array
    {
        $input[Parent2::BUTTON_SUBMIT_SAVE] = true;
        $input[Parent2::BUTTON_SUBMIT_TEMPORARY_POST] = true;
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult();
    }

    /**
     * Open
     *
     * @param array $input
     * @return array
     */
    public function open(array $input): array
    {
        $input[Parent2::BUTTON_SUBMIT_SAVE] = true;
        $input[Parent2::BUTTON_SUBMIT_OPEN] = true;
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult();
    }

    /**
     * Post
     *
     * @param array $input
     * @return array
     */
    public function post(array $input): array
    {
        $input[Parent2::BUTTON_SUBMIT_SAVE] = true;
        $input[Parent2::BUTTON_SUBMIT_POST] = true;
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult();
    }

    /**
     * Mark deleted
     *
     * @param array $input
     * @return array
     */
    public function markDeleted(array $input): array
    {
        $input[Parent2::BUTTON_SUBMIT_SAVE] = true;
        $input[Parent2::BUTTON_SUBMIT_MARK_DELETED] = true;
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult();
    }

    /**
     * Deleted
     *
     * @param array $input
     * @param array $options
     * @return array
     */
    public function delete(array $input, array $options = []): array
    {
        $input[Parent2::BUTTON_SUBMIT_SAVE] = true;
        $input[Parent2::BUTTON_SUBMIT_DELETE] = true;
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult($options);
    }

    /**
     * Execute
     *
     * @param array $input
     * @return array
     */
    public function execute(array $input): array
    {
        $this->form->form_object->addInput($input);
        $this->form->form_object->process();
        return $this->form->form_object->apiResult();
    }
}
