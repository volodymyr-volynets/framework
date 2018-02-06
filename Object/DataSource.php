<?php

namespace Object;
class DataSource extends \Object\Table\Options {

	/**
	 * Db link
	 *
	 * @var string
	 */
	public $db_link;

	/**
	 * Db link flag
	 *
	 * @var string
	 */
	public $db_link_flag;

	/**
	 * Primary key
	 *
	 * @var array
	 */
	public $pk;

	/**
	 * Columns
	 *
	 * @var array
	 */
	public $columns;

	/**
	 * Limit
	 *
	 * @var int
	 */
	public $limit;

	/**
	 * Order by
	 *
	 * @var array
	 */
	public $orderby;

	/**
	 * Single row
	 *
	 * @var boolean
	 */
	public $single_row;

	/**
	 * Single value
	 *
	 * @var boolean
	 */
	public $single_value;

	/**
	 * Cache
	 *
	 * @var boolean
	 */
	public $cache;

	/**
	 * Cache tags
	 *
	 * @var array
	 */
	public $cache_tags;

	/**
	 * Cache in memory
	 *
	 * @var boolean
	 */
	public $cache_memory;

	/**
	 * Primary model, following are copied if not set:
	 *		db_link
	 *		db_link_flag
	 *		cache_tags
	 *		pk
	 *		query_builder - new object would be created
	 *
	 * @var string
	 */
	public $primary_model;

	/**
	 * Parameters
	 *
	 * @var array
	 */
	public $parameters = [];

	/**
	 * Query
	 *
	 * @var object
	 */
	public $query;

	/**
	 * SQL Last query
	 *
	 * @var string
	 */
	public $sql_last_query;

	/**
	 * Tenant
	 *
	 * @var integer
	 */
	public $tenant;
	public $tenant_column;

	/**
	 * Get
	 *
	 * @param array $options
	 * @return mixed
	 * @throws \Exception
	 */
	final public function get($options = []) {
		// process parameters
		$parameters = [];
		$options['where'] = $options['where'] ?? [];
		if (!empty($this->parameters)) {
			$this->parameters = \Object\Data\Common::processDomainsAndTypes($this->parameters);
			foreach ($this->parameters as $k => $v) {
				// if we have a parameter
				if (array_key_exists($k, $options['where'])) {
					if (!empty($v['multiple_column'])) {
						if (!is_array($options['where'][$k])) $options['where'][$k] = [$options['where'][$k]];
						$parameters[$k] = [];
						foreach ($options['where'][$k] as $v2) {
							$result = \Object\Table\Columns::validateSingleColumn($k, $v, $v2);
							if (!$result['success']) {
								Throw new \Exception("Datasource: " . get_called_class() . " parameter: {$k} error" . implode(', ', $result['error']));
							} else {
								$parameters[$k][] = $result['data'][$k];
							}
						}
					} else {
						$result = \Object\Table\Columns::validateSingleColumn($k, $v, $options['where'][$k]);
						if (!$result['success']) {
							Throw new \Exception("Datasource: " . get_called_class() . " parameter: {$k} error" . implode(', ', $result['error']));
						} else {
							$parameters[$k] = $result['data'][$k];
						}
					}
				}
				// required
				if (!empty($v['required']) && empty($parameters[$k])) {
					return [];
					//Throw new \Exception("Datasource: " . get_called_class() . " parameter: {$k} error" . i18n(null, \Object\Content\Messages::required_field));
				}
			}
		}
		// we need to push existing values to parameters
		if (!empty($options['existing_values']) && !empty($this->parameters['existing_values'])) {
			$parameters['existing_values'] = $options['existing_values'];
		}
		unset($options['where']);
		// process primary model
		if (!empty($this->primary_model)) {
			$model = new $this->primary_model();
			$this->db_link = $model->db_link;
			if (empty($this->pk)) $this->pk = $model->pk;
			$this->cache_tags = array_merge($this->cache_tag ?? [], $model->cache_tags);
			// query
			$this->query = call_user_func_array([$this->primary_model, 'queryBuilderStatic'], [$options])->select();
		}
		// we need to determine db link
		if (empty($this->db_link)) {
			// get from flags first
			if (!empty($this->db_link_flag)) {
				$this->db_link = \Application::get($this->db_link_flag);
			}
			// get default link
			if (empty($this->db_link)) {
				$this->db_link = \Application::get('flag.global.default_db_link');
			}
			// if we could not determine the link we throw exception
			if (empty($this->db_link)) {
				Throw new \Exception('Could not determine db link in datasource!');
			}
		}
		// create empty query object
		if (empty($this->query)) {
			$this->query = new \Object\Query\Builder($this->db_link, $options);
			$this->query->select();
		}
		// add settings to query
		$query_settings = [];
		foreach (['pk', 'columns', 'limit', 'orderby', 'single_row', 'single_value'] as $v) {
			if (array_key_exists($v, $options)) {
				$query_settings[$v] = $options[$v];
			} else if (isset($this->{$v})) {
				$query_settings[$v] = $this->{$v};
			}
		}
		if (isset($query_settings['columns'])) $this->query->columns($query_settings['columns']);
		if (isset($query_settings['limit'])) $this->query->limit($query_settings['limit']);
		if (isset($query_settings['orderby'])) $this->query->limit($query_settings['orderby']);
		if (!empty($query_settings['single_row']) || !empty($query_settings['single_value'])) $this->query->limit(1);
		// fix variables
		$query_settings['pk'] = $query_settings['pk'] ?? null;
		if (empty($this->cache_tags)) $this->cache_tags = [];
		// check if we have query method
		if (!method_exists($this, 'query')) {
			Throw new \Exception('You must specify sql in query method!');
		}
		// process query
		$sql = $this->query($parameters, $options);
		if (!empty($sql)) {
			$db_object = new \Db($this->db_link);
			$this->sql_last_query = $sql;
		} else {
			$db_object = $this->query->db_object;
			$this->sql_last_query = $this->query->sql();
			// grab cache tags from query object
			if (!empty($this->query->cache_tags)) {
				$this->cache_tags = array_unique(array_merge($this->cache_tags, $this->query->cache_tags));
			}
		}
		// determine caching strategy
		if (method_exists($this, 'process')) {
			// retrive data from the cache
			if ($this->cache && !empty($db_object->object->options['cache_link'])) {
				$cache_id = !empty($options['cache_id']) ? $options['cache_id'] : 'Db_DataSource_' . trim(sha1($this->sql_last_query . serialize($query_settings['pk'])));
				// if we cache this query
				$cache_object = new \Cache($db_object->object->options['cache_link']);
				$cached_result = $cache_object->get($cache_id, true);
				if ($cached_result !== false) {
					// if we are debugging
					if (\Debug::$debug) {
						\Debug::$data['sql'][] = $cached_result;
					}
					return $cached_result['rows'];
				}
			} else {
				$this->cache = false;
			}
		}
		$query_options = [
			'cache' => $this->cache,
			'cache_tags' => array_unique($this->cache_tags)
		];
		// if we need to return a query
		if (!empty($options['return_query_only'])) {
			if (empty($sql)) {
				$sql = $this->query->sql();
			}
			// wrap into tabs
			if ($options['return_query_only'] == 'wrap') {
				$object = new \Object\Query\Builder($this->db_link, []);
				$sql = '(' . $object->wrapSqlIntoTabs($sql) . ')';
			}
			return [
				'sql' => $sql,
				'cache_tags' => array_unique($this->cache_tags)
			];
		}
		// if we have SQL
		if (!empty($sql)) {
			$result = $db_object->query($sql, $query_settings['pk'], $query_options);
		} else { // query builder
			$result = $this->query->query($query_settings['pk'], $query_options);
		}
		if (!$result['success']) {
			Throw new \Exception(implode(", ", $result['error']));
		}
		// put parameters beck into options
		$options['parameters'] = $parameters;
		// process data
		if (method_exists($this, 'process')) {
			$data = $this->process($result['rows'], $options);
			// if we are caching
			if ($this->cache) {
				// the same cache structure as in Db classes
				$cache_data = [
					'success' => true,
					'error' => [],
					'errno' => 0,
					'rows' => $data,
					'num_rows' => count($data),
					'affected_rows' => 0,
					'structure' => [],
					// debug attributes
					'cache' => true,
					'cache_tags' => [],
					'time' => microtime(true),
					'sql' => $this->sql_last_query,
					'key' => $query_settings['pk'],
					'backtrace' => null
				];
				if (\Debug::$debug) {
					$cache_data['backtrace']  = implode("\n", \Object\Error\Base::debugBacktraceString());
					$cache_data['cache_tags'] = $this->cache_tags;
				}
				$cache_object->set($cache_id, $cache_data, null, $this->cache_tags);
			}
		} else {
			$data = $result['rows'];
		}
		// process not cached
		if (method_exists($this, 'processNotCached')) {
			$data = $this->processNotCached($data, $options);
		}
		// single row
		if (!empty($query_settings['single_row']) && !empty($data)) {
			$data = current($data);
		}
		// single value
		if (!empty($query_settings['single_value']) && !empty($data)) {
			$data = current($data);
			if (!empty($data)) {
				$data = current($data);
			}
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
	 * Query builder
	 *
	 * @param array $options
	 * @return \\Object\Query\Builder
	 */
	public function queryBuilder(array $options = []) : \Object\Query\Builder {
		return self::queryBuilderStatic($options);
	}

	/**
	 * Query builder (static)
	 *
	 * @param array $options
	 * @return \\Object\Query\Builder
	 */
	public static function queryBuilderStatic(array $options = []) : \Object\Query\Builder {
		$class = get_called_class();
		$model = new $class();
		$options['cache_tags'] = $options['cache_tags'] ?? [];
		$sql = $model->sql([
			'where' => $options['where'] ?? []
		], $options['cache_tags']);
		// alias
		$alias = $options['alias'] ?? 'a';
		unset($options['alias']);
		$object = new \Object\Query\Builder($model->db_link, $options);
		$object->from($sql, $alias);
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
	 * SQL
	 *
	 * @param array $options
	 * @param array $cache_tags
	 * @return string
	 */
	public function sql(array $options, array & $cache_tags = null) : string {
		$options['return_query_only'] = 'wrap';
		$result = $this->get($options);
		if (isset($cache_tags)) {
			$cache_tags = array_merge($cache_tags, $result['cache_tags']);
		}
		return $result['sql'];
	}
}