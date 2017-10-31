<?php

namespace Object\Form;
class API {

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
	public function __construct($form) {
		$this->form = $form;
	}

	/**
	 * Begin
	 */
	public function begin() {
		$this->form->form_object->collection_object->primary_model->db_object->begin();
	}

	/**
	 * Commit
	 */
	public function commit() {
		$this->form->form_object->collection_object->primary_model->db_object->commit();
	}

	/**
	 * Rollback
	 */
	public function rollback() {
		$this->form->form_object->collection_object->primary_model->db_object->rollback();
	}

	/**
	 * Get
	 *
	 * @param array $input
	 * @return array
	 */
	public function get(array $input) : array {
		$this->form->form_object->addInput($input);
		$this->form->form_object->process();
		return $this->form->form_object->apiResult();
	}

	/**
	 * Save
	 *
	 * @param array $input
	 * @return array
	 */
	public function save(array $input) : array {
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_SAVE] = true;
		// we must set entry method
		$input[$this->form->form_object->collection_object->primary_model->column_prefix . 'entry_method'] = 'G';
		$this->form->form_object->addInput($input);
		$this->form->form_object->process();
		return $this->form->form_object->apiResult();
	}

	/**
	 * Ready to post
	 *
	 * @param array $input
	 * @return array
	 */
	public function readyToPost(array $input) : array {
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_SAVE] = true;
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_READY_TO_POST] = true;
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
	public function temporaryPost(array $input) : array {
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_SAVE] = true;
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_TEMPORARY_POST] = true;
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
	public function open(array $input) : array {
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_SAVE] = true;
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_OPEN] = true;
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
	public function post(array $input) : array {
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_SAVE] = true;
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_POST] = true;
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
	public function markDeleted(array $input) : array {
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_SAVE] = true;
		$input[\Object\Form\Parent2::BUTTON_SUBMIT_MARK_DELETED] = true;
		$this->form->form_object->addInput($input);
		$this->form->form_object->process();
		return $this->form->form_object->apiResult();
	}

	/**
	 * Execute
	 *
	 * @param array $input
	 * @return array
	 */
	public function execute(array $input) : array {
		$this->form->form_object->addInput($input);
		$this->form->form_object->process();
		return $this->form->form_object->apiResult();
	}
}
