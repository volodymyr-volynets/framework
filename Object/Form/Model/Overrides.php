<?php

namespace Object\Form\Model;
class Overrides extends \Object\Override\Data {

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		// we need to handle overrrides
		parent::overrideHandle($this);
	}

	/**
	 * Get overrides
	 *
	 * @param string $form_class
	 * @return array
	 */
	public function getOverrides(string $form_class) : array {
		$form_class = '\\' . ltrim($form_class, '\\');
		return array_keys($this->data[$form_class] ?? []);
	}
}