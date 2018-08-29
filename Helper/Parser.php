<?php

namespace Helper;
class Parser {

	/**
	 * Regular expressions
	 */
	const REGEXP_EMAIL = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,})+/i';

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

	/**
	 * Match all email addresses
	 *
	 * @param string $str
	 * @return boolean|array
	 */
	public static function emails(string $str) {
		preg_match_all(self::REGEXP_EMAIL, $str, $matches);
		return $matches[0] ?? false;
	}
}