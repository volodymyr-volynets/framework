<?php

class math {

	/**
	 * Scale
	 *
	 * @var int
	 */
	public static $scale = 2;

	/**
	 * Scale
	 *
	 * @param int $scale
	 */
	public static function scale($scale) {
		self::$scale = $scale;
		bcscale($scale);
	}

	/**
	 * Compare two numbers
	 *
	 * @param mixed $arg1
	 * @param mixed $arg2
	 * @param int $scale
	 * @return int, -1, 0 or 1
	 */
	public static function compare($arg1, $arg2, $scale = null) {
		return bccomp($arg1 . '', $arg2 . '', $scale ?? self::$scale);
	}

	/**
	 * Add numbers
	 *
	 * @param mixed $arg1
	 * @return type
	 */
	public static function add($arg1) {
		$result = '0';
		$args = func_get_args();
		foreach ($args as $v) {
			$result = bcadd($result, $v . '');
		}
		return $result;
	}
}
