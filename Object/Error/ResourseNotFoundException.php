<?php

namespace Object\Error;
class ResourseNotFoundException extends \Exception {

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct(string $message, int $code = 0, \Exception $previous = null) {
		// call parent constructor
		parent::__construct($message, $code, $previous);
	}
}