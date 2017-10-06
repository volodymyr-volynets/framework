<?php

namespace Object\Form\Wrapper;
class Report extends \Object\Form\Wrapper\Base {

	/**
	 * Constructor
	 *
	 * @see \Object\Form\Wrapper\Base::construct()
	 */
	public function __construct($options = []) {
		$options['initiator_class'] = 'report';
		parent::__construct($options);
	}
}