<?php

/**
 * Cache
 */
class cache {

	/**
	 * Cache object
	 *
	 * @var object
	 */
	public $object;

	/**
	 * Memory caches would be kept here as static
	 *
	 * @var array
	 */
	public static $memory_storage = [];

	/**
	 * Reset caches
	 *
	 * @var array
	 */
	public static $reset_caches = [];

	/**
	 * Constructing cache object
	 *
	 * @param string $cache_link
	 * @param string $class
	 */
	public function __construct($cache_link = null, $class = null) {
		// if we need to use default link from application
		if (empty($cache_link)) {
			$cache_link = application::get(['flag', 'global', 'cache', 'default_cache_link']);
			if (empty($cache_link)) {
				Throw new Exception('You must specify cache link and/or class!');
			}
		}
		// get object from factory
		$temp = factory::get(['cache', $cache_link]);
		// if we have class
		if (!empty($class) && !empty($cache_link)) {
			// replaces in case we have it as submodule
			$class = str_replace('.', '_', trim($class));
			// if we are replacing database connection with the same link we
			// need to manually close connection
			if (!empty($temp['object']) && $temp['class'] != $class) {
				$object = $temp['object'];
				$object->close();
				unset($this->object);
			}
			$this->object = new $class($cache_link);
			// putting every thing into factory
			factory::set(['cache', $cache_link], [
				'object' => $this->object,
				'class' => $class
			]);
		} else if (!empty($temp['object'])) {
			$this->object = $temp['object'];
		} else {
			Throw new Exception('You must specify cache link and/or class!');
		}
	}

	/**
	 * Connect
	 *
	 * @param array $options
	 * @return array
	 */
	public function connect($options) {
		return $this->object->connect($options);
	}

	/**
	 * Close
	 *
	 * @return array
	 */
	public function close() {
		return $this->object->close();
	}

	/**
	 * Get data from cache
	 *
	 * @param string $cache_id
	 * @return mixed
	 */
	public function get($cache_id) {
		$data = $this->object->get($cache_id);
		// if we are debugging
		if (debug::$debug) {
			debug::$data['cache'][] = array(
				'type' => 'get',
				'link' => $this->object->cache_link,
				'cache_id' => $cache_id,
				'have_data' => ($data !== false)
			);
		}
		return $data;
	}

	/**
	 * Set cache
	 *
	 * @param string $cache_id
	 * @param mixed $data
	 * @param mixed $tags
	 * @param int $expire
	 * @return bool
	 */
	public function set($cache_id, $data, $tags = [], $expire = null) {
		$data = $this->object->set($cache_id, $data, $tags, $expire);
		// if we are debugging
		if (debug::$debug) {
			debug::$data['cache'][] = array(
				'type' => 'set',
				'link' => $this->object->cache_link,
				'cache_id' => $cache_id,
				'have_data' => ($data !== false)
			);
		}
		return $data;
	}

	/**
	 * Collect garbage
	 *
	 * @param string $mode
	 *		1 - old
	 *		2 - all
	 * @param array $tags
	 * @return bool
	 */
	public function gc($mode = 1, $tags = []) {
		return $this->object->gc($mode, $tags);
	}
}