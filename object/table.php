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
	 * Schema name
	 *
	 * @var string
	 */
	public $schema = '';

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Full table name
	 *
	 * @var string
	 */
	public $full_table_name;

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

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
	 * Audit class
	 *
	 * @var string
	 */
	public $audit_model;

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
	 * Mapping for options(),
	 * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
	 *
	 * @var array
	 */
	public $options_map = [
		//'[table field]' => '[key in array]',
	];

	/**
	 * Condition for options_active()
	 *
	 * @var type
	 */
	public $options_active = [
		//'[table field]' => [value],
	];

	/**
	 * Whether we need to cache this table
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
	 * Attribute class
	 *
	 * @var string
	 */
	public $attributes_model;

	/**
	 * Addresses
	 *
	 * @var boolean
	 */
	public $addresses;

	/**
	 * Addresses class
	 *
	 * @var string
	 */
	public $addresses_model;

	/**
	 * Map with parent table, used in widgets
	 *
	 * @var array
	 */
	public $map = [];

	/**
	 * Virtual class name
	 *
	 * @var string
	 */
	public $virtual_class_name;

	/**
	 * Initiator class
	 *
	 * @var string
	 */
	public $initiator_class = 'object_table';

	/**
	 * Who inserted/updated/posted the record
	 *
	 * @var array
	 */
	public $who = [
		//'inserted' => true,
		//'updated' => true,
		//'posted' => true
	];

	/**
	 * Acl options returned from get, used in options and presets
	 *
	 * @var array
	 */
	public $acl_get_options = [];

	/**
	 * Constructing object
	 *
	 * @param array $options
	 *		skip_db_object
	 * @throws Exception
	 */
	public function __construct($options = []) {
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
		// process table name and schema
		if (!empty($this->schema)) {
			$this->full_table_name = $this->schema . '.' . $this->name;
		} else {
			$this->full_table_name = $this->name;
			$this->schema = '';
		}
		// history table name
		$this->history_name = $this->full_table_name . '__history';
		// process relations
		if (!empty($this->relation)) {
			// add a column if not exists
			if (empty($this->columns[$this->relation['field']])) {
				$this->columns[$this->relation['field']] = ['name' => 'Relation #', 'domain' => 'relation_id_sequence'];
				// add unique constraint
				$this->constraints[$this->relation['field'] . '_un'] = ['type' => 'unique', 'columns' => [$this->relation['field']]];
			}
		} else {
			$this->relation = false;
		}
		// optimistic lock
		if ($this->optimistic_lock) {
			$this->optimistic_lock_column = $this->column_prefix . 'optimistic_lock';
			$this->columns[$this->optimistic_lock_column] = ['name' => 'Optimistic Lock', 'domain' => 'optimistic_lock'];
		}
		// who
		if (!empty($this->who)) {
			foreach ($this->who as $k => $v) {
				$k = strtolower($k);
				$this->columns[$this->column_prefix . $k . '_timestamp'] = ['name' => ucwords($k) . ' Datetime', 'type' => 'timestamp', 'null' => ($k != 'inserted')];
				$this->columns[$this->column_prefix . $k . '_entity_id'] = ['name' => ucwords($k) . ' Entity #', 'domain' => 'entity_id', 'null' => true];
			}
		}
		// process domain in columns
		$this->columns = object_data_common::process_domains_and_types($this->columns);
		// initialize db object
		if (empty($options['skip_db_object'])) {
			$this->db_object = new db($this->db_link);
		}
		// process widgets
		foreach (object_widgets::widget_models as $widget) {
			if (!object_widgets::enabled($widget)) {
				$this->{$widget} = false;
			} else if (!empty($this->{$widget})) {
				$temp = $widget . '_model';
				$this->{$temp} = get_class($this) . '__virtual__' . $widget;
			}
		}
	}

	/**
	 * Process who columns
	 *
	 * @param mixed $types
	 * @param array $row
	 */
	public function process_who_columns($types, & $row, $timestamp = null) {
		if ($types === 'all') $types = array_keys($this->who);
		if (!is_array($types)) $types = [$types];
		if (empty($timestamp)) $timestamp = format::now('timestamp');
		foreach ($types as $type) {
			if (!empty($this->who[$type])) {
				// timestamp
				$row[$this->column_prefix . $type . '_timestamp'] = $timestamp;
				// entity #
				$row[$this->column_prefix . $type . '_entity_id'] = entity::id();
			} else if ($type == 'optimistic_lock') {
				if ($this->optimistic_lock) {
					$row[$this->optimistic_lock_column] = $timestamp;
				}
			}
		}
	}

	/**
	 * Determine model map
	 *
	 * @param string $class
	 * @param string $widget_name
	 * @return boolean
	 * @throws Exception
	 */
	final public function determine_model_map($class, $widget_name) {
		$this->virtual_class_name = $class . '__virtual__' . $widget_name;
		$model = factory::model($class, true);
		if (empty($model->{$widget_name}) || empty($model->{$widget_name}['map'])) {
			Throw new Exception("You must indicate {$widget_name} for {$class} map!");
		}
		// title & name
		$this->title = $model->title . ' ' . ucwords($widget_name);
		$this->name = $model->name . '__' . $widget_name;
		$this->full_table_name = $model->full_table_name . '__' . $widget_name;
		// determine pk
		$columns = [];
		$this->map = $model->{$widget_name}['map'];
		foreach ($model->{$widget_name}['map'] as $k => $v) {
			$columns[$v] = $model->columns[$k];
			if (isset($columns[$v]['domain'])) {
				$columns[$v]['domain'] = str_replace('_sequence', '', $columns[$v]['domain']);
				unset($columns[$v]['type']);
			}
			if (!empty($model->relation['field']) && $k == $model->relation['field']) {
				$this->__relation_pk = $model->pk;
			}
		}
		$this->columns = array_merge_hard($columns, $this->columns);
		return true;
	}

	/**
	 * Process columns
	 *		removes not existing columns
	 *		processes column types
	 *
	 * @param array $data
	 * @param array $options
	 *		boolean ignore_not_set_fields
	 *		boolean skip_type_validation
	 * @return array
	 */
	public function process_columns(& $data, $options = []) {
		$save = [];
		foreach ($this->columns as $k => $v) {
			if (!empty($options['ignore_not_set_fields']) && !array_key_exists($k, $data)) {
				continue;
			}
			if (empty($options['skip_type_validation'])) {
				$temp = object_table_columns::process_single_column_type($k, $v, $data[$k] ?? null);
				if (array_key_exists($k, $temp)) {
					$save[$k] = $temp[$k];
				}
			} else {
				$save[$k] = $data[$k];
			}
		}
		$data = $save;
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
		$data = [];
		$this->acl_get_options = $options;
		// handle acl init
		if (!empty($options['acl'])) {
			$acl_key = get_called_class();
			if (factory::model('object_acl_class', true)->acl_init($acl_key, $data, $this->acl_get_options) === false) {
				return $data;
			}
			$options = $this->acl_get_options;
		}
		$options_query = [];
		// if we are caching
		if (!empty($this->cache) && empty($options['no_cache'])) {
			$options_query['cache'] = true;
		}
		$options_query['cache_tags'] = !empty($this->cache_tags) ? array_values($this->cache_tags) : [];
		$options_query['cache_tags'][] = $this->full_table_name;
		$sql = '';
		// pk
		$pk = array_key_exists('pk', $options) ? $options['pk'] : $this->pk;
		// preset columns
		if (!empty($options['__preset'])) {
			$columns = 'DISTINCT ';
			if (!empty($pk) && count($pk) > 1) {
				$temp = $pk;
				unset($temp[array_search('preset_value', $temp)]);
				$columns.= $this->db_object->prepare_expression($temp) . ', ';
			}
			$columns.= "concat_ws(' ', " . $this->db_object->prepare_expression($options['columns']) . ") preset_value";
			$sql.= ' AND coalesce(' . $this->db_object->prepare_expression($options['columns']) . ') IS NOT NULL';
			// if its a preset we cache
			$options_query['cache'] = true;
		} else { // regular columns
			if (!empty($options['columns'])) {
				$columns = $this->db_object->prepare_expression($options['columns']);
			} else {
				$columns = '*';
			}
		}
		// where
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
		// querying
		$sql_full = 'SELECT ' . $columns . ' FROM ' . $this->full_table_name . ' a WHERE 1=1' . $sql;
		// memory caching
		if ($this->cache_memory) {
			// hash is query + primary key
			$sql_hash = sha1($sql_full . serialize($pk));
			if (isset(cache::$memory_storage[$sql_hash])) {
				return cache::$memory_storage[$sql_hash];
			}
		}
		$result = $this->db_object->query($sql_full, $pk, $options_query);
		if (!$result['success']) {
			Throw new Exception(implode(", ", $result['error']));
		}
		// single row
		if (!empty($options['single_row'])) {
			$data = current($result['rows']);
		} else {
			$data = $result['rows'];
		}
		// handle acl init
		if (!empty($options['acl'])) {
			if (factory::model('object_acl_class', true)->acl_finish($acl_key, $data, $this->acl_get_options) === false) {
				return $data;
			}
		}
		// memory caching
		if ($this->cache_memory) {
			cache::$memory_storage[$sql_hash] = & $data;
		}
		return $data;
	}

	/**
	 * Generate a sequence
	 *
	 * @param string $column
	 * @return int
	 */
	public function sequence($column) {
		$temp = $this->db_object->sequence($this->full_table_name . '_' . $column . '_seq');
		return $temp['rows'][0]['counter'];
	}

	/**
	 * Synchronize sequence
	 *
	 * @param string $column
	 */
	public function synchronize_sequence($column) {
		$result = $this->db_object->query("SELECT max({$column}) max_sequence FROM {$this->full_table_name}");
		if (empty($result['num_rows']) || empty($result['rows'][0]['max_sequence'])) return;
		$sequence = $this->full_table_name . '_' . $column . '_seq';
		$this->db_object->query("SELECT setval('{$sequence}', {$result['rows'][0]['max_sequence']});");
	}

	/**
	 * Reset caches on exit
	 */
	public function reset_cache() {
		// only reset caches if cache link is present
		if (!empty($this->db_object->object->options['cache_link'])) {
			// create unique caches by adding new
			// todo - add miltitenancy
			$tags = array_merge($this->cache_tags, [$this->full_table_name]);
			$hash = sha1(serialize($tags));
			cache::$reset_caches[$this->db_object->object->options['cache_link']][$hash] = $tags;
		}
	}

	/**
	 * Options
	 *
	 * @see $this->get()
	 */
	public function options($options = []) {
		$options['__options'] = true;
		$data = $this->options_query_data($options);
		// process options_map
		if (isset($options['options_map'])) {
			$options_map = $options['options_map'];
		} else if (!empty($this->options_map)) {
			$options_map = $this->options_map;
		} else {
			$options_map = [$this->column_prefix . 'name' => 'name'];
		}
		// if we need to filter options_active
		if (!empty($options['__options_active'])) {
			$options_active = $this->options_active ? $this->options_active : [$this->column_prefix . 'inactive' => 0];
			$data = object_data_common::filter_active_options($data, $options_active, $options['existing_values'] ?? [], $options['skip_values'] ?? []);
		}
		// if we need to prepend values based on pk
		if (!empty($options['__prepend_if_key'])) {
			foreach ($options['__prepend_if_key'] as $k => $v) {
				if (!empty($data[$k])) {
					$data[$k]['__prepend_if_key'] = !empty($options['i18n']) ? i18n(null, $v) : $v;
					$options_map['__prepend_if_key'] = 'name';
				}
			}
		}
		// build options
		$options['column_prefix'] = $this->column_prefix;
		return object_data_common::build_options($data, $options_map, $this->orderby, $options);
	}

	/**
	 * Options active
	 *
	 * @see $this->get()
	 */
	public function options_active($options = []) {
		$options['__options_active'] = true;
		return $this->options($options);
	}

	/**
	 * Presets
	 *
	 * @see $this->get()
	 */
	public function presets($options = []) {
		$options['__preset'] = true;
		if (empty($options['columns'])) {
			$options['columns'] = [$this->column_prefix . 'name'];
		} else if (!is_array($options['columns'])) {
			$options['columns'] = [$options['columns']];
		}
		$options['options_map'] = [
			'preset_value' => 'name'
		];
		$options['orderby'] = [
			'preset_value' => SORT_ASC
		];
		$options['pk'] = [];
		if (!empty($options['where'])) {
			$options['pk'] = array_keys($options['where']);
		}
		$options['pk'][] = 'preset_value';
		$values_found = $this->options($options);
		foreach ($values_found as $k => $v) {
			$values_found[$k]['__parent'] = '__values_found_all__';
		}
		$values_found['__values_found_all__'] = ['name' => i18n_if('Previously Set Values:', $options['i18n'] ?? false), '__parent' => null, 'disabled' => true];
		// eixsting values
		if (!empty($options['existing_values'])) {
			$existing_values = is_array($options['existing_values']) ? $options['existing_values'] : [$options['existing_values']];
			$found = false;
			foreach ($existing_values as $v) {
				if (empty($values_found[$v])) {
					$found = true;
					$values_found[$v] = ['name' => i18n_if($v, $options['i18n'] ?? false), '__parent' => '__values_existing__'];
				}
			}
			if ($found) {
				$values_found['__values_existing__'] = ['name' => i18n_if('Existing Value(s)', $options['i18n'] ?? false), '__parent' => null];
			}
		}
		// convert to tree
		$values_found = helper_tree::convert_by_parent($values_found, '__parent');
		$result = [];
		helper_tree::convert_tree_to_options_multi($values_found, 0, ['name_field' => 'name'], $result);
		return $result;
	}

	/**
	 * Presets active
	 *
	 * @see $this->get()
	 */
	public function presets_active($options = []) {
		$options['__options_active'] = true;
		return $this->presets($options);
	}

	/**
	 * Query data for options
	 *
	 * @param array $options
	 * @return array
	 */
	public function options_query_data(& $options) {
		// column prefix
		if (empty($options['column_prefix'])) {
			$options['column_prefix'] = $this->column_prefix;
		}
		// handle pk
		if (!array_key_exists('pk', $options)) {
			$options['pk'] = $this->pk;
		}
		$pk = $options['pk'];
		// if compound key
		if (count($pk) > 1) {
			$temp = $pk;
			$last = array_pop($temp);
			foreach ($temp as $v) {
				if (empty($options['where'][$v])) {
					return [];
				}
			}
		}
		$data = $this->get($options);
		// merge acl returned from get
		$options = $this->acl_get_options;
		// if compound key
		if (!empty($temp)) {
			foreach ($temp as $v) {
				if (!isset($data[$options['where'][$v]])) {
					return [];
				}
				$data = $data[$options['where'][$v]];
			}
		}
		return $data;
	}

	/**
	 * Multi level options
	 *
	 * @see $this->get()
	 */
	/*
	 * todo retire
	public function optmultis($options = []) {
		// todo - retire in favour of tree
		if (empty($this->optmultis_map)) {
			return [];
		} else {
			$data = $this->get($options);
			$optmultis_map = $this->optmultis_map;
			return object_data_common::optmultis($data, $optmultis_map, $options);
		}
	}
	*/

	/**
	 * Check unique constraint
	 *
	 * @param string $column
	 * @param mixed $value
	 * @param mixed $pk
	 * @return boolean
	 */
	/*
	 * todo - add multi tenancy
	public function check_unique_constraint($column, $value, $pk) {

		// todo refactor to allow multiple criterias

		if (is_string($value)) {
			$value = "'" . $this->db_object->escape($value) . "'";
		}
		if (!empty($pk)) {
			if (is_string($pk)) {
				$pk = "'" . $this->db_object->escape($pk) . "'";
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
		$result = $this->db_object->query($sql);
		return ($result['num_rows'] <> 0 ? true : false);
	}
	*/

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
	public static function exists_static($options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->exists($options);
	}

	/**
	 * Create collection object
	 *
	 * @return object
	 */
	public static function collection() {
		return object_collection::collection_to_model([
			'model' => get_called_class()
		]);
	}

	/**
	 * Check if table exists in database
	 *
	 * @return boolean
	 */
	public function db_present() {
		$temp_result = $this->db_object->query("SELECT count(*) counter FROM (" . $this->db_object->sql_helper('fetch_tables') . ") a WHERE a.schema_name = '{$this->schema}' AND table_name = '{$this->name}'");
		return !empty($temp_result['rows'][0]['counter']);
	}

	/**
	 * Insert multiple rows
	 *
	 * @param array $data
	 * @return array
	 */
	/*
	public function insert($data) {
		return $this->db_object->insert($this->full_table_name, $data, null);
	}
	*/
}