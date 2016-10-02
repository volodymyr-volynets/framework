<?php

class math {

	/**
	 * Scale
	 *
	 * @param int $scale
	 */
	public static function scale($scale) {
		bcscale($scale);
	}

	/**
	 * Compare two numbers
	 *
	 * @param type $arg1
	 * @param type $arg2
	 * @return int, -1, 0 or 1
	 */
	public static function compare($arg1, $arg2) {
		return bccomp($arg1 . '', $arg2 . '');
	}

	/**
	 * Normilize value
	 *
	 * @param mixed $value
	 */
	public static function normilize($value) {
		if (!is_string($value)) {
			return $value . '';
		}
		return $value;
	}
}
