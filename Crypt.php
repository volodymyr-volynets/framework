<?php

class Crypt {

	/**
	 * Crypt object
	 *
	 * @var object
	 */
	public $object;

	/**
	 * Constructing crypt object
	 *
	 * @param string $db_link
	 * @param string $class
	 */
	public function __construct($crypt_link = null, $class = null, $options = []) {
		// if we need to use default link from application
		if ($crypt_link === null) {
			$crypt_link = \Application::get('flag.global.default_crypt_link');
			if (empty($crypt_link)) {
				Throw new Exception('You must specify crypt link!');
			}
		}
		// get object from factory
		$temp = \Factory::get(['crypt', $crypt_link]);
		// if we have class
		if (!empty($class) && !empty($crypt_link)) {
			// replaces in case we have it as submodule
			$class = str_replace('.', '_', trim($class));
			// creating new class
			unset($this->object);
			$this->object = new $class($crypt_link, $options);
			\Factory::set(['crypt', $crypt_link], ['object' => $this->object, 'class' => $class]);
		} else if (!empty($temp['object'])) {
			$this->object = $temp['object'];
		} else {
			Throw new Exception('You must specify crypt link and/or class!');
		}
	}

	/**
	 * Encrypting data (URL safe)
	 *
	 * @param string $data
	 * @return string
	 */
	public function encrypt($data) {
		return $this->object->encrypt($data);
	}

	/**
	 * Decrypting data (URL safe)
	 * 
	 * @param string $data
	 * @return string or false on error
	 */
	public function decrypt($data) {
		return $this->object->decrypt($data);
	}

	/**
	 * Generate a hash of a value
	 *
	 * @param string $data
	 * @return string
	 */
	public function hash($data) {
		return $this->object->hash($data);
	}

	/**
	 * Generate has of a file
	 *
	 * @param string $path
	 * @return string
	 */
	public function hashFile($path) {
		return $this->object->hash_file($data);
	}

	/**
	 * Create token
	 *
	 * @param string $id
	 * @param string $token
	 * @param mixed $data
	 * @return string - erlencoded
	 */
	public function tokenCreate($id, $token = null, $data = null) {
		return $this->object->tokenCreate($id, $token, $data);
	}

	/**
	 * Validate token
	 *
	 * @param string $token - urldecoded
	 * @param array $options
	 *		boolean skip_time_validation
	 * @return array or false on error
	 */
	public function tokenValidate($token, $options = []) {
		return $this->object->tokenValidate($token, $options);
	}

	/**
	 * Verify token
	 *
	 * @param string $token - urldecoded
	 * @param array $tokens
	 * @param array $options
	 *	boolean skip_time_validation
	 * @return array
	 */
	public function tokenVerify($token, $tokens, $options = []) {
		return $this->object->tokenVerify($token, $tokens, $options);
	}

	/**
	 * Hash password
	 *
	 * @param string $password
	 * @return string
	 */
	public function passwordHash($password) {
		return $this->object->passwordHash($password);
	}

	/**
	 * Verify password
	 *
	 * @param string $password
	 * @param string $hash
	 * @return boolean
	 */
	public function passwordVerify($password, $hash) {
		return $this->object->passwordVerify($password, $hash);
	}

	/**
	 * Compress data
	 *
	 * @param string $data
	 * @return string or false on error
	 */
	public function compress($data) {
		return $this->object->compress($data);
	}

	/**
	 * Uncompress data
	 *
	 * @param string $data
	 * @return string or false on error
	 */
	public function uncompress($data) {
		return $this->object->uncompress($data);
	}

	/**
	 * Bearer authorization token create
	 *
	 * @param int|null $user_id
	 * @param int|null $tenant_id
	 * @param string|null $ip
	 * @param string|null $session_id
	 * @return string
	 */
	public function bearerAuthorizationTokenCreate(?int $user_id = null, ?int $tenant_id = null, ?string $ip = null, ?string $session_id = null) : string {
		return $this->object->bearerAuthorizationTokenCreate($user_id, $tenant_id, $ip, $session_id);
	}

	/**
	 * Bearer authorization token validate
	 *
	 * @param string $token
	 * @return bool
	 */
	public function bearerAuthorizationTokenValidate(string $token) : bool {
		return $this->object->bearerAuthorizationTokenValidate($token);
	}

	/**
	 * Bearer authorization token decode
	 *
	 * @param string $token
	 * @return array
	 */
	public function bearerAuthorizationTokenDecode(string $token) : array {
		return $this->object->bearerAuthorizationTokenDecode($token);
	}
}