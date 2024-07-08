<?php

namespace Helper;
class cURL {

	/**
	 * User agent
	 */
	const USERAGENT = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)";

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
			'data' => null,
			'info' => null,
			'params' => $options['params'] ?? [],
			'url' => $url
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		if (!empty($options['params'])) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['params']));
		} else if (isset($options['raw'])) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options['raw']);
		}
		// put
		if (!empty($options['put'])) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		// basic authentication
		if (!empty($options['basic_auth'])) {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $options['basic_auth']);
		}
		// extra headers
		if (!empty($options['headers'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
		}
		$result['data'] = curl_exec($ch);
		if (!empty($options['json']) && is_json($result['data'])) {
			$result['data'] = json_decode($result['data'], true);
		}
		if (!curl_errno($ch)) {
			$result['info'] = curl_getinfo($ch);
		} else {
			$result['error'][] = curl_error($ch);
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
			'data' => null,
			'info' => [],
			'params' => $options['params'] ?? []
		];
		if (!empty($options['params'])) {
			if (strpos($url, '?') !== false) {
				$url.= '&';
			} else {
				$url.= '?';
			}
			$url.= http_build_query($options['params']);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		// basic authentication
		if (!empty($options['basic_auth'])) {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $options['basic_auth']);
		}
		// extra headers
		if (!empty($options['headers'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
		}
		$result['data'] = curl_exec($ch);
		if (!empty($options['json']) && is_json($result['data'])) {
			$result['data'] = json_decode($result['data'], true);
		}
		if (!curl_errno($ch)) {
			$result['info'] = curl_getinfo($ch);
		} else {
			$result['error'][] = curl_error($ch);
		}
		curl_close($ch);
		$result['success'] = true;
		return $result;
	}

	/**
	 * Multi exec get
	 *
	 * @param array $urls
	 *	url
	 * @param array $options
	 *	bool json
	 *      bool info
	 * @return array
	 */
	public static function multiExecGet(array $urls, array $options =[]) : array {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [],
			'info' => []
		];
		// create both cURL resources
		$ch = [];
		$mh = curl_multi_init();
		foreach ($urls as $k => $v) {
			$ch[$k] = curl_init();
			curl_setopt($ch[$k], CURLOPT_URL, $v['url']);
			curl_setopt($ch[$k], CURLOPT_USERAGENT, self::USERAGENT);
			curl_setopt($ch[$k], CURLOPT_HEADER, 0);
			curl_setopt($ch[$k], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch[$k], CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch[$k], CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch[$k], CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch[$k], CURLOPT_VERBOSE, 1);
			// add handles
			curl_multi_add_handle($mh, $ch[$k]);
		}
		$active = null;
		// execute the handles
		do {
			$mrc = curl_multi_exec($mh, $active);
			usleep(10000);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) != -1) {
				do {
					$mrc = curl_multi_exec($mh, $active);
					usleep(10000);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		// Check for errors
		if ($mrc != CURLM_OK) {
			$result['error'][] = curl_multi_strerror($mrc);
		}
		// close the handles
		foreach ($ch as $k => $v) {
			$result['data'][$k] = curl_multi_getcontent($v); // get the content
			if (!empty($options['json']) && !empty($result['data'][$k])) {
				$result['data'][$k] = json_decode($result['data'][$k], true);
			}
			if (!empty($options['info'])) {
				$result['info'][$k] = curl_getinfo($v);
			}
			$error = curl_error($v);
			if ($error) {
				$result['error'][] = $error;
			}
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
