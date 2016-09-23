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
	 * Db object
	 *
	 * @var object
	 */
	public $db_object;

	/**
	 * Table name including schema in format [schema_name]
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Schema extracted from name
	 *
	 * @var string
	 */
	public $schema;

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
	 * Relation
	 *
	 * @var array
	 */
	public $relation = [
		//'field' => '[field name]',
		//'inactive' => 1 or 0
	];

	/**
	 * Attributes
	 *
	 * @var boolean
	 */
	public $attributes;

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
		$this->history_name = $this->name . '__history';
		// process relations if we have a module
		if (!empty($this->relation) && application::get('dep.submodule.numbers.data.relations')) {
			// add a column if not exists
			if (empty($this->columns[$this->relation['field']])) {
				$this->columns[$this->relation['field']] = ['name' => 'Relation #', 'domain' => 'relation_id_sequence'];
				// add unique constraint
				$this->constraints[$this->relation['field'] . '_un'] = ['type' => 'unique', 'columns' => [$this->relation['field']]];
			}
		}
		// process domain in columns
		$this->columns = object_data_common::process_domains($this->columns);
		// optimistic lock
		if ($this->optimistic_lock) {
			$this->optimistic_lock_column = $this->column_prefix . 'optimistic_lock';
			$this->columns[$this->optimistic_lock_column] = ['name' => 'Optimistic Lock', 'type' => 'timestamp', 'default' => 'now()'];
		}
		// schema & title
		$temp = explode('_', $this->name);
		if (empty($this->schema)) {
			$this->schema = $temp[0];
		}
		unset($temp[0]);
		if (empty($this->title)) {
			$this->title = ucwords(implode(' ', $temp));
		}
		// initialize db object
		$this->db_object = new db($this->db_link);
	}

	/**
	 * Insert single row into table
	 *
	 * @param array $data
	 * @return array
	 */
	public function insert($data) {
		return $this->db_object->insert($this->name, [$data], null, ['returning' => $this->pk]);
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

			// we need to process serial columns
			$pk = $options['pk'] ?? $this->pk;
			$options['sequences'] = [];
			foreach ($this->columns as $k => $v) {
				if (strpos($v['type'], 'serial') !== false && empty($v['null'])) {
					$options['sequences'][$k] = [
						'sequence_column' => $k,
						'sequence_name' => $this->name . '_' . $k . '_seq'
					];
				}
			}

			// saving record to database
			$result = $this->db_object->save($this->name, $save, $pk, $options);
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
		// where
		$sql = '';
		$sql.= !empty($options['where']) ? (' AND ' . $this->db_object->prepare_condition($options['where'])) : '';
		$sql.= !empty($options['search']) ? (' AND (' . $this->db_object->prepare_condition($options['search'], 'OR') . ')') : '';
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
			$columns = $this->db_object->prepare_expression($options['columns']);
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
		$result = $this->db_object->query($sql_full, $pk, $options_query);
		if (!$result['success']) {
			Throw new Exception(implode(", ", $result['error']));
		}
		if ($this->cache_memory) {
			cache::$memory_storage[$sql_hash] = & $result['rows'];
		}
		// single row
		if (!empty($options['single_row'])) {
			return current($result['rows']);
		} else {
			return $result['rows'];
		}
	}

	/**
	 * Get db object
	 *
	 * @return object
	 */
	public function db_object() {
		return $this->db_object;
	}

	/**
	 * Reset caches on exit
	 */
	public function reset_cache() {
		// get cache link
		$cache_link = $this->db_object->object->connect_options['cache_link'];
		// create empty cache array
		if (!isset(cache::$reset_caches[$cache_link])) {
			cache::$reset_caches[$cache_link] = [];
		}
		// create unique caches by adding new
		cache::$reset_caches[$cache_link] = array_unique(array_merge(cache::$reset_caches[$cache_link], $this->cache_tags, [$this->name]));
	}

	/**
	 * Options
	 *
	 * @see $this->get()
	 */
	public function options($options = []) {
		// if compound key
		if (count($this->pk) > 1) {
			$temp = $this->pk;
			$last = array_pop($temp);
			foreach ($temp as $v) {
				if (empty($options['where'][$v])) {
					return [];
				}
			}
		}
		$data = $this->get($options);
		// if compound key
		if (!empty($temp)) {
			foreach ($temp as $v) {
				if (!isset($data[$options['where'][$v]])) {
					return [];
				}
				$data = $data[$options['where'][$v]];
			}
		}
		$options_map = !empty($this->options_map) ? $this->options_map : [$this->column_prefix . 'name' => 'name'];
		// build options
		return object_data_common::build_options($data, $options_map, $this->orderby, $options['i18n'] ?? false);
	}

	/**
	 * Active Options
	 *
	 * @param array $options
	 * @return array
	 */
	public function options_active($options = []) {
		$temp = $this->options_active ? $this->options_active : [$this->column_prefix . 'inactive' => 0];
		$options['where'] = array_merge_hard($options['where'] ?? [], $temp);
		return $this->options($options);
	}

	/**
	 * Multi level options
	 *
	 * @see $this->get()
	 */
	public function optmultis($options = []) {
		if (empty($this->optmultis_map)) {
			return [];
		} else {
			$data = $this->get($options);
			$optmultis_map = $this->optmultis_map;
			return object_data_common::optmultis($data, $optmultis_map, $options);
		}
	}

	/**
	 * Check unique constraint
	 *
	 * @param string $column
	 * @param mixed $value
	 * @param mixed $pk
	 * @return boolean
	 */
	public function check_unique_constraint($column, $value, $pk) {
		$db = $this->db_object();
		if (is_string($value)) {
			$value = "'" . $db->escape($value) . "'";
		}
		if (!empty($pk)) {
			if (is_string($pk)) {
				$pk = "'" . $db->escape($pk) . "'";
			}
			$pk = 'AND ' . $this->pk[0] . ' <> ' . $pk;
		} else {
			$pk = "";
		}
		$sql = <<<TTT
			SELECT
				1
			FROM {$this->name}
			WHERE 1=1
				AND {$column} = {$value}
				{$pk}
TTT;
		$result = $db->query($sql);
		return ($result['num_rows'] <> 0 ? true : false);
	}

	/**
	 * @see $this->get()
	 * @return boolean
	 */
	public function exists($options = []) {
		$data = $this->get($options);
		return !empty($data);
	}

	/**
	 * @see $this->get()
	 * @return boolean
	 */
	public static function exists_static($options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->exists($options);
	}

	/**
	 * Validate multiple options/autocompletes at the same time
	 *
	 * @param array $options
	 * @return array
	 */
	public function validate_options_multiple($options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'discrepancies' => []
		];
		$mass_sql = [];
		foreach ($options as $k => $v) {
			$model = factory::model($v['model'], true);
			$temp = [
				$v['field'] => $v['values']
			];
			$where = $this->db_object->prepare_condition(array_merge_hard($v['params'] ?? [], $temp), 'AND');
			$fields = "concat_ws('', " . implode(', ', array_keys($temp)) . ")";
			$mass_sql[] = <<<TTT
				SELECT
					'{$k}' validate_name,
					{$fields} validate_value
				FROM {$model->name}
				WHERE 1=1
					AND {$where}
TTT;
		}
		$mass_sql = implode("\n\nUNION ALL\n\n", $mass_sql);
		$temp = $this->db_object->query($mass_sql);
		if ($temp['success']) {
			// generate array of unique values
			$unique = [];
			foreach ($temp['rows'] as $k => $v) {
				if (!isset($unique[$v['validate_name']])) {
					$unique[$v['validate_name']] = [];
				}
				$unique[$v['validate_name']][] = $v['validate_value'];
			}
			// find differencies
			foreach ($options as $k => $v) {
				// see if we found values
				if (!isset($unique[$k])) {
					$result['discrepancies'][$k] = count($v['values']);
				} else {
					foreach ($v['values'] as $v2) {
						if (!in_array($v2 . '', $unique[$k])) {
							if (!isset($result['discrepancies'][$k])) {
								$result['discrepancies'][$k] = 0;
							}
							$result['discrepancies'][$k]++;
						}
					}
				}
			}
			$result['success'] = true;
		}
		return $result;
	}
}