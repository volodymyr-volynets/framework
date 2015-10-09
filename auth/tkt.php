<?php

class auth_tkt {

	/**
	 * Determine whether we need to encrypt user/data/token do not change it to
	 * true unless you have reversal of encode function which is currently not available
	 * 
	 * @var boolean
	 */
	public static $encrypt_cookie = false;

	/**
	 * $result = getTKTHash( $ip, $user, $tokens, $data, $key, [, $base64 [, $ts [, $forcetokens]]] );
	 * Returns a string that contains the signed cookie. The cookie includes the ip address of the user, 
	 * the user UID, the tokens, the user data and a time stamp. The cookie can be
	 * optionnally base64 encoded. The data is also crypted with the encode() function.
	 * Usage on the same domain:
	 *      $hash = getTKTHash("0.0.0.0","username","","",getSecretKey(),false,"');
	 *      setcookie("AuthTkt",$hash,time() + (86400 * 30),"/","");
	 * If between 2 diferent domains send cookies in URL as AuthTkt=...
	 * 
	 * @param string $ip
	 * @param string $user
	 * @param string $tokens
	 * @param string $data
	 * @param string $key
	 * @param boolean $base64
	 * @param integer $ts
	 * @param boolean $forcetokens
	 * @return string
	 */
	public static function getTKTHash($ip, $user, $tokens, $data, $key, $base64 = false, $ts = 0, $forcetokens = false) {
		// set the timestamp to now unless a time is specified
		if (empty($ts)) {
			$ts = time(); 
		}
		if (empty($ip)) {
			$ipts = pack("NN", 0, $ts);
		} else {
			$ipts = pack("NN", ip2long($ip), $ts);
		}
		// cookie signature
		$digest0 = md5($ipts . $key . $user . "\0" . $tokens . "\0" . $data);
		$digest = md5($digest0 . $key);
		if (!empty($tokens) or $forcetokens) {
			$tkt = sprintf("%s%08x%s!%s!%s", $digest, $ts,
							self::encode($user, $ts, 0, $key),
							self::encode($tokens, $ts, 4, $key),
							self::encode($data, $ts, 8, $key));
		} else {
			$tkt = sprintf("%s%08x%s!%s", $digest, $ts,
							self::encode( $user, $ts, 0, $key),
							self::encode( $data, $ts, 8, $key));
		}
		if ($base64) {
			return base64_encode($tkt);
		} else {
			return $tkt;
		}
	}

	/**
	 * Function to get all parameters from the auth_tkt hash
	 * 
	 * @param string $auth_tkt
	 * @param boolean $base64_encoded
	 * @return array
	 */
	public static function decodeTKTHash($auth_tkt, $base64_encoded = false) {
		$auth_tkt = $base64_encoded? base64_decode($auth_tkt) : $auth_tkt;
		// 1st parameter comes from md5 which returns the hash as a 32-character hexadecimal number.
		$result["digest"] = substr($auth_tkt, 0, 32);
		// %08x in sprintf - 8 digit timestamp
		$result["ts"] = hexdec(substr($auth_tkt, 32, 8));
		// explode the rest
		$tmptmp = explode("!", substr($auth_tkt, 40, (sizeof($auth_tkt)>40)? sizeof($auth_tkt) : 40));
		$result["user"] = @$tmptmp[0];
		if (sizeof($tmptmp)==3) {
			$result["token"] = @$tmptmp[1];
			$result["data"] = @$tmptmp[2];
		} else {
			$result["token"] = "";
			$result["data"] = @$tmptmp[1];
		}
		return $result;
	}

	/**
	 * Returns a "crypted" version of the data. The length of the data is
	 * unchanged.
	 *
	 * The encryption is deactivated (the function simply returns the
	 * string unencrypted) if the configuration variable $ENCRYPT_COOKIE
	 * is not set to TRUE.

	 * The function implements a encryption algorithm that substitutes
	 * each character for another one using a key to compute the shift
	 * value. The key is generated from a hash of the timestamp of the
	 * cookie and the secret key. This key is used from the offset
	 * specified. This algorithm is reversed in the mod_auth_tkt apache
	 * module before using the data. This may not be strictly
	 * cryptographically secure, but should provide sufficient protection
	 * for the personnal data included in the cookie.
	 * 
	 * @param string $data
	 * @param integer $timestamp
	 * @param integer $offset
	 * @param string $secretkey
	 * @return string
	 */
	private static function encode($data, $timestamp, $offset, $secretkey) {
		// check if encryption is activated
		if (!self::$encrypt_cookie) {
			return $data;
		}
		$CHARS_TO_ENCODE = " abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-.:";
		$LENGTH = strlen($CHARS_TO_ENCODE);
		$md5key = md5($timestamp . $secretkey);
		$encoded = '';
		// encode the data one character at a time
		for ($i = 0; $i < strlen($data); $i++) {
			$pos = strpos( $CHARS_TO_ENCODE, $data{$i} );
			// skip characters that are not in list to encode
			if( $pos === FALSE ) {
				$encoded .= $data{$i};
			} else {
				$newPos = ($pos + (hexdec($md5key{($offset + $i)%strlen($md5key)})*7)) % $LENGTH; 
				$encoded .= $CHARS_TO_ENCODE{$newPos};
			}
		}
		return $encoded;
	}
}