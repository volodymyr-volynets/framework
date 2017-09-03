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
	 * Save
	 *
	 * @param array $input
	 * @return array
	 */
	public function save(array $input) : array {
		$input['__submit_save'] = true;
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
