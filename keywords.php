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
		// remove extra white characters
		$text = preg_replace("/\s+/", " ", $text);
		return $text;
	}
}