<?php

class request {
	
	
	/**
	 * User IP
	 * 
	 * @return string
	 */
	public static function ip() {
		$result = $_SERVER['REMOTE_ADDR'];
		// if request goes through the proxy
		if (@$_SERVER['HTTP_X_FORWARDED_FOR']) $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
		// sometimes we have few IP addresses we take last one
		if (strpos($result, ',') !== false) {
			$temp = explode(',', $result);
			$result = trim($temp[sizeof($temp)-1]);
		}
		return $result;
	}
	
	/**
	 * Get merged cookie, get and post
	 * 
	 * @param mixed $key
	 * @param boolean $xss
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
			$result = array();
			foreach ($arr as $k=>$v) {
				$k = strip_tags($k);
				$result[$k] = self::strip_tags($v);
			}
		} else {
			$result = strip_tags($arr);
		}
		return $result;
	}
	

    /**
     * Host, input parameters: ip, request, protocol
     * 
     * @param array $params
     * @return string
     */
    public static function host($params = array()) {
		$protocol = @$params['protocol'] ? $params['protocol'] : '';
		if (!$protocol) $protocol = (!getenv('HTTPS') || getenv('HTTPS')=='off') ? 'http' : 'https';
		$host = @$params['ip'] ? (getenv('SERVER_ADDR') . ':' . getenv('SERVER_PORT')) : getenv('HTTP_HOST');
		if (@$params['level3']) {
			$host = str_replace('www.', '', $host);
			$host = @$params['level3'] . '.' . $host;
		}
		return $protocol . '://' . $host . (@$params['request'] ? $_SERVER['REQUEST_URI'] : '/');
    }
    
    /**
     * Get host parts
     * 
     * @param string $host
     * @return array
     */
    public static function host_parts($host = null) {
    	if (empty($host)) $host = self::host();
    	$host = str_replace(array('http://', 'https://'), '', $host);
    	$host = str_replace('/', '', $host);
    	$temp = explode('.', $host);
    	krsort($temp);
    	$result = array();
    	$counter = 1;
    	foreach ($temp as $k=>$v) {
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
		if (strtolower(@$_SERVER['HTTP_X_FORWARDED_PROTO'])=='https') {
			return true;
		} else if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off') {
    		return true;
    	} else {
    		return false;
    	}
    }
}