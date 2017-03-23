<?php

class Request {

	/**
	 * User IP
	 * 
	 * @return string
	 */
	public static function ip() : string {
		// for development purposes we might need to have specific IP address
		$request_ip = Application::get('flag.numbers.framework.request.ip');
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
	public static function input($key = '', bool $xss = true, bool $cookie = false) {
		// cookie first, get and post after
		if ($cookie) {
			$result = array_merge($_COOKIE, $_GET, $_POST);
		} else {
			$result = array_merge($_GET, $_POST);
		}
		// protection against XSS attacks is on by default
		if ($xss) $result = strip_tags2($result);
		// we need to get rid of session id from the result
		if (!$cookie) {
			unset($result[session_name()]);
		}
		// if we are debugging
		if (Debug::$debug) {
			Debug::$data['input'][] = $result;
		}
		// returning result
		if ($key) {
			return array_key_get($result, $key);
		} else {
			return $result;
		}
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
		if (!empty($params['host_parts'])) {
			$host = implode('.', $params['host_parts']);
		} else {
			$host = !empty($params['ip']) ? (getenv('SERVER_ADDR') . ':' . getenv('SERVER_PORT')) : getenv('HTTP_HOST');
		}
		if (!empty($params['level3'])) {
			$host = str_replace('www.', '', $host);
			$host = @$params['level3'] . '.' . $host;
		}
		$result = $protocol . '://' . $host . (!empty($params['request']) ? $_SERVER['REQUEST_URI'] : '/');
		// append mvc
		if (!empty($params['mvc'])) {
			$result = rtrim($result, '/') . $params['mvc'];
		}
		// append parameters
		if (!empty($params['params'])) {
			$result.= '?' . http_build_query2($params['params']);
		}
		return $result;
	}

	/**
	 * Get host parts
	 * 
	 * @param string $host
	 * @return array
	 */
	public static function hostParts($host = null) {
		if (empty($host)) {
			$host = self::host();
		}
		$host = str_replace(['http://', 'https://', '/'], '', $host);
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
	public static function isSsl() : bool {
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