<?php

class regex_keywords {

	/**
	 * Highlight keyword
	 *
	 * @param string $str
	 * @param string $keywords
	 * @param string $start_tag
	 * @param string $end_tag
	 * @return string
	 */
	public static function highlight($str, $keywords, $start_tag = '<b><u>', $end_tag = '</u></b>') {
		$keywords = trim($keywords);
		if ($keywords != '') {
			$keywords = self::cleanup_keywords($keywords);
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

	/**
	 * Extract short strings by keyword
	 *
	 * @param string $str
	 * @param string $keywords
	 * @return string
	 */
	public static function extract_short_strings($str, $keywords) {
		$result = [];
		// cleanup string
		$str = trim(strip_tags($str));
		$str = html_entity_decode($str);
		$str = preg_replace('/[\t]/', ' ', $str);
		$str = preg_replace('/\s{2,}/', ' ', $str);
		// generate sentances
		$sentances = [];
		$temp = null;
		for ($i = 0; $i < strlen($str); $i++) {
			$temp.= $str[$i];
			if (in_array($str[$i], ['.', '?', ';', "\n", '!'])) {
				$temp = trim($temp) . '';
				if ($temp != '' && !in_array($temp, ['.', '?', ';', "\n", '!'])) {
					$sentances[] = $temp;
				}
				$temp = null;
			}
		}
		if ($temp !== null) {
			$sentances[] = $temp;
		}
		// cleanup keywords
		$cleaned_keywords = self::cleanup_keywords($keywords);
		// run keywords though sentances
		$statistics = [];
		foreach ($sentances as $k0 => $v0) {
			$matches = [];
			foreach ($cleaned_keywords as $v) {
				if (preg_match_all("/($v)/i", $v0, $matches)) {
					if (!empty($matches[1])) {
						//$matches[1] = array_unique($matches[1]); // a must
						foreach ($matches[1] as $v2) {
							$statistics[$k0][$v2] = ($statistics[$k0][$v2] ?? 0) + 1;
						}
					}
				}
			}
		}
		// rank
		$final = [];
		foreach ($statistics as $k => $v) {
			$total = 0;
			foreach ($v as $k2 => $v2) {
				$total+= $v2;
			}
			$final[$k] = $total * count($v);
		}
		// sort
		arsort($final);
		// assemble
		if (!empty($final)) {
			$total = 0;
			foreach ($final as $k => $v) {
				$total+= strlen($sentances[$k]);
				if ($total < 500) {
					$result[] = $sentances[$k] . ' ... ';
				} else {
					break;
				}
			}
		} else {
			// just grab first few
			$total = 0;
			foreach ($sentances as $k => $v) {
				$total+= strlen($v);
				if ($total < 500) {
					$result[] = $v . ' ... ';
				} else {
					break;
				}
			}
		}
		return self::highlight(implode(' ', $result), $keywords);
	}

	/**
	 * Cleanup keywords
	 *
	 * @param string $keywords
	 * @return array
	 */
	public static function cleanup_keywords($keywords) {
		// remove extra spaces, tabs and line breaks
		$keywords = preg_replace('/[\t\n\r]/', ' ', $keywords);
		$keywords = preg_replace('/\s{2,}/', ' ', $keywords);
		return array_unique(explode(' ', $keywords));
	}
}