<?php

class regex_base {

	/**
	 * Parse
	 *
	 * @param string $value
	 * @param array $keywords
	 * @return array
	 */
	public static function parse($value, $keywords) {
		$result = array(
			'success' => false,
			'error' => [],
			'data' => []
		);
		if (is_string($value)) {
			$regexp = '/\[(' . implode('|', $keywords) . ')\[(.*?)\](\[(.*?)\])?\]/i';
			preg_match_all($regexp, $value, $matches);
			if (!empty($matches[0])) {
				foreach ($matches[0] as $k => $v) {
					$result['data'][$v] = array(
						'type' => strtolower($matches[1][$k]),
						'name' => strtolower($matches[2][$k]),
						'param_name' => strtolower(isset($matches[4][$k]) ? $matches[4][$k] : '')
					);
				}
			}
			if (count($result['data']) > 0) $result['success'] = true;
		}
		return $result;
	}
}