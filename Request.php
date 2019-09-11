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
		// unknown
		if ($result == 'unknown' || is_null($result)) {
			$result = '127.0.0.1';
		}
		return $result;
	}

	/**
	 * Get merged cookie, get and post
	 * 
	 * @param mixed $key
	 * @param boolean $xss
	 * @param boolean $cookie
	 * @param array $options
	 *		array skip_xss_on_keys
	 *		boolean trim_empty_html_input
	 *		boolean remove_script_tag
	 * @return mixed
	 */
	public static function input($key = '', bool $xss = true, bool $cookie = false, array $options = []) {
		// cookie first, get and post after
		$_GET = $_GET ?? $_REQUEST ?? [];
		// fix files
		$files = [];
		foreach (($_FILES ?? []) as $k => $v) {
			// we need to convert
			if (is_array($v['name'])) {
				foreach ($v['name'] as $k2 => $v2) {
					if (empty($v['tmp_name'][$k2])) continue;
					$files[$k][$k2] = [
						'name' => $v2,
						'type' => $v['type'][$k2],
						'tmp_name' => $v['tmp_name'][$k2],
						'error' => $v['error'][$k2],
						'size' => $v['size'][$k2],
					];
				}
			} else {
				if (empty($v['tmp_name'])) continue;
				$files[$k] = $v;
			}
		}
		if ($cookie) {
			$result = array_merge($_COOKIE, $_GET, $_POST, $files);
		} else {
			$result = array_merge($_GET, $_POST, $files);
		}
		// protection against XSS attacks is on by default
		if ($xss) $result = strip_tags2($result, $options);
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
		$port = !empty($params['port']) ? (':' . $params['port']) : '';
		if (!$protocol) $protocol = self::isSSL() ? 'https' : 'http';
		if (!empty($params['host_parts'])) {
			$host = implode('.', $params['host_parts']);
		} else {
			$host = !empty($params['ip']) ? (getenv('SERVER_ADDR') . ':' . getenv('SERVER_PORT')) : getenv('HTTP_HOST');
		}
		if (!empty($params['name_only'])) {
			return $host;
		}
		if (!empty($params['level3'])) {
			$host = str_replace('www.', '', $host);
			$host = @$params['level3'] . '.' . $host;
		}
		$result = $protocol . '://' . $host . $port . (!empty($params['request']) ? $_SERVER['REQUEST_URI'] : '/');
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
	 * Generate urt for particular tenant
	 *
	 * @param string $tenant_part
	 * @return string
	 */
	public static function tenantHost(string $tenant_part) : string {
		$url = \Application::get('application.structure.app_domain_host');
		if (!empty($url)) {
			return \Request::host(['host_parts' => explode('.', $url)]);
		} else {
			// generate link to system tenant
			$domain_level = (int) \Application::get('application.structure.tenant_domain_level');
			$host_parts = \Request::hostParts();
			$host_parts[$domain_level] = $tenant_part;
			krsort($host_parts);
			return \Request::host(['host_parts' => $host_parts]);
		}
	}

	/**
	 * Is ssl
	 * 
	 * @return boolean
	 */
	public static function isSSL() : bool {
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
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
		session_write_close(); // a must
		header('Location: ' . $url);
		exit;
	}

	/**
	 * Build URL
	 *
	 * @param type $controller
	 * @param array $params
	 * @param string $host
	 * @return string
	 */
	public static function buildURL($controller, array $params = [], string $host = '', string $anchor = '') : string {
		if (empty($host)) {
			$host = \Request::host();
		}
		$controller = ltrim($controller, '/');
		return $host . $controller . '?' . http_build_query2($params) . ($anchor ? ('#' . $anchor) : '');
	}

	/**
	 * Get request method
	 *
	 * @return string
	 *		GET,HEAD,POST,PUT,DELETE,CONNECT,OPTIONS,TRACE,PATCH
	 *		CONSOLE is returned if not set
	 */
	public static function method() : string {
		return $_SERVER['REQUEST_METHOD'] ?? 'CONSOLE';
	}

	/**
	 * Hash
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hash(array $options) : string {
		return 'hash::' . implode('::', $options);
	}
}