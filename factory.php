<?php

class factory {

	/**
	 * A list of class objects
	 *
	 * @var array
	 */
	public static $class_objects = [];

	/**
	 * Add class to factory
	 *
	 * @param mixed $key
	 * @param object $class_object
	 */
	public static function set($key, $class_object) {
		array_key_set(self::$class_objects, $key, $class_object);
	}

	/**
	 * Get class object
	 *
	 * @param mixed $key
	 * @return object
	 */
	public static function get($key) {
		return array_key_get(self::$class_objects, $key);
	}

	/**
	 * Get submodule object
	 *	- implementation of Factory Method pattern and Simple Factory pattern
	 *
	 * @param string $submodule
	 * @return object
	 * @throws Exception
	 */
	public static function submodule($submodule) {
		if (isset(self::$class_objects['submodule'][$submodule])) {
			return self::$class_objects['submodule'][$submodule];
		} else {
			$class = application::get($submodule, ['class' => 1]);
			if (empty($class)) {
				Throw new Exception("You must indicate $submodule submodule!");
			}
			self::$class_objects['submodule'][$submodule] = new $class();
			return self::$class_objects['submodule'][$submodule];
		}
	}

	/**
	 * Delegate arguments to submodule
	 *
	 * @param string $flag
	 * @param string $submodule
	 * @param array $arguments
	 * @return mixed
	 */
	public static function delegate($flag, $submodule, $arguments) {
		$options = application::get($flag . '.' . $submodule . '.options');
		if (!empty($options)) {
			// todo: maybe add to first array instead to first element in arguments
			$arguments[0] = array_merge_hard($options, $arguments[0]);
		}
		// we need to determine whether we need to use additional submodule
		if (application::get($flag . '.' . $submodule . '.submodule') && empty($arguments[0]['flag_call_previous_parent'])) {
			return call_user_func_array([self::submodule($flag . '.' . $submodule . '.submodule'), $submodule], $arguments);
		} else {
			return call_user_func_array([self::submodule($flag . '.submodule'), $submodule], $arguments);
		}
	}

	/**
	 * Create model
	 *
	 * @param string $class
	 * @param boolean $cache
	 * @param array $options
	 * @return object
	 */
	public static function model($class, $cache = false, $options = null) {
		// fix dot notation
		$class = str_replace('.', '_', $class);
		// if we need to override classes
		if (isset(overrides_factory::$data[$class])) {
			$class = overrides_factory::$data[$class];
		}
		// if we are not caching
		if (!$cache) goto no_cache;
		// try to find objects in the cache
		if (isset(self::$class_objects['model'][$class])) {
			$object = & self::$class_objects['model'][$class];
		} else {
			// process virtual models
			if (strpos($class, '__virtual__') !== false) {
				self::$class_objects['model'][$class] = object_virtual_models::model($class);
			} else {
no_cache:
				// if we need to pass options to an object
				if (isset($options)) {
					self::$class_objects['model'][$class] = new $class($options);
				} else {
					self::$class_objects['model'][$class] = new $class();
				}
			}
			$object = & self::$class_objects['model'][$class];
		}
		return $object;
	}

	/**
	 * Convert method string to an array for future execution
	 *
	 * @param string $method
	 * @param string $base_class
	 * @return array
	 */
	public static function method($method, $base_class = null, $model = false) {
		$temp = explode('::', $method);
		if (count($temp) > 1) {
			$temp_model = $temp[0];
			$temp_method = $temp[1];
		} else {
			$temp_model = $base_class;
			$temp_method = $temp[0];
		}
		if ($model) {
			$temp_model = factory::model($temp_model, true);
		}
		return [& $temp_model, $temp_method];
	}
}