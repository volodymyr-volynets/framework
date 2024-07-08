<?php

namespace Object\Traits;
trait Stringable {

	/**
	 * To string
	 *
	 * @return string
	 */
	public function __toString() {
		// it will call __debugInfo on an object
        return print_r($this, true);
    }
}