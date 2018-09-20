<?php

namespace Object\Table;
trait Trait2 {

	/**
	 * Query builder
	 *
	 * @param array $options
	 *		string alias, default a
	 *		boolean skip_tenant
	 *		boolean skip_acl
	 * @return \Object\Query\Builder
	 */
	public function queryBuilder(array $options = []) : \Object\Query\Builder {
		$model = $this;
		// alias
		$alias = $options['alias'] ?? 'a';
		unset($options['alias']);
		// set tenant parameter
		if ($model->tenant && empty($options['skip_tenant'])) {
			$options['tenant'] = true;
		}
		// we must set pk
		$options['primary_key'] = $model->pk ?? null;
		$object = new \Object\Query\Builder($model->db_link, $options);
		$object->from($model, $alias);
		// inject tenant into the query
		if ($model->tenant && empty($options['skip_tenant'])) {
			$object->where('AND', [$alias . '.' . $model->column_prefix . 'tenant_id', '=', \Tenant::id()]);
			$object->where('AND', [$model->column_prefix . 'tenant_id', '=', \Tenant::id()], false, ['for_delete' => true]);
		}
		// registered ALC
		if (empty($options['skip_acl'])) {
			\Object\ACL\Registered::process('\\' . get_called_class(), $object, [
				'initiator' => $options['initiator'] ?? null,
				'existing_values' => $options['existing_values'] ?? null
			]);
		}
		return $object;
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
	public function get($options = []) {
		$data = [];
		$this->acl_get_options = $options;
		// handle tenant
		if ($this->tenant && empty($options['skip_tenant'])) {
			$options['where'][$this->tenant_column] = \Tenant::id();
		}
		// handle acl init
		/*
		if (!empty($options['acl'])) {
			$acl_key = get_called_class();
			if (\Factory::model('\Object\ACL\Class2', true)->aclInit($acl_key, $data, $this->acl_get_options) === false) {
				return $data;
			}
			$options = $this->acl_get_options;
		}
		*/
		$options_query = [];
		// if we are caching
		if (!empty($this->cache) && empty($options['no_cache'])) {
			$options_query['cache'] = true;
		}
		$options_query['cache_tags'] = !empty($this->cache_tags) ? array_values($this->cache_tags) : [];
		// pk
		$pk = array_key_exists('pk', $options) ? $options['pk'] : $this->pk;
		// query
		$query = self::queryBuilderStatic([
			'skip_tenant' => $options['skip_tenant'] ?? false,
			'skip_acl' => $options['skip_acl'] ?? false,
			'initiator' => 'table',
			'existing_values' => $options['existing_values'] ?? null
		])->select();
		// skip filtering by tenant twice
		if (!empty($query->options['tenant']) && $this->tenant) {
			unset($options['where'][$this->tenant_column]);
		}
		// preset columns
		if (!empty($options['__preset'])) {
			$query->distinct();
			if (!empty($pk) && count($pk) > 1) {
				$temp = $pk;
				unset($temp[array_search('preset_value', $temp)]);
				$query->columns($temp);
			}
			$query->columns(['preset_value' => "concat_ws(' ', " . $query->db_object->prepareExpression($options['columns']) . ")"]);
			$query->where('AND', ["coalesce(" . $query->db_object->prepareExpression($options['columns']) . ")", 'IS NOT', null]);
			// if its a preset we cache
			$options_query['cache'] = true;
		} else { // regular columns
			if (!empty($options['columns'])) {
				$query->columns($options['columns']);
			}
		}
		// where
		if (!empty($options['where'])) {
			$query->whereMultiple('AND', $options['where']);
		}
		// todo
		//$sql.= !empty($options['search']) ? (' AND (' . $this->db_object->prepareCondition($options['search'], 'OR') . ')') : '';
		// order by
		$orderby = $options['orderby'] ?? (!empty($this->orderby) ? $this->orderby : null);
		if (!empty($orderby)) {
			$query->orderby($orderby);
		}
		// offset
		if (!empty($options['offset'])) {
			$query->offset($options['offset']);
		}
		// limit
		if (!empty($options['limit'])) {
			$query->limit($options['limit']);
		} else if (!empty($this->limit)) {
			$query->limit($this->limit);
		} else if (!empty($options['single_row'])) {
			$query->limit(1);
		}
		// memory caching
		if ($this->cache_memory) {
			// hash is query + primary key
			$sql_hash = sha1($query->sql() . serialize($pk));
			if (isset(\Cache::$memory_storage[$sql_hash])) {
				return \Cache::$memory_storage[$sql_hash];
			}
		}
		// query
		$result = $query->query($pk, $options_query);
		$this->sql_last_query = $query->sql();
		if (!$result['success']) {
			Throw new \Exception(implode(", ", $result['error']));
		}
		// single row
		if (!empty($options['single_row'])) {
			$data = current($result['rows']);
		} else {
			$data = $result['rows'];
		}
		// handle acl
		/*
		if (!empty($options['acl'])) {
			if (\Factory::model('\Object\ACL\Class2', true)->aclFinish($acl_key, $data, $this->acl_get_options) === false) {
				return $data;
			}
		}
		*/
		// memory caching
		if ($this->cache_memory) {
			\Cache::$memory_storage[$sql_hash] = & $data;
		}
		return $data;
	}

	/**
	 * Get (static)
	 *
	 * @see $this::get()
	 */
	public static function getStatic(array $options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->get($options);
	}

	/**
	 * Get by column
	 *
	 * @param string $column
	 * @param mixed $value
	 * @param mixed $only_column
	 * @return mixed
	 */
	public function getByColumn(string $column, $value, $only_column = null) {
		$result = $this->get([
			//'columns' => ($only_column ? [$only_column] : null),
			'where' => [
				$column => $value
			],
			'pk' => [$column],
			'single_row' => true,
			'skip_acl' => true
		]);
		if ($only_column) {
			return $result[$only_column] ?? null;
		} else {
			return $result;
		}
	}

	/**
	 * Get by column (static)
	 *
	 * @see $this::get_by_column()
	 */
	public static function getByColumnStatic(string $column, $value, $only_column = null) {
		$class = get_called_class();
		$object = new $class();
		return $object->getByColumn($column, $value, $only_column);
	}

	/**
	 * Exists
	 *
	 * @see $this->get()
	 * @return boolean
	 */
	public function exists($options = []) {
		$data = $this->get($options);
		return !empty($data);
	}

	/**
	 * Exists (static)
	 *
	 * @see $this->get()
	 * @return boolean
	 */
	public static function existsStatic($options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->exists($options);
	}

	/**
	 * Load by id
	 *
	 * @param int $id
	 * @param string $column
	 * @return array | boolean
	 */
	public static function loadById(int $id, string $column = '') {
		$class = get_called_class();
		$model = new $class();
		$pk = $model->pk;
		$last = array_pop($model->pk);
		$result = $model->get([
			'where' => [
				$last => $id
			],
			'pk' => [$last],
			'single_row' => true,
			'skip_acl' => true
		]);
		if (!empty($column)) {
			return $result[$column] ?? null;
		}
		return $result;
	}
}