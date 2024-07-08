<?php

namespace Object\Traits;
trait ObjectableAndStaticable {

	/**
	 * Call (objectee)
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		if (method_exists($this, $name . 'AsObject')) {
			return call_user_func_array([$this, $name . 'AsObject'], $arguments);
		} else {
			return call_user_func_array([self::class, $name], $arguments);
		}
    }

	/**
	 * Call (static)
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 * @throws Exception
	 */
	public static function __callStatic($name, $arguments) {
		$class = get_called_class();
		if (str_ends_with($name, 'Static')) {
			$name = str_replace('Static', '', $name);
			if (is_class_method_exists($class, $name, 'static')) {
				return call_user_func_array([$class, $name], $arguments);
			}
		}
		$object = new $class();
		return call_user_func_array([$object, $name], $arguments);
	}
}