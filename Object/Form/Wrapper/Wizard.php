<?php

namespace Object\Form\Wrapper;
class Wizard {

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	public function __construct(array $options = []) {
		$this->options = $options;
	}

	/**
	 * Render
	 *
	 * @return string
	 */
	public function render() {
		$result = \HTML::wizard([
			'type' => $this->options['wizard']['type'] ?? null,
			'step' => $this->options['input']['__wizard_step'] ?? null,
			'options' => $this->options['wizard']['options'] ?? []
		]);
		if (!empty($result)) {
			$result.= \HTML::hr();
		}
		return $result;
	}
}