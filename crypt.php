<?php

class crypt {
	
	/**
	 * Encrypting data (URL safe)
	 * 
	 * @param string $data
	 * @param string $key
	 * @return string
	 */
	public static function encrypt($data, $key = '') {
		// using default key if no key is passed
		$key = $key ? $key : application::get(array('crypt', 'key'));
		
		// encrypting
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv);

		// make it URL safe
		return base64_encode($iv . $encrypted);
	}

	/**
	 * Decrypting data (URL safe)
	 * 
	 * @param string $data
	 * @param string $key
	 * @return string
	 */
	public static function decrypt($data, $key = '') {
		// using default key if no key is passed
		$key = $key ? $key : application::get(array('crypt', 'key'));
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$decoded = base64_decode($data);
		$iv = mb_substr($decoded, 0, $iv_size, 'latin1');
		$cipher = mb_substr($decoded, $iv_size, mb_strlen($decoded, 'latin1'), 'latin1');
		$decrypted = @mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $cipher, MCRYPT_MODE_CBC, $iv);
		return trim($decrypted);
	}
	
	/**
	 * Generate a hash
	 * 
	 * @param string $data
	 * @return string
	 */
	public static function hash($data) {
		$method = application::get(array('crypt', 'hash'));
		return hash(($method ? $method : 'md5'), $data);
	}
	
	/**
	 * this would create encrypted token
	 * 
	 * @param mixed $id
	 * @param mixed $data
	 */
	public static function token_create($id, $data = '') {
		$result = array(
			'id' => $id,
			'data' => $data,
			'time' => time(),
			'ip' => request::ip()
		);
		$serilialized = base64_encode(serialize($result));
		$md5 = md5($serilialized . application::get(array('crypt', 'salt')));
		return self::encrypt($md5 . $serilialized);
	}
	
	/**
	 * Validate encrypted token
	 * 
	 * @param string $token
	 * @param int $valid
	 * @param boolean $check_ip
	 * @return mixed|boolean
	 */
	public static function token_validate($token, $valid = 0, $check_ip = false) {
		do {
			$token_decrypted = self::decrypt($token);
			$md5 = substr($token_decrypted, 0, 32);
			$serilialized = substr($token_decrypted, 32);
			if (empty($md5) || $md5 != md5($serilialized . application::get(array('crypt', 'salt')))) break;
			$result = unserialize(base64_decode($serilialized));
			if (empty($result['id'])) break;
			$hours = application::get(array('crypt', 'token', 'valid_hours'));
			if (empty($hours)) $hours = 2;
			if (!empty($valid)) $hours = $valid;
			if ($result['time'] + ($hours * 60 * 60) <= time()) break;
			// ip verification
			if ($check_ip && $result['ip']!=request::ip()) break;
			return $result;
		} while(0);
		return false;
	}
	
	/**
	 * Check password complexity
	 * 
	 * @param string $password
	 * @param string $conf_password
	 * @param string $username
	 * @return boolean
	 */
	public static function password_check_complexity($password, $conf_password, $username = '') {
		$settings = application::get(array('crypt', 'password'));
		do {
			// if empty, or not equal
			if (!$password || !$conf_password || $password != $conf_password) break;
			// minimum length
			if (empty($settings['min_length'])) $settings['min_length'] = 8;
			if ($settings['min_length'] && mb_strlen($password)<$settings['min_length']) break;
			// maximum length
			if (empty($settings['max_length'])) $settings['max_length'] = $settings['min_length'];
			if ($settings['max_length'] && mb_strlen($password)>$settings['max_length']) break;
			// password must have one number
			if (@$settings['one_number'] && !preg_match("/\d+/", $password)) break;
			// password must have one character
			if (@$settings['one_character']) {
				$temp = preg_replace("/[0-9]+/", "", $password);
				if (!preg_match("/\w+/", $temp)) break;
			}
			// password should not be equal username
			if ($username && $password == $username) break;
			return true;
		} while (0);
		return false;
	}
	
	/**
	 * Hash password for storing in database
	 * 
	 * @param string $password
	 * @return string
	 */
	public static function password_hash($password) {
		if (application::get(array('crypt', 'password', 'hash'))) {
			return self::hash($password) . "";
		} else {
			return $password . "";
		}
	}
	
	/**
	 * Password policy for user friendly websites
	 * 
	 * @return string
	 */
	public static function password_policy() {
		$settings = application::get(array('crypt', 'password'));
		$result = '<ul>';
			if (empty($settings['min_length'])) $settings['min_length'] = 8;
			if ($settings['min_length']) $result.= '<li>Min length: ' . $settings['min_length'] . ';</li>';
			if (empty($settings['max_length'])) $settings['max_length'] = $settings['min_length'];
			if ($settings['max_length']) $result.= '<li>Max length: ' . $settings['max_length'] . ';</li>';
			if (!empty($settings['one_number'])) $result.= '<li>Must have one number;</li>';
			if (!empty($settings['one_character'])) $result.= '<li>Must have one character;</li>';
		$result.= '</ul>';
		return $result;
	}
	
	/**
	 * Generate password complying with password policy
	 * 
	 * @param integer $length
	 * @return string
	 */
	public static function password_generate($length = 0, $include_symbols = false) {
		$settings = application::get(array('crypt', 'password'));
		if (empty($settings['min_length'])) $settings['min_length'] = 8;
		if (empty($settings['max_length'])) $settings['max_length'] = $settings['min_length'];
		if (empty($length)) $length = rand($settings['min_length'], $settings['max_length']);
		$numbers = 0;
		$caps = 0;
		$symbols = 0;
		if (!empty($settings['one_number'])) $numbers = intval($length/4);
		if (!empty($settings['one_character'])) $caps = intval($length/4);
		if ($include_symbols) $symbols = intval($length/4);
		return self::password_generate_complex($length, $caps, $numbers, $symbols);
	}
	
	/**
	 * Complex funtion for password generation
	 * 
	 * @param integer $l - length
	 * @param integer $c - # of characters
	 * @param integer $n - # of numbers
	 * @param integer $s - # of symbols
	 * @return string
	 */
	private static function password_generate_complex($l = 8, $c = 0, $n = 0, $s = 0) {
		// get count of all required minimum special chars
		$count = $c + $n + $s;
		// sanitize inputs; should be self-explanatory
		if ($c > $l) {
			$c = $l;
		} else if ($n > $l) {
			$n = $l;
		} else if ($s > $l) {
			$s = $l;
		} else if ($count > $l) {
			$count = $l;
		}
		// change these strings if you want to include or exclude possible password characters
		$chars = "abcdefghijklmnopqrstuvwxyz";
		$caps = strtoupper($chars);
		$nums = "0123456789";
		$syms = "!@#$%^&*()-+?";
		// build the base password of all lower-case letters
		$out = '';
		for ($i = 0; $i < $l; $i++) $out.= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		// create arrays if special character(s) required
		if ($count) {
			// split base password to array; create special chars array
			$tmp1 = str_split($out);
			$tmp2 = array();
			// add required special character(s) to second array
			for ($i = 0; $i < $c; $i++) array_push($tmp2, substr($caps, mt_rand(0, strlen($caps) - 1), 1));
			for ($i = 0; $i < $n; $i++) array_push($tmp2, substr($nums, mt_rand(0, strlen($nums) - 1), 1));
			for ($i = 0; $i < $s; $i++) array_push($tmp2, substr($syms, mt_rand(0, strlen($syms) - 1), 1));
			// hack off a chunk of the base password array that's as big as the special chars array
			$tmp1 = array_slice($tmp1, 0, $l - $count);
			// merge special character(s) array with base password array
			$tmp1 = array_merge($tmp1, $tmp2);
			// mix the characters up
			shuffle($tmp1);
			// convert to string for output
			$out = implode('', $tmp1);
		}
		return $out;
	}
}