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
		return $decrypted;
	}

	/**
	 * Generate a hash
	 * 
	 * @param string $data
	 * @return string
	 */
	public static function hash($data) {
		$method = application::get(array('crypt', 'hash'));
		if ($method == 'md5' || $method == 'sha1') {
			return $method($data);
		} else {
			return hash(($method ? $method : 'md5'), $data);
		}
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
			$md5 = mb_substr($token_decrypted, 0, 32, 'latin1');
			$serilialized = mb_substr($token_decrypted, $iv_size, mb_strlen($token_decrypted, 'latin1'), 'latin1');
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
}