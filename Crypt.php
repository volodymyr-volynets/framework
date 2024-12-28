<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\Traits\ObjectableAndStaticable;

/**
 * @method static string nanoCreateStatic(int $id) Create a nano token
 * @method static bool|array nanoValidateStatic(?string $token) Validate token
 * @method static array nanoVerifyStatic(?string $token) Verify nano token
 */
class Crypt
{
    use ObjectableAndStaticable;

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
     * @param array $options
     */
    public function __construct($crypt_link = null, $class = null, $options = [])
    {
        // if we need to use default link from application
        if ($crypt_link === null) {
            $crypt_link = Application::get('flag.global.default_crypt_link');
            if (empty($crypt_link)) {
                throw new Exception('You must specify crypt link!');
            }
        }
        // get object from factory
        $temp = Factory::get(['crypt', $crypt_link]);
        // if we have class
        if (!empty($class) && !empty($crypt_link)) {
            // replaces in case we have it as submodule
            $class = str_replace('.', '_', trim($class));
            // creating new class
            unset($this->object);
            $this->object = new $class($crypt_link, $options);
            Factory::set(['crypt', $crypt_link], ['object' => $this->object, 'class' => $class]);
        } elseif (!empty($temp['object'])) {
            $this->object = $temp['object'];
        } else {
            throw new Exception('You must specify crypt link and/or class!');
        }
    }

    /**
     * Encrypting data (URL safe)
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        return $this->object->encrypt($data);
    }

    /**
     * Decrypting data (URL safe)
     *
     * @param string $data
     * @return string or false on error
     */
    public function decrypt($data)
    {
        return $this->object->decrypt($data);
    }

    /**
     * Generate a hash of a value
     *
     * @param string $data
     * @return string
     */
    public function hash($data)
    {
        return $this->object->hash($data);
    }

    /**
     * Generate has of a file
     *
     * @param string $path
     * @return string
     */
    public function hashFile($path)
    {
        return $this->object->hash_file($data);
    }

    /**
     * Create token
     *
     * @param string $id
     * @param string $token
     * @param mixed $data
     * @param array $options
     * @return string - urlencoded
     */
    public function tokenCreate($id, $token = null, $data = null, $options = [])
    {
        return $this->object->tokenCreate($id, $token, $data, $options);
    }

    /**
     * Validate token
     *
     * @param string $token - urldecoded
     * @param array $options
     *		boolean skip_time_validation
     * @return array or false on error
     */
    public function tokenValidate($token, $options = [])
    {
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
    public function tokenVerify($token, $tokens, $options = [])
    {
        return $this->object->tokenVerify($token, $tokens, $options);
    }

    /**
     * Hash password
     *
     * @param string $password
     * @return string
     */
    public function passwordHash($password)
    {
        return $this->object->passwordHash($password);
    }

    /**
     * Verify password
     *
     * @param string $password
     * @param string $hash
     * @return boolean
     */
    public function passwordVerify($password, $hash)
    {
        return $this->object->passwordVerify($password, $hash);
    }

    /**
     * Compress data
     *
     * @param string $data
     * @return string or false on error
     */
    public function compress($data)
    {
        return $this->object->compress($data);
    }

    /**
     * Uncompress data
     *
     * @param string $data
     * @return string or false on error
     */
    public function uncompress($data)
    {
        return $this->object->uncompress($data);
    }

    /**
     * Bearer authorization token create
     *
     * @param string $type
     * @param int|null $user_id
     * @param int|null $tenant_id
     * @param string|null $ip
     * @param string|null $session_id
     * @return string
     */
    public function bearerAuthorizationTokenCreate(string $type = 'REG', ?int $user_id = null, ?int $tenant_id = null, ?string $ip = null, ?string $session_id = null): string
    {
        return $this->object->bearerAuthorizationTokenCreate($type, $user_id, $tenant_id, $ip, $session_id);
    }

    /**
     * Bearer authorization token validate
     *
     * @param string $token
     * @return bool
     */
    public function bearerAuthorizationTokenValidate(string $token): bool
    {
        return $this->object->bearerAuthorizationTokenValidate($token);
    }

    /**
     * Bearer authorization token decode
     *
     * @param string $token
     * @return array
     */
    public function bearerAuthorizationTokenDecode(string $token): array
    {
        return $this->object->bearerAuthorizationTokenDecode($token);
    }

    /**
     * Generate password string
     *
     * @param int $length
     * @param string $characters
     * @param array $options
     * 		bool as_array
     * @return string|array
     */
    public function passwordStringGenerate(int $length = 12, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', array $options = []): string|array
    {
        return $this->object->passwordStringGenerate($length, $characters);
    }

    /**
     * Generate password as per policy
     *
     * @param int $length
     * @param array $options
     *		int uppercase
     *		int special
     *		int number
     *		int lowercase - is computed
     *		bool as_array
     */
    public function passwordPolicyGenerate(int $length, array $options = []): string|array
    {
        return $this->object->passwordPolicyGenerate($length, $options);
    }

    /**
     * JSON Web Token create (JWT)
     *
     * @param int $id
     * @param string $token
     * @param array|string|null $data
     * @param array $options
     * @return string
     */
    public function jwtCreate(int $id, string $token, array|string|null $data, array $options = []): string
    {
        return $this->object->jwtCreate($id, $token, $data, $options);
    }

    /**
     * JSON Web Token Validate
     *
     * @param string|null $token
     * @return bool|array
     */
    public function jwtValidate(?string $token): bool|array
    {
        return $this->object->jwtValidate($token);
    }

    /**
     * JSON Web Token Verify
     *
     * @param string|null $token
     * @param array $tokens
     * @return array
     */
    public function jwtVerify(?string $token, array $tokens): array
    {
        return $this->object->jwtVerify($token, $tokens);
    }

    /**
     * Micro Token Create
     *
     * @param int $id
     * @param string $token
     * @return string
     */
    public function microCreate(int $id, string $token)
    {
        return $this->object->microCreate($id, $token);
    }

    /**
     * Micro Token Validate
     *
     * @param string|null $token
     * @return bool|array
     */
    public function microValidate(?string $token): bool|array
    {
        return $this->object->microValidate($token);
    }

    /**
     * Micro Token Verify
     *
     * @param string|null $token
     * @param array $tokens
     * @return array
     */
    public function microVerify(?string $token, array $tokens): array
    {
        return $this->object->microVerify($token, $tokens);
    }

    /**
     * Nano Token Create
     *
     * @param int $id
     * @return string
     */
    public function nanoCreate(int $id): string
    {
        return $this->object->nanoCreate($id);
    }

    /**
     * Nano Token Validate
     *
     * @param string|null $token
     * @return bool|array
     */
    public function nanoValidate(?string $token): bool|array
    {
        return $this->object->nanoValidate($token);
    }

    /**
     * Nano Token Verify
     *
     * @param string|null $token
     * @return array
     */
    public function nanoVerify(?string $token): array
    {
        return $this->object->nanoVerify($token);
    }
}
