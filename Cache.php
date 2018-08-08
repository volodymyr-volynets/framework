<?php

class Cache {

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
	 * @param array $options
	 */
	public function __construct($cache_link = null, $class = null, $options = []) {
		// if we need to use default link from application
		if (empty($cache_link)) {
			$cache_link = Application::get('flag.global.default_cache_link');
			if (empty($cache_link)) {
				Throw new Exception('You must specify cache link and/or class!');
			}
		}
		// get object from factory
		$temp = \Factory::get(['cache', $cache_link]);
		// if we have class
		if (!empty($class) && !empty($cache_link)) {
			// check if backend has been enabled
			if (!\Application::get($class, ['submodule_exists' => true])) {
				Throw new Exception('You must enable ' . $class . ' first!');
			}
			// replaces in case we have it as submodule
			$class = str_replace('.', '_', trim($class));
			// if we are replacing database connection with the same link we
			// need to manually close connection
			if (!empty($temp['object']) && $temp['class'] != $class) {
				$temp['object']->close();
				unset($this->object);
			}
			$this->object = new $class($cache_link, $options);
			// putting every thing into factory
			\Factory::set(['cache', $cache_link], [
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
		// for deployed code the directory is different because we relate it based on code
		if (!empty($options['dir']) && Application::isDeployed() && $options['dir'][0] != '/') {
			if ($options['dir'][0] . $options['dir'][1] == './') {
				$options['dir'] = './.' . $options['dir'];
			} else {
				$options['dir'] = '../' . $options['dir'];
			}
		}
		return $this->object->connect($options);
	}

	/**
	 * Close
	 *
	 * @return array
	 */
	public function close() {
		// run gc in 1% cases to clean old caches
		if (chance(1)) {
			$this->gc(1);
		}
		return $this->object->close();
	}

	/**
	 * Get
	 *
	 * @param string $cache_id
	 * @param boolean $return_data_only
	 * @return mixed
	 */
	public function get($cache_id, $return_data_only = false) {
		$result = $this->object->get($cache_id);
		// if we are debugging
		if (\Debug::$debug) {
			\Debug::$data['cache'][] = array(
				'type' => 'get',
				'link' => $this->object->cache_link,
				'cache_id' => $cache_id,
				'have_data' => $result['success']
			);
			// todo: log errors
		}
		// if we need to return data
		if ($return_data_only) {
			if ($result['success']) {
				return $result['data'];
			} else {
				return false;
			}
		} else {
			return $result;
		}
	}

	/**
	 * Set
	 *
	 * @param string $cache_id
	 * @param mixed $data
	 * @param int $expire
	 * @param array $tags
	 * @return array
	 */
	public function set($cache_id, $data, $expire = null, $tags = []) {
		$result = $this->object->set($cache_id, $data, $expire, $tags);
		// if we are debugging
		if (\Debug::$debug) {
			\Debug::$data['cache'][] = array(
				'type' => 'set',
				'link' => $this->object->cache_link,
				'cache_id' => $cache_id,
				'have_data' => $result['success']
			);
			// todo: log errors
		}
		return $result;
	}

	/**
	 * Garbage collector
	 *
	 * @param int $mode
	 *		1 - old
	 *		2 - all
	 *		3 - tag
	 * @param array $tags
	 *		array of arrays of tags
	 * @return array
	 */
	public function gc($mode = 1, $tags = []) {
		return $this->object->gc($mode, $tags);
	}

	/**
	 * Connect to servers
	 *
	 * @param string $cache_link
	 * @param array $cache_settings
	 * @return array
	 */
	public static function connectToServers(string $cache_link, array $cache_settings) : array {
		$result = [
			'success' => false,
			'error' => []
		];
		foreach ($cache_settings['servers'] as $cache_server) {
			$cache_object = new \Cache($cache_link, $cache_settings['submodule'], $cache_settings);
			$cache_status = $cache_object->connect($cache_server);
			if ($cache_status['success']) {
				$result['success'] = true;
				return $result;
			}
		}
		$result['error'][] = 'Unable to open cache connection!';
		return $result;
	}
}