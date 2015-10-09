<?php

class keywords {

	/**
	 * Process string to get keywords
	 * 
	 * @param string $text
	 * @return array
	 */
	public static function get($type, $text, $name, $title) {
		$result = array(
			'text' => '',
			'keywords' => array(),
		);

		// text is everything together stripped
		$result['text'] = trim(self::strip($type . ' ' . $text . ' ' . $name . ' ' . $title));

		// keywords we get from text
		$temp = explode(' ', $result['text']);
		foreach ($temp as $v) {
			$v = trim($v);
			if ($v!='') {
				@$result['keywords'][$v]++;
			}
		}
		return $result;
	}

	/**
	 * Strip tags
	 * 
	 * @param string $text
	 * @return string
	 */
	public static function strip($text) {
		// extract alt attributes in images
		$text = preg_replace('/<img(.*)alt="(.*)"\>/', "$2", $text);
		// remove all tags
		$text = strip_tags($text);
		// remove non word or number
		//$text = preg_replace("/[^A-Za-z0-9 ]/", '', $text);
		// remove line breaks / tabs
		$text = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $text);
		$text = trim($text);
		// remove extra white characters
		$text = preg_replace("/\s+/", " ", $text);
		return $text;
	}

	/**
	 * Highlight keywords
	 *
	 * @param string $text
	 * @param string $words
	 * @return string
	 */
	public static function highlight($text, $words, $tag = array('<b>', '</b>')) {
		$text = self::strip($text);
		preg_match_all('~\w+~', $words, $m);
		if(!$m) {
			return $text;
		}
		$re = '~\\b(' . implode('|', $m[0]) . ')\\b~i';
		return preg_replace($re, $tag[0] . '$0' . $tag[1], $text);
	}
}