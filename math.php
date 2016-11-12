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
	 * Compare
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
	 * Add
	 *
	 * @param mixed $arg1
	 * @param mixed $arg2
	 * @param int $scale
	 * @return string
	 */
	public static function add($arg1, $arg2, $scale = null) {
		return self::__operator('bcadd', $arg1, $arg2, $scale ?? self::$scale);
	}

	/**
	 * Add with reference
	 *
	 * @param string $arg1
	 * @param string $arg2
	 * @param int $scale
	 * @return string
	 */
	public static function add2(& $arg1, $arg2, $scale = null) {
		$arg1 = self::add($arg1, $arg2, $scale);
		return $arg1;
	}

	/**
	 * Subtract
	 *
	 * @param mixed $arg1
	 * @param mixed $arg2
	 * @param int $scale
	 * @return string
	 */
	public static function subtract($arg1, $arg2, $scale = null) {
		return self::__operator('bcsub', $arg1, $arg2, $scale ?? self::$scale);
	}

	/**
	 * Subtract with reference
	 *
	 * @param string $arg1
	 * @param string $arg2
	 * @param int $scale
	 * @return string
	 */
	public static function subtract2(& $arg1, $arg2, $scale = null) {
		$arg1 = self::subtract($arg1, $arg2, $scale);
		return $arg1;
	}

	/**
	 * Multiply
	 *
	 * @param mixed $arg1
	 * @param mixed $arg2
	 * @param int $scale
	 * @param boolean $first
	 * @return string
	 */
	public static function multiply($arg1, $arg2, $scale = null) {
		return self::__operator('bcmul', $arg1, $arg2, $scale ?? self::$scale);
	}

	/**
	 * Wrapper for bcmath functions
	 *
	 * @param string $function
	 * @param mixed $arg1
	 * @param mixed $arg2
	 * @param int $scale
	 * @param boolean $first
	 * @return string
	 */
	private static function __operator($function, $arg1, $arg2, $scale) {
		if (is_array($arg1)) {
			$arg1_temp = $arg1;
			$temp1 = array_shift($arg1_temp);
			foreach ($arg1_temp as $v) {
				$temp1 = call_user_func_array($function, [$temp1, $v . '', $scale]);
			}
		} else {
			$temp1 = $arg1;
		}
		if (is_array($arg2)) {
			$temp2 = array_shift($arg2);
			foreach ($arg2 as $v) {
				$temp2 = call_user_func_array($function, [$temp2, $v . '', $scale]);
			}
		} else {
			$temp2 = $arg2;
		}
		return call_user_func_array($function, [$temp1, $temp2, $scale]);
	}

	/**
	 * Round
	 *
	 * @param string $arg1
	 * @param int $scale
	 * @return string
	 */
	public static function round($arg1, $scale = 0) {
		if (!is_int($scale)) {
			print_r2($scale);
			exit;
		}
		if ($arg1[0] != '-') {
			return bcadd($arg1, '0.' . str_repeat('0', $scale) . '5', $scale);
		} else {
			return bcsub($arg1, '0.' . str_repeat('0', $scale) . '5', $scale);
		}
	}

	/**
	 * Floor, round fractions down
	 *
	 * @param string $arg1
	 * @return string
	 */
	public static function floor($arg1) {
		if ($arg1[0] != '-') {
			return bcadd($arg1, '0', 0);
		} else {
			return bcsub($arg1, '1', 0);
		}
	}

	/**
	 * Ceil, round fractions up
	 *
	 * @param string $arg1
	 * @return string
	 */
	public static function ceil($arg1) {
		if ($arg1[0] != '-') {
			return bcadd($arg1, '1', 0);
		} else {
			return bcsub($arg1, '0', 0);
		}
	}

	/**
	 * Absolute
	 *
	 * @param string $arg1
	 * @return string
	 */
	public static function abs($arg1) {
		return ltrim($arg1, '-');
	}

	/**
	 * Zero
	 *
	 * @param int $scale
	 * @return string
	 */
	public static function zero($scale = null) {
		return self::add('0', '0.0000000000000', $scale ?? self::$scale);
	}
}