<?php

namespace Object\ACL;
abstract class Registered {

	/**
	 * Models
	 *
	 * @var array
	 */
	public $models = [
		//'\Model' => []
	];

	/**
	 * Cached models
	 *
	 * @var array
	 */
	public static $cached_models;

	/**
	 * Execute
	 *
	 * @param \Numbers\Backend\Db\Common\Query\Builder $query
	 */
	abstract public function execute(\Numbers\Backend\Db\Common\Query\Builder & $query, array $options = []);

	/**
	 * Can
	 *
	 * @param string $model
	 * @return boolean
	 */
	public static function can(string $model) : bool {
		// load models
		if (!isset(self::$cached_models)) {
			$file = './Overrides/Class/Override_Object_ACL_Registered.php';
			if (file_exists($file)) {
				require($file);
				self::$cached_models = $object_override_blank_object;
			} else {
				self::$cached_models = [];
			}
		}
		return !empty(self::$cached_models[$model]);
	}

	/**
	 * Process
	 *
	 * @param string $model
	 * @param \Numbers\Backend\Db\Common\Query\Builder $query
	 * @return boolean
	 */
	public static function process(string $model, \Numbers\Backend\Db\Common\Query\Builder & $query, array $options = []) {
		if (!self::can($model)) return false;
		foreach (self::$cached_models[$model] as $k => $v) {
			$object = new $k();
			$object->execute($query, $options);
		}
		return true;
	}
}