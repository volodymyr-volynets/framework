<?php

class helper_ob {

	/**
	 * Start output buffering
	 */
	public static function start($clean = false) {
		if ($clean) {
			self::clean_all();
		}
		ob_start();
	}

	/**
	 * Clean output buffer and return its content
	 *
	 * @return string
	 */
	public static function clean() {
		if (ob_get_level() != 0) {
			return ob_get_clean();
		}
	}

	/**
	 * Clean all output buffers and return their content
	 *
	 * @return string
	 */
	public static function clean_all() {
		$result = '';
		for ($i = 0; $i < ob_get_level(); $i++) {
			$result.= ob_get_clean();
		}
		return $result;
	}
}
