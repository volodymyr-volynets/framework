<?php

namespace Object;

/**
 * @method $this get($options = []) : array
 * @method $this loadById(int|array $id, array|string $options = []) : \Object\ActiveRecord
 * @method $this find(int|array $id, array|string $options = []) : \Object\ActiveRecord
 */
class ActiveRecord {

	/**
	 * Include common trait
	 */
	use \Object\Traits\Debugable;
	use \Object\Traits\Stringable;
	use \Object\Traits\ObjectableAndStaticable;

	/**
	 * @var string
	 */
	protected string $object_table_class;

	/**
	 * @var \Object\Table
	 */
	protected \Object\Table $object_table_object;

	/**
	 * @var array
	 */
	public array $object_table_pk = [];

	/**
	 * @var array
	 */
	protected array $object_table_log = [];

	/**
	 * Query builder
	 *
	 * @param array $options
	 *		string alias, default a
	 *		boolean skip_tenant
	 *		boolean skip_acl
	 *		mixed existing_values
	 * @return \Object\Query\Builder
	 */
	public function queryBuilder(array $options = []) : \Object\Query\Builder {
		return $this->object_table_object->queryBuilder($options);
	}

	/**
	 * Query builder (static)
	 *
	 * @param array $options
	 *		string alias, default a
	 *		boolean skip_tenant
	 *		boolean skip_acl
	 * @return \Object\Query\Builder
	 */
	public static function queryBuilderStatic(array $options = []) : \Object\Query\Builder {
		$class = get_called_class();
		$model = new $class();
		return $model->queryBuilder($options);
	}

	/**
	 * Get data as an array of rows
	 *
	 * @param array $options
	 *		cache - if we cache
	 *		no_cache - if we need to skip caching
	 *		search - array of search condition
	 *		where - array of where conditions
	 *		orderby - array of columns to sort by
	 *		pk - primary key to be used by query
	 *		columns - if we need to get certain columns
	 *		limit - set this integer if we need to limit query
	 *		skip_tenant - if we need to skip tenant
	 * @return array
	 */
	public static function get($options = []) {
		$class = get_called_class();
		$object = new $class();
		$options['cast_to_class'] = $class;
		return call_user_func_array([$object->object_table_class, 'get'], [$options]);
	}

	/**
	 * Load by id (static)
	 *
	 * @param int|array $id
	 * @param array|string $options
	 * 		column - column name
	 * @return \Object\ActiveRecord
	 */
	public static function loadById(int|array $id, array|string $options = []) : \Object\ActiveRecord {
		$class = get_called_class();
		$object = new $class();
		$options['cast_to_class'] = $class;
		return call_user_func_array([$object->object_table_class, 'loadById'], [$id, $options]);
	}

	/**
	 * Find (static)
	 *
	 * Alias for $this->loadById();
	 *
	 * @param int|array $id
	 * @param array|string $options
	 * 		column - column name
	 * @return \Object\ActiveRecord
	 */
	public static function find(int|array $id, array|string $options = []) : \Object\ActiveRecord {
		return self::loadByIdStatic($id, $options);
	}

	/**
	 * Compare
	 *
	 * @param string $type
	 * 		pk - primary keys comparison
	 * 		values - values comparison
	 * @param \Object\ActiveRecord $object
	 * @param bool $strict
	 * @return array|bool
	 */
	public function compare(\Object\ActiveRecord $object, string $type = 'pk', bool $strict = false) : array|bool {
		// pk comparison
		if ($type == 'pk') {
			foreach ($this->object_table_object->pk ?? [] as $v) {
				if ($strict) {
					if (!empty($this->{$v}) && $this->{$v} !== $object->{$v}) {
						return false;
					}
				} else {
					if (!empty($this->{$v}) && $this->{$v} != $object->{$v}) {
						return false;
					}
				}
			}
			return true;
		}
		// values comparison
		if ($type == 'values') {
			$result = [];
			foreach ($this->object_table_object->columns as $k => $v) {
				if ($strict) {
					if ($this->{$k} != $object->{$k}) {
						$result[$k] = $object->{$k};
					}
				} else {
					if ($this->{$k} !== $object->{$k}) {
						$result[$k] = $object->{$k};
					}
				}
			}
			return count($result) > 0 ? $result : true;
		}
	}

	/**
	 * Log changes
	 *
	 * @param array $data
	 * @return void
	 */
	public function logChanges(array $data) {
		foreach ($data as $k => $v) {
			if (!isset($this->object_table_log[$k])) {
				$this->object_table_log[$k] = [];
			}
			$this->object_table_log[$k][] = $v;
		}
	}

	/**
	 * Get table object
	 *
	 * @return \Object\Table
	 */
	public function getTableObject() : \Object\Table {
		return $this->object_table_object;
	}
}