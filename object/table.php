<?php

class object_table {

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
	public $table_name;

	/**
	 * Table primary key in format ['id1'] or ['id1', 'id2', 'id3']
	 *
	 * @var array
	 */
	public $table_pk;

	/**
	 * Table default order
	 *
	 * @var string or array
	 */
	public $table_orderby = null;

	/**
	 * Table get limit
	 *
	 * @var int
	 */
	public $table_get_limit = 0;

	/**
	 * Table columns
	 *
	 * @var array
	 */
	public $table_columns = [
		//'id' => array('name' => '#', 'type' => 'bigserial'),
		//'name' => array('name' => 'Name', 'type' => 'varchar', 'length' => 255),
	];

	/**
	 * Tables constraints
	 *
	 * @var array
	 */
	public $table_constraints = [
		//'name_un' => array('type' => 'unique', 'columns' => ['name']),
	];

	/**
	 * Tables indexes
	 *
	 * @var array
	 */
	public $table_indexes = [
		//'name_idx' => array('type' => 'btree', 'columns' => ['name']),
	];

	/**
	 * Final table columns after processing inheritance
	 *
	 * @var type
	 */
	public $table_columns_final;

	/**
	 * Whether its a table with a history, and we do point in time quering
	 *
	 * @var bool
	 */
	public $table_history = false;

	/**
	 * Whether we need to keep audit log for this table
	 *
	 * @var bool
	 */
	public $table_audit = false;

	/**
	 * Table row details for crud::row() function
	 *
	 * @var type
	 */
 	public $table_row_details = [
		//'[model]' => ['map' => ['[filed from parent table]'=>'[field from child table]'], 'pk' => ['[arranges child data]'], 'counter' => '[conter filed form parent table]']
 	];

	/**
	 * Mapping for crud::options(),
	 * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
	 *
	 * @var array
	 */
	public $table_options_map = [
		//'[table field]' => '[key in array]',
	];

	/**
	 * Condition for crud::options_active()
	 *
	 * @var type
	 */
	public $table_options_active = [
		//'[table field]' => [value],
	];

	/**
	 * Wherether we need to cache this table
	 *
	 * @var bool
	 */
	public $cache = false;

	/**
	 * Cache link
	 *
	 * @var string 
	 */
	public $cache_link;

	/**
	 * Cache link override
	 *
	 * @var string
	 */
	public $cache_link_flag;

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

		// we need to determine cache link

		// processing table name
		$ddl = factory::get(['db', $this->db_link, 'ddl_object']);
		$temp = $ddl->is_schema_supported($this->table_name);
		$this->table_name = $temp['full_table_name'];
	}

	/**
	 * Insert single row into table
	 *
	 * @param array $data
	 * @return array
	 */
	public function insert($data) {
		$db = new db($this->db_link);
		return $db->insert($this->table_name, [$data], null, ['returning' => $this->table_pk]);
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
		foreach ($this->table_columns as $k => $v) {
			if ($ignore_not_set_fields && !isset($data[$k]) && !array_key_exists($k, $data)) {
				continue;
			}
			// processing as per different data types
			if ($v['type'] == 'boolean') {
				$save[$k] = !empty($data[$k]) ? 1 : 0;
			} else if (in_array($v['type'], array('smallint', 'integer', 'bigint', 'smallserial', 'serial', 'bigserial'))) {
				$save[$k] = format::read_intval(isset($data[$k]) ? $data[$k] : null);
			} else if ($v['type'] == 'numeric') {
				$save[$k] = format::read_floatval(isset($data[$k]) ? $data[$k] : null);
			} else if (in_array($v['type'], ['date', 'time', 'datetime', 'timestamp'])) {
				$save[$k] = format::read_date(isset($data[$k]) ? $data[$k] : null, $v['type']);
			} else if ($v['type'] == 'json') {
				if (!isset($data[$k]) || is_null($data[$k])) {
					$save[$k] = null;
				} else if (is_array($data[$k])) {
					$save[$k] = json_encode($data[$k]);
				} else {
					$save[$k] = $data[$k];
				}
			} else {
				if (!isset($data[$k]) || is_null($data[$k])) {
					$save[$k] = null;
				} else {
					$save[$k] = $data[$k] . '';
				}
			}
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
		$save = $this->process_fields($data, isset($options['ignore_not_set_fields']) ? $options['ignore_not_set_fields'] : false);

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

			// processing sequence
			/* todo: fix sequence here!!!
			if (!empty($this->save_columns)) {
				$settings = new model_co_settings();
				foreach ($this->save_columns as $k=>$v) {
					if (!empty($save[$k])) continue;
					if (!empty($v['sequence'])) {
						if (@$v['sequence']['extended']) {
							$save[$k] = $settings->extended_sequence($v['sequence']['ledger'], $v['sequence']['key']);
						} else {
							$save[$k] = $settings->sequence($v['sequence']['ledger'], $v['sequence']['key']);
						}
					}
				}
			}
			*/

			// saving record to database
			$db = new db($this->db_link);
			$result = $db->save($this->table_name, $save, $this->table_pk, $options);
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
	 * @param array $where
	 * @param array $options
	 *		no_cache
	 *		search
	 * @return array
	 */
	public function get($where = [], $options = []) {
		$options_query = array();
		// if we are caching
		if (!empty($this->cache) && empty($options['no_cache'])) {
			$options_query['cache'] = true;
		}
		$options_query['cache_tags'] = !empty($this->cache_tags) ? array_values($this->cache_tags) : [];
		$options_query['cache_tags'][] = $this->table_name;
		// db object
		$db = new db($this->db_link);
		// where
		$sql = '';
		$sql.= !empty($where) ? (' AND ' . $db->prepare_condition($where)) : '';
		$sql.= !empty($options['search']) ? (' AND (' . $db->prepare_condition($options['search'], 'OR') . ')') : '';
		// order by
		if (!empty($options['orderby'])) {
			$sql.= ' ORDER BY ' . $options['orderby'];
		} else if ($this->table_orderby){
			$sql.= ' ORDER BY ' . $this->table_orderby;
		}
		// limit
		if (!empty($options['limit'])) {
			$sql.= ' LIMIT ' . $options['limit'];
		} else if (!empty($this->table_get_limit)) {
			$sql.= ' LIMIT ' . $this->table_get_limit;
		}
		// pk
		$pk = array_key_exists('pk', $options) ? $options['pk'] : $this->table_pk;
		// columns
		if (!empty($options['columns'])) {
			$columns = $db->prepare_expression($options['columns']);
		} else {
			$columns = '*';
		}
		// querying
		$sql_full = 'SELECT ' . $columns . ' FROM ' . $this->table_name . ' WHERE 1=1' . $sql;
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
		if ($this->cache_memory) {
			cache::$memory_storage[$sql_hash] = & $result['rows'];
		}
		return $result['rows'];
	}
}