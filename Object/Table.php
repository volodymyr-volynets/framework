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
	 * Name
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
	 * Whether its a temp table
	 *
	 * @var bool
	 */
	public $temporary = false;

	/**
	 * Data asset (default)
	 *
	 * @var array
	 */
	public $data_asset = [
		'classification' => 'public',
		'protection' => 0,
		'scope' => 'global'
	];

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
			$this->full_table_name = $db_object->handleName($this->schema, $this->name, ['temporary' => $this->temporary]);
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
}