<?php

class cache_file implements cache_interface {
	
	private static $file_prefix = 'cache';
	
	private static $file_cookie = 'cookie'; 
	
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
			$cookie_name = $adapter['dir'] . self::$file_prefix . '--' . self::$file_cookie . '--' . $cache_id;
			$data_name = $adapter['dir'] . self::$file_prefix . '--' . $cache_id;
			if (!file_exists($cookie_name)) {
				break;
			}
			// reading cookie
			$cookie = unserialize(file::read($cookie_name));
			
			// if cookie expired we destroy the cookie
			if (time() > $cookie['expire']) {
				unlink($cookie_name);
				unlink($data_name);
				break;
			}

			// returning unserialized content
			return @unserialize(file::read($data_name));
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

			// converting tags to an array
			$tags = array_fix($tags);
			
			// writing data first
			$data_name = $adapter['dir'] . self::$file_prefix . '--' . $cache_id;
			file::write($data_name, serialize($data));
			
			// data for cookie
			$time = time();
			$cookie_data = array(
				'time' => $time,
				'expire' => $time + $expire,
				'tags' => $tags,
				'file' => $data_name
			);
			
			// writing cookie
			$cookie_name = $adapter['dir'] . self::$file_prefix . '--' . self::$file_cookie . '--' . $cache_id;
			file::write($cookie_name, serialize($cookie_data));
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
		$adapter = cache::adapter($id);
		
		$cookies = @glob($adapter['dir'] . self::$file_prefix . '--' . self::$file_cookie . '--*');
		if ($cookies === false) {
			return true;
		}
		$time = time();
		foreach ($cookies as $file)  {
			
			$flag_delete = false;
			do {
				if (!is_file($file)) break;
				
				$fileName = basename($file);
				$cookie = unserialize(file::read($file));
				
				if ($mode == 2) {
					$flag_delete = true;
					break;
				}

				// processing tags
				if ($tags) {
					$tags= array_fix($tags);
					if (array_intersect($tags, $cookie['tags'])) {
						$flag_delete = true;
						break;
					}
				}
				
				// if file expired
				if ($time > $cookie['expire']) {
					$flag_delete = true;
					break;
				}
			} while(0);

			// if we need to delete
			if ($flag_delete) {
				unlink($file);
				unlink($cookie['file']);
			}
		}
	}
}