<?php

class regex_keywords {

	/**
	 * Highlight keyword
	 *
	 * @param string $str
	 * @param string $keywords
	 * @param string $start_tag
	 * @param string $end_tag
	 * @return type
	 */
	public static function highlight($str, $keywords, $start_tag = '<b><u>', $end_tag = '</u></b>') {
		$keywords = trim($keywords);
		if ($keywords != '') {
			$keywords = array_unique(explode(' ', $keywords));
			$matches = [];
			foreach ($keywords as $v) {
				if (preg_match_all("/($v)/i", $str, $matches)) {
					if (!empty($matches[1])) {
						$matches[1] = array_unique($matches[1]); // a must
						foreach ($matches[1] as $v2) {
							$str = str_replace($v2, $start_tag . $v2 . $end_tag, $str);
						}
					}
				}
			}
		}
		return $str;
	}
}