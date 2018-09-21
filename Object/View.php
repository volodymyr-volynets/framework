<?php

namespace Object;
abstract class View extends \Object\Table\Options {

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
	 * Module code
	 *
	 * @var string
	 */
	public $module_code;

	/**
	 * Schema
	 *
	 * @var string
	 */
	public $schema;

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Backend
	 *
	 * @var string|array
	 */
	public $backend;

	/**
	 * Full view name
	 *
	 * @var string
	 */
	public $full_view_name;

	/**
	 * Table primary key in format ['id1'] or ['id1', 'id2', 'id3']
	 *
	 * @var array
	 */
	public $pk;

	/**
	 * Definition
	 *
	 * @var string
	 */
	public $definition;

	/**
	 * Grant tables
	 *
	 * @var array
	 */
	public $grant_tables = [];

	/**
	 * Query
	 *
	 * @var object
	 */
	public $query;

	/**
	 * Column prefix
	 *
	 * @var string
	 */
	public $column_prefix;

	/**
	 * SQL version
	 *
	 * @var string
	 */
	public $sql_version;

	/**
	 * SQL last query
	 *
	 * @var string
	 */
	public $sql_last_query;

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
	 * Constructing object
	 *
	 * @throws Exception
	 */
	public function __construct() {
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
				Throw new \Exception('Could not determine db link in view!');
			}
		}
		// SQL version
		if (empty($this->sql_version)) {
			Throw new \Exception('You must provide SQL version!');
		}
		// see if we have special handling
		$db_object = \Factory::get(['db', $this->db_link, 'object']);
		if (method_exists($db_object, 'handleName')) {
			$this->full_view_name = $db_object->handleName($this->schema, $this->name);
		} else { // process table name and schema
			if (!empty($this->schema)) {
				$this->full_view_name = $this->schema . '.' . $this->name;
			} else {
				$this->full_view_name = $this->name;
				$this->schema = '';
			}
		}
		// tenant column
		if ($this->tenant) {
			$this->tenant_column = $this->column_prefix . 'tenant_id';
		}
		// cache tags
		$this->cache_tags[] = $this->full_view_name;
		if ($this->tenant) $this->cache_tags[] = '+numbers_tenant_' . \Tenant::id();
		// initialize query object
		$this->query = new \Object\Query\Builder($this->db_link);
		$this->query->select();
		$this->definition();
		$this->definition = $this->query->sql();
		$this->grant_tables = array_values($this->query->data['from']);
	}

	/**
	 * Definition
	 */
	abstract public function definition();
}