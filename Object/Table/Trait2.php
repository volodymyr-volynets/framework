<?php

namespace Object\Table;
trait Trait2 {

	use \Object\Traits\ObjectableAndStaticable;
	use \Object\Table\ColumnSettings;

	/**
	 * Query builder (Static)
	 *
	 * @param array $options
	 *		string alias, default a
	 *		boolean skip_tenant
	 *		boolean skip_acl
	 *		boolean skip_global_scope
	 *		mixed existing_values
	 * @return \Object\Query\Builder
	 */
	public static function queryBuilderStatic(array $options = []) : \Object\Query\Builder {
		$class = get_called_class();
		$object = new $class();
		return $object->queryBuilder($options);
	}

	/**
	 * Get (static)
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
	 *		skip_acl - if we need to skip acl
	 *		skip_global_scope - if we need to skip global scopes
	 * 		cast_to_class - if we need to cast results to a class
	 *		skip_column_settings - if we need to skip column settings for example maskable
	 *			ALL, MASKABLE, PASSWORDABLE, GENERABLE, READ_ONLY, READ_ONLY_IF_SET
	 * @return array
	 */
	public static function getStatic(array $options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->get($options);
	}

	/**
	 * Query assembler
	 *
	 * @param array $data
	 * @param array $options
	 * 		alias - alias of a table
	 * @return \Object\Query\Assembler
	 */
	public static function queryAssemblerStatic(array & $data, array $options = []) : \Object\Query\Assembler {
		$class = get_called_class();
		$model = new $class();
		return $model->queryAssembler($data, $options);
	}

	/**
	 * Counter
	 *
	 * @param array $where
	 * @param array $options
	 * @return int
	 */
	public static function counterStatic(array $where, array $options = []) : int {
		$class = get_called_class();
		$model = new $class();
		return $model->counter($where, $options);
	}

	/**
	 * Query builder
	 *
	 * @param array $options
	 *		string alias, default a
	 *		boolean skip_tenant
	 *		boolean skip_acl
	 *		boolean skip_global_scope
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
		// global scopes
		if (empty($options['skip_global_scope'])) {
			$reflection = new \ReflectionClass($object->primary_model);
			foreach ($reflection->getMethods() as $v) {
				$method_name = $v->getName();
				if (str_starts_with($method_name, 'scope') && str_ends_with($method_name, 'Global')) {
					$method_name = str_replace('scope', '', $method_name);
					$object->withScope([$method_name]);
				}
			}
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
	 *		skip_acl - if we need to skip acl
	 *		skip_global_scope - if we need to skip global scopes
	 * 		cast_to_class - if we need to cast results to a class
	 *		skip_column_settings - if we need to skip column settings for example maskable
	 *			ALL, MASKABLE, PASSWORDABLE, GENERABLE, READ_ONLY, READ_ONLY_IF_SET
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
			'skip_global_scope' => $options['skip_global_scope'] ?? true,
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
		$result = $query->query(null, $options_query);
		$this->sql_last_query = $query->sql();
		if (!$result['success']) {
			Throw new \Exception(implode(", ", $result['error']));
		}
		// cast to class
		if (!empty($options['cast_to_class']) && count($result['rows']) > 0) {
			foreach ($result['rows'] as $k => $v) {
				$destination = new $options['cast_to_class'](['skip_table_object']);
				object_cast($destination, (object) $v);
				// we load full PK
				foreach ($this->pk as $v2) {
					$destination->object_table_pk[$v2] = $v[$v2];
				}
				// process column settings
				if (!$this->processColumnSettingsForObjects($this->column_settings, $destination, $options)) {
					Throw new \Exception('Could not process column settings!');
				}
				$result['rows'][$k] = $destination;
			}
		}
		// change primary keys
		if ($pk) {
			pk($pk, $result['rows']);
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
	 * Get single
	 *
	 * @see $this::get()
	 */
	public function getSingle(array $options = []) {
		$options['pk'] = null;
		$options['single_row'] = true;
		return $this->get($options);
	}

	/**
	 * Get single (static)
	 *
	 * @see $this::get()
	 */
	public static function getSingleStatic(array $options = []) {
		$class = get_called_class();
		$object = new $class();
		$options['pk'] = null;
		$options['single_row'] = true;
		return $object->get($options);
	}

	/**
	 * Get by column
	 *
	 * @param string|array $where
	 * @param mixed $value
	 * @param string $only_column
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
	 * @param array $options
	 * @return boolean
	 * @see $this->get()
	 */
	public function exists(array $options = []) : bool {
		$data = $this->get($options);
		return !empty($data);
	}

	/**
	 * Exists (static)
	 *
	 * @param array $options
	 * @return boolean
	 * @see $this->get()
	 */
	public static function existsStatic(array $options = []) : \Object\Query\Builder {
		$class = get_called_class();
		$object = new $class();
		return $object->exists($options);
	}

	/**
	 * Load by id
	 *
	 * @param int|array $id
	 * @param string|array $options
	 * 		column - column name
	 * 		cast_to_class - class name
	 * @return mixed
	 */
	public function loadById(int|array $id, string|array $options = []) {
		if (is_string($options)) {
			$options = ['column' => $options];
		}
		$options['cast_to_class'] = $options['cast_to_class'] ?? null;
		$pk = $this->pk;
		$last = array_pop($pk);
		if (is_array($id)) {
			$where = $id;
		} else {
			$where = [
				$last => $id
			];
		}
		$result = $this->get([
			'where' => $where,
			'pk' => [$last],
			'single_row' => true,
			'skip_acl' => true,
			'cast_to_class' => $options['cast_to_class'],
			'cache_memory' => $options['cache_memory'] ?? null,
		]);
		if (isset($options['column'])) {
			if (is_object($result)) {
				return $result->{$options['column']};
			} else {
				return $result[$options['column']] ?? null;
			}
		}
		return $result;
	}

	/**
	 * Load by id (static)
	 *
	 * @param int|array $id
	 * @param string|array $options
	 * 		column - column name
	 * 		cast_to_class - class name
	 * @return mixed
	 */
	public static function loadByIdStatic(int|array $id, string|array $options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->loadById($id, $options);
	}

	/**
	 * Counter
	 *
	 * @param array $where
	 * @param array $options
	 * @return int
	 * @see $this->get()
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

	/**
	 * Query assembler
	 *
	 * @param array $data
	 * @param array $options
	 * 		alias - alias of a table
	 * @return \Object\Query\Assembler
	 */
	public function queryAssembler(array & $data, array $options = []) : \Object\Query\Assembler {
		$options['alias'] = $options['alias'] ?? 'relation_a';
		$object = new \Object\Query\Assembler($this, $data, $options);
		return $object;
	}
}