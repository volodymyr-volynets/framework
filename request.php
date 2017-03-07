<?php

class request {

	/**
	 * User IP
	 * 
	 * @return string
	 */
	public static function ip() {
		// for development purposes we might need to have specific IP address
		$request_ip = application::get('flag.numbers.framework.request.ip');
		if (!empty($request_ip)) {
			return $request_ip;
		}
		// get users IP
		$result = $_SERVER['REMOTE_ADDR'];
		// if request goes through the proxy
		if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
			$result = $_SERVER['HTTP_X_REAL_IP'];
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$result = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		// sometimes we have few IP addresses we take last one
		if (strpos($result, ',') !== false) {
			$temp = explode(',', $result);
			$result = trim($temp[sizeof($temp) - 1]);
		}
		return $result;
	}

	/**
	 * Get merged cookie, get and post
	 * 
	 * @param mixed $key
	 * @param boolean $xss
	 * @param boolean $cookie
	 * @return mixed
	 */
	public static function input($key = '', $xss = true, $cookie = false) {

		// cookie first, get and post after
		if ($cookie) {
			$result = array_merge2($_COOKIE, $_GET, $_POST);
		} else {
			$result = array_merge2($_GET, $_POST);
		}

		// protection against XSS attacks is on by default
		if ($xss) $result = self::strip_tags($result);

		// we need to get rid of session id from the result
		if (!$cookie) {
			unset($result[session_name()]);
		}

		// if we are debugging
		if (debug::$debug) {
			debug::$data['input'][] = $result;
		}

		// returning result
		if ($key) {
			return array_key_get($result, $key);
		} else {
			return $result;
		}
	}

	/**
	 * Strip tags
	 * 
	 * @param array $arr
	 * @return array
	 */
	private static function strip_tags($arr) {
		if (is_array($arr)) {
			$result = [];
			foreach ($arr as $k=>$v) {
				if (is_string($k)) {
					$k = strip_tags($k);
				}
				$result[$k] = self::strip_tags($v);
			}
			return $result;
		} else if (is_string($arr)) {
			return strip_tags($arr);
		}
		return $arr;
	}

	/**
	 * Host, input parameters: ip, request, protocol
	 * 
	 * @param array $params
	 * @return string
	 */
	public static function host(array $params = []) : string {
		$protocol = !empty($params['protocol']) ? $params['protocol'] : '';
		if (!$protocol) $protocol = self::is_ssl() ? 'https' : 'http';
		$host = !empty($params['ip']) ? (getenv('SERVER_ADDR') . ':' . getenv('SERVER_PORT')) : getenv('HTTP_HOST');
		if (!empty($params['level3'])) {
			$host = str_replace('www.', '', $host);
			$host = @$params['level3'] . '.' . $host;
		}
		return $protocol . '://' . $host . (!empty($params['request']) ? $_SERVER['REQUEST_URI'] : '/');
	}

	/**
	 * Get host parts
	 * 
	 * @param string $host
	 * @return array
	 */
	public static function host_parts($host = null) {
		if (empty($host)) {
			$host = self::host();
		}
		$host = str_replace(array('http://', 'https://'), '', $host);
		$host = str_replace('/', '', $host);
		$temp = explode('.', $host);
		krsort($temp);
		$result = [];
		$counter = 1;
		foreach ($temp as $k => $v) {
			$result[$counter] = $v;
			$counter++;
		}
		return $result;
	}

	/**
	 * Is ssl
	 * 
	 * @return boolean
	 */
	public static function is_ssl() {
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'])=='https') {
			return true;
		} else if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Redirect
	 *
	 * @param string $url
	 */
	public static function redirect($url) {
		// todo: handle flow
		header('Location: ' . $url);
		exit;
	}
}