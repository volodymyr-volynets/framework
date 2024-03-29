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
	 *		mixed existing_values
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
		$object->primary_model = $model;
		$object->primary_alias = $alias;
		$object->from($model, $alias);
		// inject tenant into the query
		if ($model->tenant && empty($options['skip_tenant'])) {
			$object->where('AND', [$alias . '.' . $model->column_prefix . 'tenant_id', '=', \Tenant::id()]);
			$object->where('AND', [$model->column_prefix . 'tenant_id', '=', \Tenant::id()], false, ['for_delete' => true]);
		}
		// ABAC ACL
		if (empty($options['skip_acl'])) {
			$abac_class = \Object\ACL\Resources::getStatic('abac', 'model', 'get');
			if (!empty($abac_class)) {
				$abac_model = \Factory::model($abac_class, false);
				$abac_model->process(get_called_class(), $object, $alias, [
					'initiator' => $options['initiator'] ?? null,
					'existing_values' => $options['existing_values'] ?? null,
					'where' => $options['where'] ?? null,
					'pk' => $options['pk'] ?? $model->pk,
				]);
			}
		}
		// registered ACL
		if (empty($options['skip_acl'])) {
			\Object\ACL\Registered::process(get_called_class(), $object, [
				'initiator' => $options['initiator'] ?? null,
				'existing_values' => $options['existing_values'] ?? null,
				'pk' => $model->pk,
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
	public function get($options = []) {
		$data = [];
		$this->acl_get_options = $options;
		// handle tenant
		if ($this->tenant && empty($options['skip_tenant'])) {
			$options['where'][$this->tenant_column] = \Tenant::id();
		}
		$options_query = [];
		// if we are caching
		if ((!empty($this->cache) || !empty($options['cache'])) && empty($options['no_cache'])) {
			$options_query['cache'] = true;
		}
		$options_query['cache_tags'] = !empty($this->cache_tags) ? array_values($this->cache_tags) : [];
		// pk
		$pk = array_key_exists('pk', $options) ? $options['pk'] : $this->pk;
		// query
		$where_acl = $options['where'] ?? [];
		unset($where_acl[$this->tenant_column]);
		$query = $this->queryBuilder([
			'skip_tenant' => $options['skip_tenant'] ?? false,
			'skip_acl' => $options['skip_acl'] ?? false,
			'initiator' => 'table',
			'existing_values' => $options['existing_values'] ?? null,
			'where' => $where_acl ?? [],
			'pk' => $pk
		])->select();
		// if we came from options
		if (!empty($options['__options']) && empty($options['__preset'])) {
			$columns = array_merge($options['pk'], array_keys($options['options_map']));
			foreach ($columns as $k => $v) {
				if (strpos($v, '*') !== false) {
					unset($columns[$k]);
				}
			}
			$query->columns($columns);
		}
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
			if (!empty($options['existing_values'])) {
				$pk2 = end($pk);
				$query->where('AND', function (& $query) use ($options, $pk2) {
					$query->where('OR', [$pk2, '=', $options['existing_values'], false]);
					$query->where('OR', function (& $query) use ($options) {
						$query->whereMultiple('AND', $options['where']);
					});
				});
			} else {
				$query->whereMultiple('AND', $options['where']);
			}
		}
		// todo
		//$sql.= !empty($options['search']) ? (' AND (' . $this->db_object->prepareCondition($options['search'], 'OR') . ')') : '';
		// order by
		if (array_key_exists('orderby', $options)) {
			if (!empty($options['orderby'])) {
				$query->orderby($options['orderby']);
			}
		} else {
			$orderby = $options['orderby'] ?? (!empty($this->orderby) ? $this->orderby : null);
			if (!empty($orderby)) {
				$query->orderby($orderby);
			}
		}
		// groupby
		if (!empty($options['groupby'])) {
			$query->groupby($options['groupby']);
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
		if (($this->cache_memory || !empty($options['cache_memory'])) && empty($options['no_cache'])) {
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
			if (empty($result['rows'])) {
				$data = [];
			} else {
				$data = current($result['rows']);
			}
		} else {
			$data = $result['rows'];
		}
		// memory caching
		if (($this->cache_memory || !empty($options['cache_memory'])) && empty($options['no_cache'])) {
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
	 * @param string|array $where
	 * @param mixed $value
	 * @param mixed $only_column
	 * @return mixed
	 */
	public function getByColumn(string|array $where, $value = null, $only_column = null) {
		if (is_string($where)) {
			$key = $where;
			$where = [
				$where => $value
			];
		} else {
			$key = null;
		}
		$result = $this->get([
			'where' => $where,
			'pk' => $key,
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
	public static function getByColumnStatic(string|array $where, $value = null, $only_column = null) {
		$class = get_called_class();
		$object = new $class();
		return $object->getByColumn($where, $value, $only_column);
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

	/**
	 * Counter
	 *
	 * @param array $where
	 * @param array $options
	 * @return int
	 */
	public function counter(array $where, array $options = []) : int {
		$query = $this->queryBuilder()->select();
		$query->whereMultiple('AND', $where);
		$query->columns([
			'counter' => 'COUNT(*)',
		]);
		$result = $query->query(null, $options);
		return ($result['rows'][0]['counter'] ?? 0);
	}
}