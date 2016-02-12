<?php

class url_tinyurl implements numbers_backend_misc_tinyurl_interface_base {

	/**
	 * Get url
	 *
	 * @param string $hash
	 * @return array
	 */
	public static function get($hash) {
		return factory::delegate('flag.global.tinyurl', 'get', [$hash]);
	}

	/**
	 * Set url
	 *
	 * @param string $url
	 * @param array $options
	 *		expires - datetime when url expires
	 * @return array
	 */
	public static function set($url, $options = []) {
		return factory::delegate('flag.global.tinyurl', 'set', [$url, $options]);
	}
}