<?php

namespace Object;
abstract class View {

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
	 * Schema
	 *
	 * @var string
	 */
	public $schema;

	/**
	 * Function name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Backend
	 *
	 * @var string
	 */
	public $backend;

	/**
	 * Full view name
	 *
	 * @var string
	 */
	public $full_view_name;

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
	 * Tenant
	 *
	 * @var int
	 */
	public $tenant;

	/**
	 * Column prefix
	 *
	 * @var string
	 */
	public $column_prefix;

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
				Throw new \Exception('Could not determine db link in trigger!');
			}
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
		// initialize query object
		$this->query = new \Object\Query\Builder($this->db_link);
		$this->definition();
		$this->definition = $this->query->sql();
		$this->grant_tables = array_values($this->query->data['from']);
		// view must not contain asterisk
		$temp = explode('FROM', strtoupper($this->definition));
		if (strpos($temp[0], '*') !== false) {
			Throw new \Exception('View ' . $this->full_view_name . ' contains asterisk!');
		}
	}

	/**
	 * Definition
	 */
	abstract public function definition();
}