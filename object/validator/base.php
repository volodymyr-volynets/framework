<?php

class object_validator_base {

	/**
	 * Override object
	 *
	 * @var object
	 */
	public $override;

	/**
	 * Constructor
	 */
	public function __construct() {
		$called_class = get_called_class();
		$called_class = str_replace('object_validator_', 'overrides_validator_', $called_class);
		// check if override exists
		$path = application::get(['application', 'path']) . str_replace('_', DIRECTORY_SEPARATOR, $called_class);
		if (file_exists($path)) {
			$this->override = factory::model($called_class);
		}
	}
}