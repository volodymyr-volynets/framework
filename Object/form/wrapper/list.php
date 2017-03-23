<?php

class object_form_wrapper_list extends object_form_wrapper_base {

	/**
	 * Constructor
	 *
	 * @see object_form_wrapper_base::construct()
	 */
	public function __construct($options = []) {
		$options['initiator_class'] = 'list';
		parent::__construct($options);
	}
}