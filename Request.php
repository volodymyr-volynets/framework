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
		$result = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
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
				$level = array_nested_levels_count($v['name']);
				// details
				if ($level == 2) {
					foreach ($v['name'] as $k2 => $v2) {
						foreach ($v2 as $k3 => $v3) {
							if (empty($v['tmp_name'][$k2][$k3])) continue;
							$files[$k][$k2][$k3] = [
								'name' => $v3,
								'type' => $v['type'][$k2][$k3],
								'tmp_name' => $v['tmp_name'][$k2][$k3],
								'error' => $v['error'][$k2][$k3],
								'size' => $v['size'][$k2][$k3],
							];
						}
					}
				} else if ($level == 3) {
					foreach ($v['name'] as $k2 => $v2) {
						foreach ($v2 as $k3 => $v3) {
							foreach ($v3 as $k4 => $v4) {
								if (empty($v['tmp_name'][$k2][$k3][$k4])) continue;
								$files[$k][$k2][$k3] = [
									'name' => $v4,
									'type' => $v['type'][$k2][$k3][$k4],
									'tmp_name' => $v['tmp_name'][$k2][$k3][$k4],
									'error' => $v['error'][$k2][$k3][$k4],
									'size' => $v['size'][$k2][$k3][$k4],
								];
							}
						}
					}
				} else {
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
				}
			} else {
				if (empty($v['name'])) {
					continue;
				}
				$files[$k] = $v;
			}
		}
		if ($cookie) {
			$result = array_merge_hard($_COOKIE, $_GET, $_POST, $files);
		} else {
			$result = array_merge_hard($_GET, $_POST, $files);
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
	public static function buildURL($controller, array $params = [], $host = null, string $anchor = '') : string {
		if (!isset($host)) {
			$host = \Request::host();
		}
		$controller = ltrim($controller, '/');
		return $host . $controller . '?' . http_build_query($params) . ($anchor ? ('#' . $anchor) : '');
	}

	/**
	 * Build URL from name
	 *
	 * @param string $name
	 * @param string $action
	 * @param array $params
	 * @param string|null $host
	 * @param string|null $anchor
	 * @param bool $as_json
	 * @return string
	 */
	public static function buildFromName(string $name, string $action = 'Edit', array $params = [], ?string $host = null, ?string $anchor = null, bool $as_json = false) : string {
		if (is_null(\Object\Controller::$cached_controllers) && !\Object\Error\Base::$flag_database_tenant_not_found) {
			\Object\Controller::$cached_controllers = \Object\ACL\Resources::getStatic('controllers', 'primary');
		}
		if (is_null(\Object\Controller::$cached_controllers_by_names)) {
			foreach (\Object\Controller::$cached_controllers as $k => $v) {
				$v['key'] = $k;
				\Object\Controller::$cached_controllers_by_names[$v['name']] = $v;
			}
		}
		$url = '/';
		$template = '';
		if (!empty(\Object\Controller::$cached_controllers_by_names[$name])) {
			$v = \Object\Controller::$cached_controllers_by_names[$name];
			$url = rtrim($host ?? '', '/') . '/' . \Application::get('application.template.url_path_name') . '-' . ucfirst($v['template']) . '/'. ltrim(str_replace('\\', '/', $v['key']), '/') . '/_' . $action . '?' . http_build_query($params) . ($anchor ? ('#' . $anchor) : '');
			$url = rtrim($url, '?');
			$template = $v['template'];
		}
		if ($as_json) {
			return json_encode([
				'name' => $name,
				'action' => $action,
				'params' => $params,
				'host' => $host,
				'anchor' => $anchor,
				'template' => $template
			]);
		} else {
			return $url;
		}
	}

	/**
	 * Build URL from JSON
	 *
	 * @param string $json
	 * @param string|null $host
	 * @return string
	 */
	public static function buildFromJson(string $json, ?string $host = null) : string {
		$json = json_decode($json, true);
		return self::buildFromName($json['name'], $json['action'] ?? 'Edit', $json['params'] ?? [], $host ?? $json['host'] ?? null, $json['anchor']);
	}


	/**
	 * Build URL for current controller
	 *
	 * @param string|null $action
	 * @return string
	 */
	public static function buildFromCurrentController(?string $action = null) : string {
		$mvc = \Application::get('mvc');
		$controller_class = $mvc['controller_class'];
		$controller_object = new $controller_class();
		return rtrim(self::buildFromName($controller_object->title, $action ?? $mvc['controller_action_raw']), '?');
	}

	/**
	 * Fix URL
	 *
	 * @param string|null $url
	 * @param string $template
	 * @param string $default
	 * @return string
	 */
	public static function fixUrl(?string $url, string $template, string $default = '') : string {
		if (!empty($url)) {
			if ($url[0] === '/') {
				if (strpos($url, '/' . \Application::get('application.template.url_path_name') ?? 'X-Template') === false) {
					$url = '/' .  \Application::get('application.template.url_path_name') . '-' . ucfirst($template) . $url;
				}
			}
			return $url;
		} else {
			return $default;
		}
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

	/**
	 * Replace HTML tags
	 *
	 * In HTML we can add: href="[[Url;U/M Sign In;Index]]"
	 *
	 * @param string $html
	 * @return string
	 */
	public static function htmlReplaceTags(string $html) : string {
		$matches = [];
		preg_match_all('/\[\[(.*?)\]\]/is', $html, $matches, PREG_PATTERN_ORDER);
		if (!empty($matches[1])) {
			foreach ($matches[1] as $k => $v) {
				$v = explode(';', $v);
				if (strtolower($v[0]) === 'url') {
					$html = str_replace($matches[0][$k], \Request::buildFromName($v[1], $v[2]), $html);
				}
			}
		}
		return $html;
	}

	/**
	 * Check if given URL or domain is white listed.
	 *
	 * @param string $url
	 * @param array $whitelist
	 * @return bool
	 */
	public static function urlWhitelisted(string $url, array $whitelist) : bool {
		$domain = parse_url($url, PHP_URL_HOST);
		// exact match
		if (in_array($domain, $whitelist)) {
			return true;
		}
		foreach ($whitelist as $v ) {
			$v = '.' . $v;
			if (strpos($domain, $v) === (strlen($domain) - strlen($v))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Error
	 *
	 * @param int $status
	 * @param string|null $text
	 */
	public static function error(int $status, ?string $text = null) : void {
		if ($text === null) {
			switch ($status) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:
					$text = 'Not Found';
			}
		}
		header('HTTP/1.1 ' . $status);
		echo i18n(null, $text);
		exit;
	}
}