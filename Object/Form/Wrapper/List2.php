<?php

namespace Object\Form\Wrapper;
class List2 extends \Object\Form\Wrapper\Base {

	/**
	 * Constructor
	 *
	 * @see \Object\Form\Wrapper\Base::construct()
	 */
	public function __construct($options = []) {
		$options['initiator_class'] = 'list';
		parent::__construct($options);
	}
}