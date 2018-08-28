<?php

namespace Helper;
class Parser {

	/**
	 * Match single occurrence in a string
	 *
	 * @param string $str
	 * @param string $start
	 * @param string $end
	 * @return boolean|string
	 */
	public static function match(string $str, string $start, string $end) {
		$start = preg_quote($start);
		$end = preg_quote($end);
		if (preg_match('/' . $start . '(.*)' . $end . '/', $str, $matches)) {
			return $matches[1] . '';
		}
		return false;
	}
}