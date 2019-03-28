<?php

namespace Object\Form\Wrapper;
class Email extends \Object\Form\Wrapper\Base {

	/**
	 * Constructor
	 *
	 * @see \Object\Form\Wrapper\Base::construct()
	 */
	public function __construct($options = []) {
		$options['initiator_class'] = 'email';
		parent::__construct($options);
	}
}