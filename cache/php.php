<?php

class cache_php implements cache_interface {
	
	/**
	 * Get data from cache
	 * 
	 * @param string $cache_id
	 * @param string $id
	 * @return mixed
	 */
	public static function get($cache_id, $id) {
		do {
			$adapter = cache::adapter($id);
			$data_name = $adapter['dir'] . $cache_id;
			if (!file_exists($data_name)) break;
			
			// reading cookie
			$time = filemtime($data_name);
			
			// if cookie expired we destroy the cookie
			if (time() > ($time + $adapter['lifetime'])) {
				unlink($data_name);
				break;
			}
			
			// returning required content
			require($data_name);
			return $__cache_php_data_name;
		} while(0);
		return false;
	}
	
	/**
	 * Put data into cache
	 * 
	 * @param string $cache_id
	 * @param mixed $data
	 * @param int $expire
	 * @param mixed $tags
	 * @param string $id
	 * @return boolean
	 */
	public static function set($cache_id, $data, $expire, $tags, $id) {
		$adapter = cache::adapter($id);
		if ($adapter) {
			// writing data first
			$data_name = $adapter['dir'] . $cache_id;
			file::write($data_name, '<?php $__cache_php_data_name = ' . var_export($data, true) . '; ?>');
			return true;
		}
		return false;
	}
	
	/**
	 * Garbage collector
	 *
	 * @param int $mode - 1 - old, 2 - all
	 * @param array $tags
	 * @param string $from
	 */
	public static function gc($mode, $tags, $id) {
		// there's no need for gc in this implementation
	}
}