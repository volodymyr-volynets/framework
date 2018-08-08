<?php

namespace Helper;
class cURL {

	/**
	 * Post
	 *
	 * @param string $url
	 * @param array $options
	 * @return array
	 */
	public static function post(string $url, array $options = []) : array {
		$result = [
			'success' => false,
			'error' => [],
			'data' => null
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query2($options['params']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result['data'] = curl_exec($ch);
		if (!empty($options['json'])) {
			$result['data'] = json_decode($result['data'], true);
		}
		curl_close($ch);
		$result['success'] = true;
		return $result;
	}

	/**
	 * Get
	 *
	 * @param string $url
	 * @param array $options
	 * @return array
	 */
	public static function get(string $url, array $options = []) : array {
		$result = [
			'success' => false,
			'error' => [],
			'data' => null
		];
		if (strpos($url, '?') !== false) {
			$url.= '&';
		} else {
			$url.= '?';
		}
		$url.= http_build_query2($options['params']);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result['data'] = curl_exec($ch);
		if (!empty($options['json'])) {
			$result['data'] = json_decode($result['data'], true);
		}
		curl_close($ch);
		$result['success'] = true;
		return $result;
	}

	/**
	 * Multi exec get
	 *
	 * @param array $urls
	 *		url
	 * @return array
	 */
	public static function multiExecGet(array $urls) : array {
		$result = [
			'success' => false,
			'error' => [],
			'data' => []
		];
		// create both cURL resources
		$ch = [];
		$mh = curl_multi_init();
		foreach ($urls as $k => $v) {
			$ch[$k] = curl_init();
			curl_setopt($ch[$k], CURLOPT_URL, $v['url']);
			curl_setopt($ch[$k], CURLOPT_HEADER, 0);
			curl_setopt($ch[$k], CURLOPT_RETURNTRANSFER, 1);
			// add handles
			curl_multi_add_handle($mh, $ch[$k]);
		}
		$active = null;
		// execute the handles
		do {
			$status = curl_multi_exec($mh, $active);
			// check for errors
			if ($status > 0) {
				$result['error'][] = curl_multi_strerror($status);
			}
		} while ($status === CURLM_CALL_MULTI_PERFORM || $active);
		// close the handles
		foreach ($ch as $k => $v) {
			$result['data'][$k] = curl_multi_getcontent($v); // get the content
			curl_multi_remove_handle($mh, $v);
			curl_close($v);
		}
		curl_multi_close($mh);
		// success
		if (empty($result['error'])) {
			$result['success'] = true;
		}
		return $result;
	}
}
