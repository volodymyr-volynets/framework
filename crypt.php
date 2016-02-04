<?php

class crypt implements numbers_backend_crypt_interface_base {

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
		if ($crypt_link == null) {
			$crypt_link = application::get(['flag', 'global', 'crypt', 'default_crypt_link']);
			if (empty($crypt_link)) {
				Throw new Exception('You must specify crypt link!');
			}
		}
		// get object from factory
		$temp = factory::get(['crypt', $crypt_link]);
		// if we have class
		if (!empty($class) && !empty($crypt_link)) {
			// replaces in case we have it as submodule
			$class = str_replace('.', '_', trim($class));
			// creating new class
			unset($this->object);
			$this->object = new $class($crypt_link, $options);
			factory::set(['crypt', $crypt_link], ['object' => $this->object, 'class' => $class]);
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
	public function hash_file($path) {
		return $this->object->hash_file($data);
	}

	/**
	 * Create token
	 *
	 * @param string $id
	 * @param mixed $data
	 * @return string - erlencoded
	 */
	public function token_create($id, $data = null){
		return $this->object->token_create($id, $data);
	}

	/**
	 * Validate token
	 *
	 * @param string $token - urldecoded
	 * @return array or false on error
	 */
	public function token_validate($token) {
		return $this->object->token_validate($token);
	}
}