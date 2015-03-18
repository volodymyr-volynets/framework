<?php

/**
 * Cache
 */
class cache {
	
	/**
	 * Available adapters
	 * 
	 * @var array
	 */
	private static $adapter_types = array(
		'file' => array('class'=>'cache_file'),		
	);
	
	/**
	 * Adapter settings
	 * 
	 * @var array
	 */
	public static $adapters = array();
	
	/**
	 * Default lifetime
	 * 
	 * @var int
	 */
	private static $default_lifetime = 7200;

	/**
	 * Get adapter information
	 * 
	 * @param string $id
	 */
	public static function adapter($id) {
		return @self::$adapters[$id];
	}
	
	/**
	 * Create an adapter
	 * 
	 * @param string $link
	 * @param array $options
	 * @return array
	 */
	public static function create($link, $options) {
		do {
			// handling cache type
			$options['type'] = strtolower($options['type']);
			if (empty($options['type'])) {
				$options['type'] = 'php';
			}
			
			// lifetime
			if (empty($options['lifetime'])) {
				$options['lifetime'] = self::$default_lifetime;
			}
			
			// handling directory
			if ($options['type'] == 'file') {
				if (empty($options['dir'])) {
					$result['error'][] = 'You must specify directory!';
					break;
				}
				$options['dir'] = rtrim($options['dir'], '/') . '/';
				if ($options['key']) $options['dir'].= $options['key'] . '/';
				// create a cache directory
				if (!file_exists($options['dir'])) mkdir($options['dir'], 0777, true);
			}
			
			// setting the adapter
			self::$adapters[$link] = $options;
			
			// and we set success in a result
			return true;
		} while(0);
		return false;
	}
	
    /**
     * Get data from cache
     * 
     * @param array $cache_id
     * @param array $link
     * @return array 
     */
    public static function get($cache_id, $link = 'default') {
    	if (empty($link)) $link = 'default';
    	if (isset(self::$adapters[$link]['type'])) {
    		$class_name = self::$adapter_types[self::$adapters[$link]['type']]['class'];
    		$class = new $class_name;
			return $class->get($cache_id, $link);
    	} else {
    		return false;
    	}
    }
    
	/**
	 * Cache data
	 * 
	 * @param string $cache_id
	 * @param mixed $data
	 * @param int $expire - seconds
	 * @param mixed $tags
	 * @param string $link
	 * @return mixed
	 */
    public static function set($cache_id, $data, $expire = null, $tags = null, $link = 'default') {
    	if (empty($link)) $link = 'default';
    	if (isset(self::$adapters[$link]['type'])) {
    		$class_name = self::$adapter_types[self::$adapters[$link]['type']]['class'];
    		$class = new $class_name;
    		$expire = $expire ? $expire : self::$adapters[$link]['lifetime'];
    		return $class->set($cache_id, $data, $expire, $tags, $link);
    	} else {
    		return false;
    	}
    }
    
    /**
     * Garbage collector
     * 
     * @param int $mode - 1 - old, 2 - all
     * @param array $tags
     * @param string $link 
     */
    public static function gc($mode = 1, $tags = array(), $link = 'default') {
    	if (!isset($link)) $link = 'default';
        if (isset(self::$adapters[$link]['type'])) {
    		$class_name = self::$adapter_types[self::$adapters[$link]['type']]['class'];
    		$class = new $class_name;
			return $class->gc($mode, $tags, $link);
    	} else {
    		return false;
    	}
    }
}