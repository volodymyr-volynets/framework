<?php

class object_datasource {

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
	 * Get
	 *
	 * @param array $options
	 * @return mixed
	 * @throws Exception
	 */
	final public function get($options = []) {
		// process parameters
		$parameters = $options['where'] ?? [];
		unset($options['where']);
		// process primary model
		if (!empty($this->primary_model)) {
			$model = new $this->primary_model();
			$this->db_link = $model->db_link;
			$this->pk = $model->pk;
			$this->cache_tags = $model->cache_tags;
			// query
			$this->query = call_user_func_array([$this->primary_model, 'query_builder'], [$options])->select();
		}
		// we need to determine db link
		if (empty($this->db_link)) {
			// get from flags first
			if (!empty($this->db_link_flag)) {
				$this->db_link = application::get($this->db_link_flag);
			}
			// get default link
			if (empty($this->db_link)) {
				$this->db_link = application::get('flag.global.db.default_db_link');
			}
			// if we could not determine the link we throw exception
			if (empty($this->db_link)) {
				Throw new Exception('Could not determine db link in datasource!');
			}
		}
		// create empty query object
		if (empty($this->query)) {
			$this->query = new object_query_builder($this->db_link, $options);
			$this->query->select();
		}
		// add settings to query
		$query_settings = [];
		foreach (['pk', 'columns', 'limit', 'orderby', 'single_row', 'single_value'] as $v) {
			if (isset($options[$v])) {
				$query_settings[$v] = $options[$v];
			} else if (isset($this->{$v})) {
				$query_settings[$v] = $this->{$v};
			}
		}
		if (isset($query_settings['columns'])) $this->query->columns($query_settings['columns']);
		if (isset($query_settings['limit'])) $this->query->limit($query_settings['limit']);
		if (isset($query_settings['orderby'])) $this->query->limit($query_settings['orderby']);
		if (!empty($query_settings['single_row']) || !empty($query_settings['single_value'])) $this->query->limit(1);
		// check if we have query method
		if (!method_exists($this, 'query')) {
			Throw new Exception('You must specify sql in query method!');
		}
		// process query
		$sql = $this->query($parameters, $options);
		// query options
		$query_options = [
			'cache' => $this->cache,
			'cache_tags' => $this->cache_tags ?? []
		];
		// if we have SQL
		if (!empty($sql)) {
			$db = new db($this->db_link);
			$result = $db->query($sql, $query_settings['pk'] ?? null, $query_options);
		} else { // query builder
			$result = $this->query->query($query_settings['pk'] ?? null, $query_options);
		}
		if (!$result['success']) {
			Throw new Exception(implode(", ", $result['error']));
		}
		// process data
		if (method_exists($this, 'process')) {
			$data = $this->process($result['rows'], $options);
		} else {
			$data = $result['rows'];
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
	 * @param array $options
	 * @return mixed
	 */
	public static function get_static(array $options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->get($options);
	}
}