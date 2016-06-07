<?php

class object_table extends object_override_data {

	/**
	 * Link to database
	 *
	 * @var string
	 */
	public $db_link;

	/**
	 * Override for link to database
	 *
	 * @var string 
	 */
	public $db_link_flag;

	/**
	 * Table name including schema in format [schema].[name]
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Table primary key in format ['id1'] or ['id1', 'id2', 'id3']
	 *
	 * @var array
	 */
	public $pk;

	/**
	 * Table default order as array
	 * Format:
	 *		column 1 => asc or SORT_ASC
	 *		column 2 => desc or SORT_DESC
	 *
	 * @var array
	 */
	public $orderby = [];

	/**
	 * Table get limit
	 *
	 * @var int
	 */
	public $limit = 0;

	/**
	 * Column prefix
	 *
	 * @var string
	 */
	public $column_prefix;

	/**
	 * Table columns
	 *
	 * @var array
	 */
	public $columns = [
		//'id' => array('name' => '#', 'type' => 'bigserial'),
		//'name' => array('name' => 'Name', 'type' => 'varchar', 'length' => 255),
	];

	/**
	 * Table constraints
	 *
	 * @var array
	 */
	public $constraints = [
		//'name_un' => array('type' => 'unique', 'columns' => ['name']),
	];

	/**
	 * Table indexes
	 *
	 * @var array
	 */
	public $indexes = [
		//'name_idx' => array('type' => 'btree', 'columns' => ['name']),
	];

	/**
	 * Whether its a table with a history, and we do point in time quering
	 *
	 * @var bool
	 */
	public $history = false;

	/**
	 * History table name
	 *
	 * @var string
	 */
	public $history_name;

	/**
	 * Whether we need to keep audit log for this table
	 *
	 * @var bool
	 */
	public $audit = false;

	/**
	 * Optimistic lock
	 *
	 * @var boolean 
	 */
	public $optimistic_lock = false;

	/**
	 * Optimistic lock column
	 *
	 * @var string
	 */
	public $optimistic_lock_column;

	/**
	 * Table engine
	 *
	 * @var array
	 */
	public $engine = [];

	/**
	 * Mapping for crud::options(),
	 * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
	 *
	 * @var array
	 */
	public $options_map = [
		//'[table field]' => '[key in array]',
	];

	/**
	 * Condition for crud::options_active()
	 *
	 * @var type
	 */
	public $options_active = [
		//'[table field]' => [value],
	];

	/**
	 * Wherether we need to cache this table
	 *
	 * @var bool
	 */
	public $cache = false;

	/**
	 * These tags will be added to caches and then will be used in cache::gc();
	 *
	 * @var type
	 */
	public $cache_tags = [];

	/**
	 * Whether we need to cache in memory
	 *
	 * @var bool
	 */
	public $cache_memory = false;

	/**
	 * Constructing object
	 *
	 * @throws Exception
	 */
	public function __construct() {
		// we need to handle overrrides
		parent::override_handle($this);
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
				Throw new Exception('Could not determine db link in model!');
			}
		}
		// processing table name
		$ddl = factory::get(['db', $this->db_link, 'ddl_object']);
		$temp = $ddl->is_schema_supported($this->name);
		$this->name = $temp['full_table_name'];
		$this->history_name = $this->name . '__history';
		// process domain in columns
		$this->columns = object_data_common::process_domains($this->columns);
		// optimistic lock
		if ($this->optimistic_lock) {
			$this->optimistic_lock_column = $this->column_prefix . 'optimistic_lock';
			$this->columns[$this->optimistic_lock_column] = ['name' => 'Optimistic Lock', 'type' => 'timestamp', 'default' => 'now()'];
		}
	}

	/**
	 * Insert single row into table
	 *
	 * @param array $data
	 * @return array
	 */
	public function insert($data) {
		$db = new db($this->db_link);
		return $db->insert($this->name, [$data], null, ['returning' => $this->pk]);
	}

	/**
	 * Convert input into array
	 *
	 * @param array $data
	 * @param boolean $ignore_not_set_fields
	 * @return array
	 */
	public function process_columns($data, $ignore_not_set_fields = false) {
		$save = [];
		foreach ($this->columns as $k => $v) {
			if ($ignore_not_set_fields && !array_key_exists($k, $data)) {
				continue;
			}
			$temp = object_table_columns::process_single_column_type($k, $v, $data[$k] ?? null);
			$save = array_merge($save, $temp);
		}
		return $save;
	}

	/**
	 * Verify fields
	 *
	 * @param array $save
	 * @param array $columns
	 * @param array $result
	 */
	public function verify_fields(& $save, & $columns, & $result) {
		foreach ($columns as $k => $v) {
			// running value through a function first
			if (!empty($v['function'])) {
				if (strpos($v['function'], '::') !== false) {
					$save[$k] = call_user_func($v['function'], $save[$k]);
				} else {
					$save[$k] = function2($v['function'], $save[$k]);
				}
			}
			// checking if value is empty
			if (empty($v['empty']) && empty($save[$k])) {
				$result['error'][] = $v['name'] . ' cannot be empty!';
			}
			// checking if value is too long
			if (!empty($v['maxlength'])) {
				if (strlen($save[$k]) > $v['maxlength']) {
					$result['error'][] = $v['name'] . ' is too long, max length = ' . $v['maxlength'] . '!';
				}
			}
		}
	}

	/**
	 * Save/create a record
	 *
	 * @param array $data
	 * @return array
	 */
	public function save($data, $options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'rows' => [],
			'inserted' => false
		];

		// populating fields
		$save = $this->process_columns($data, isset($options['ignore_not_set_fields']) ? $options['ignore_not_set_fields'] : false);

		// verifying
		do {
			if (empty($save)) {
				$result['error'][] = 'You must specify atleast one field!';
			}

			// verification against columns
			if (!empty($this->save_columns)) {
				// verification
				$this->verify_fields($save, $this->save_columns, $result);

				// additional verification
				if (method_exists($this, 'save_verify')) {
					array_merge3($result['error'], $this->save_verify($save, $this->save_columns));
				}
			}

			if (!empty($result['error'])) {
				break;
			}

			// we need to unset pk if other primary key is used
			if (!empty($options['pk'])) {
				foreach ($this->pk as $k => $v) {
					if (empty($save[$v])) {
						unset($save[$v]);
					}
				}
			}

			// saving record to database
			$db = new db($this->db_link);
			$result = $db->save($this->name, $save, $options['pk'] ?? $this->pk, $options);
			if ($result['success'] && $this->cache) {
				// now we need to reset cache
				if (empty($data['do_not_reset_cache'])) {
					$this->reset_cache();
				}
			}
		} while(0);
		return $result;
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
	 * @return array
	 */
	public function get($options = []) {
		$options_query = array();
		// if we are caching
		if (!empty($this->cache) && empty($options['no_cache'])) {
			$options_query['cache'] = true;
		}
		$options_query['cache_tags'] = !empty($this->cache_tags) ? array_values($this->cache_tags) : [];
		$options_query['cache_tags'][] = $this->name;
		// db object
		$db = new db($this->db_link);
		// where
		$sql = '';
		$sql.= !empty($options['where']) ? (' AND ' . $db->prepare_condition($options['where'])) : '';
		$sql.= !empty($options['search']) ? (' AND (' . $db->prepare_condition($options['search'], 'OR') . ')') : '';
		// order by
		$orderby = $options['orderby'] ?? (!empty($this->orderby) ? $this->orderby : null);
		if (!empty($orderby)) {
			$sql.= ' ORDER BY ' . array_key_sort_prepare_keys($orderby, true);
		}
		// limit
		if (!empty($options['limit'])) {
			$sql.= ' LIMIT ' . $options['limit'];
		} else if (!empty($this->limit)) {
			$sql.= ' LIMIT ' . $this->limit;
		}
		// pk
		$pk = array_key_exists('pk', $options) ? $options['pk'] : $this->pk;
		// columns
		if (!empty($options['columns'])) {
			$columns = $db->prepare_expression($options['columns']);
		} else {
			$columns = '*';
		}
		// querying
		$sql_full = 'SELECT ' . $columns . ' FROM ' . $this->name . ' WHERE 1=1' . $sql;
		// memory caching
		if ($this->cache_memory) {
			// hash is query + primary key
			$crypt = new crypt();
			$sql_hash = $crypt->hash($sql_full . serialize($pk));
			if (isset(cache::$memory_storage[$sql_hash])) {
				return cache::$memory_storage[$sql_hash];
			}
		}
		$result = $db->query($sql_full, $pk, $options_query);
		if (!$result['success']) {
			Throw new Exception(implode(", ", $result['error']));
		}
		if ($this->cache_memory) {
			cache::$memory_storage[$sql_hash] = & $result['rows'];
		}
		return $result['rows'];
	}

	/**
	 * Get db object
	 *
	 * @return object
	 */
	public function db_object() {
		return new db($this->db_link);
	}

	/**
	 * Reset caches on exit
	 */
	public function reset_cache() {
		// get cache link
		$db = $this->db_object();
		$cache_link = $db->object->connect_options['cache_link'];
		// create empty cache array
		if (!isset(cache::$reset_caches[$cache_link])) {
			cache::$reset_caches[$cache_link] = [];
		}
		// create unique caches by adding new
		cache::$reset_caches[$cache_link] = array_unique(array_merge(cache::$reset_caches[$cache_link], $this->cache_tags, [$this->name]));
	}
}