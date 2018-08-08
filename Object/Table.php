<?php

namespace Object;
class Table extends \Object\Table\Options {

	/**
	 * Include common trait
	 */
	use \Object\Table\Trait2;

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
	 * @var array
	 */
	public $options_active = [
		//'[table field]' => [value],
	];

	/**
	 * Skip translations in options
	 *
	 * @var boolean
	 */
	public $options_skip_i18n = false;

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
	public $attributes_model;

	/**
	 * Addresses
	 *
	 * @var boolean
	 */
	public $addresses;
	public $addresses_model;

	/**
	 * Audit
	 *
	 * @var bool
	 */
	public $audit = false;
	public $audit_model;

	/**
	 * Comments
	 *
	 * @var bool
	 */
	public $comments = false;
	public $comments_model;

	/**
	 * Documents
	 *
	 * @var bool
	 */
	public $documents = false;
	public $documents_model;

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
	public $initiator_class = 'Object\Table';

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
	 * Tenant
	 *
	 * @var boolean
	 */
	public $tenant = false;

	/**
	 * Tenant column
	 *
	 * @var string
	 */
	public $tenant_column;

	/**
	 * Module
	 *
	 * @var boolean
	 */
	public $module = false;

	/**
	 * Module column
	 *
	 * @var string
	 */
	public $module_column;

	/**
	 * SQL Last query
	 *
	 * @var string
	 */
	public $sql_last_query;

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Triggers
	 *
	 * @var array
	 */
	public $triggers = [];

	/**
	 * Acl
	 *
	 * @var array
	 */
	public $acl = [];

	/**
	 * Constructing object
	 *
	 * @param array $options
	 *		skip_db_object
	 * @throws Exception
	 */
	public function __construct($options = []) {
		$this->options = $options;
		// we need to handle overrrides
		parent::overrideHandle($this);
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
				Throw new \Exception('Could not determine db link in model!');
			}
		}
		// see if we have special handling
		$db_object = \Factory::get(['db', $this->db_link, 'object']);
		if (method_exists($db_object, 'handleName')) {
			$this->full_table_name = $db_object->handleName($this->schema, $this->name);
		} else { // process table name and schema
			if (!empty($this->schema)) {
				$this->full_table_name = $this->schema . '.' . $this->name;
			} else {
				$this->full_table_name = $this->name;
				$this->schema = '';
			}
		}
		// tenant column
		if ($this->tenant) {
			$this->tenant_column = $this->column_prefix . 'tenant_id';
		}
		// module column
		if ($this->module) {
			$this->module_column = $this->column_prefix . 'module_id';
		}
		// cache tags
		$this->cache_tags[] = $this->full_table_name;
		if ($this->tenant) $this->cache_tags[] = '+numbers_tenant_' . \Tenant::id();
		// history table name
		$this->history_name = $this->full_table_name . '__history';
		// process relations
		if (!empty($this->relation)) {
			// add a column if not exists
			if (empty($this->columns[$this->relation['field']])) {
				$this->columns[$this->relation['field']] = ['name' => 'Relation #', 'domain' => 'relation_id_sequence'];
				// add unique constraint
				$temp = [];
				if ($this->tenant) $temp[] = $this->tenant_column; // a must
				$temp[] = $this->relation['field'];
				$this->constraints[$this->relation['field'] . '_un'] = ['type' => 'unique', 'columns' => $temp];
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
				$this->columns[$this->column_prefix . $k . '_user_id'] = ['name' => ucwords($k) . ' User #', 'domain' => 'user_id', 'null' => true];
			}
		}
		// process domain in columns
		$this->columns = \Object\Data\Common::processDomainsAndTypes($this->columns);
		// initialize db object
		if (empty($options['skip_db_object'])) {
			$this->db_object = new \Db($this->db_link);
		}
		// process widgets
		$widgets = \Object\ACL\Resources::getStatic('widgets');
		$widgets = array_merge(['attributes' => false, 'addresses' => false, 'audit' => false, 'comments' => false, 'documents' => false], $widgets);
		foreach ($widgets as $widget => $widget_data) {
			if (!empty($this->{$widget}) && !empty($widget_data)) {
				$this->{$widget . '_model'} = '\\' . get_class($this) . '\0Virtual0\Widgets\\' . ucwords($widget);
			} else {
				$this->{$widget} = false;
			}
		}
	}

	/**
	 * Process who columns
	 *
	 * @param mixed $types
	 * @param array $row
	 */
	public function processWhoColumns($types, & $row, $timestamp = null) {
		if ($types === 'all') $types = array_keys($this->who);
		if (!is_array($types)) $types = [$types];
		if (empty($timestamp)) $timestamp = \Format::now('timestamp');
		foreach ($types as $type) {
			if (!empty($this->who[$type])) {
				// timestamp
				$row[$this->column_prefix . $type . '_timestamp'] = $timestamp;
				// user #
				$row[$this->column_prefix . $type . '_user_id'] = \User::id();
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
	 * @param string $virtual_class_name
	 * @param array $options
	 * @return boolean
	 * @throws Exception
	 */
	final public function determineModelMap($class, $widget_name, $virtual_class_name, $options = []) {
		$this->virtual_class_name = $virtual_class_name;
		$model = \Factory::model($class, true, [$options]);
		if (empty($model->{$widget_name}) || empty($model->{$widget_name}['map'])) {
			Throw new \Exception("You must indicate {$widget_name} for {$class} map!");
		}
		// title & name
		$this->title = $model->title . ' ' . ucwords($widget_name);
		$this->name = $model->name . '__' . $widget_name;
		$this->full_table_name = $model->full_table_name . '__' . $widget_name;
		$this->module_code = $model->module_code;
		$this->data_asset = $model->data_asset;
		$this->tenant = $model->tenant;
		$this->module = $model->module;
		// determine pk
		$columns = [];
		$this->map = $model->{$widget_name}['map'];
		foreach ($model->{$widget_name}['map'] as $k => $v) {
			$columns[$v] = $model->columns[$k];
			if (isset($columns[$v]['domain'])) {
				$columns[$v]['domain'] = str_replace('_sequence', '', $columns[$v]['domain']);
				unset($columns[$v]['type'], $columns[$v]['sequence']);
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
	public function processColumns(& $data, $options = []) {
		$save = [];
		foreach ($this->columns as $k => $v) {
			if (!empty($options['ignore_not_set_fields']) && !array_key_exists($k, $data)) {
				continue;
			}
			if (empty($options['skip_type_validation'])) {
				$temp = \Object\Table\Columns::processSingleColumnType($k, $v, $data[$k] ?? null);
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
	 * Generate a sequence
	 *
	 * @param string $column
	 * @param string $type
	 * @param int|null $tenant
	 * @param int|null $module
	 * @return int
	 */
	public function sequence(string $column, string $type = 'nextval', $tenant = null, $module = null) : int {
		// add tenant
		if (empty($tenant) && $this->tenant) {
			$tenant = \Tenant::id();
		}
		$temp = $this->db_object->sequence($this->full_table_name . '_' . $column . '_seq', $type, $tenant, $module);
		return $temp['rows'][0]['counter'] ?? 0;
	}

	/**
	 * Synchronize sequence
	 *
	 * @param string $column
	 */
	public function synchronizeSequence($column) {
		$result = $this->db_object->query("SELECT max({$column}) max_sequence FROM {$this->full_table_name}");
		if (empty($result['num_rows']) || empty($result['rows'][0]['max_sequence'])) return;
		$sequence = $this->full_table_name . '_' . $column . '_seq';
		$this->db_object->query("SELECT setval('{$sequence}', {$result['rows'][0]['max_sequence']});");
	}

	/**
	 * Reset caches on exit
	 */
	public function resetCache() {
		// only reset caches if cache link is present
		if (!empty($this->db_object->object->options['cache_link'])) {
			$tags = array_unique($this->cache_tags);
			sort($tags, SORT_STRING);
			$hash = sha1(serialize($tags));
			\Cache::$reset_caches[$this->db_object->object->options['cache_link']][$hash] = $tags;
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
	 * Options (static)
	 *
	 * @see $this::get()
	 */
	public static function optionsStatic(array $options = []) {
		$class = get_called_class();
		$object = new $class();
		return $object->options($options);
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
	 * Create collection object
	 *
	 * @param array $options
	 * @return object
	 */
	public function collection(array $options = []) : \Object\Collection {
		return self::collectionStatic($options);
	}

	/**
	 * Create collection object (static)
	 *
	 * @param array $options
	 * @return object
	 */
	public static function collectionStatic(array $options = []) : \Object\Collection {
		$options['model'] = get_called_class();
		return \Object\Collection::collectionToModel($options);
	}

	/**
	 * Check if table exists in database
	 *
	 * @return boolean
	 */
	public function dbPresent() {
		return $this->db_object->tableExists($this->full_table_name);
	}

	/**
	 * Aggregate
	 *
	 * @param array $options
	 *		array columns
	 *		array where
	 *		array groupby
	 *		array pk
	 * @return array
	 */
	public static function aggregate(array $options) : array {
		$query = self::queryBuilderStatic()->select();
		if (!empty($options['columns'])) {
			$query->columns($options['columns']);
		}
		if (!empty($options['where'])) {
			$query->whereMultiple('AND', $options['where']);
		}
		if (!empty($options['groupby'])) {
			$query->groupby($options['groupby']);
		}
		$result = $query->query($options['pk'] ?? null);
		return $result['rows'];
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