<?php

namespace Object;
abstract class Check {

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
	 * Full check name
	 *
	 * @var string
	 */
	public $full_check_name;

	/**
	 * Full table name
	 *
	 * @var string
	 */
	public $full_table_name;

	/**
	 * Definition
	 *
	 * @var string
	 */
	public $definition;

	/**
	 * Query
	 *
	 * @var object
	 */
	public $query;

	/**
	 * SQL version
	 *
	 * @var string
	 */
	public $sql_version;

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
				Throw new \Exception('Could not determine db link in check!');
			}
		}
		// SQL version
		if (empty($this->sql_version)) {
			Throw new \Exception('You must provide SQL version!');
		}
		// table name
		if (empty($this->full_table_name)) {
			Throw new \Exception('You must provide table name!');
		}
		// check must end with _check
		if (substr($this->name, -6, 6) !== '_check') {
			Throw new \Exception('Check must end with "_check"!');
		}
		// see if we have special handling
		$db_object = \Factory::get(['db', $this->db_link, 'object']);
		if (method_exists($db_object, 'handleName')) {
			$this->full_check_name = $db_object->handleName($this->schema, $this->name);
		} else { // process table name and schema
			if (!empty($this->schema)) {
				$this->full_check_name = $this->schema . '.' . $this->name;
			} else {
				$this->full_check_name = $this->name;
				$this->schema = '';
			}
		}
		// we need to fix full table name
		if (!empty($this->schema) && strpos($this->full_table_name, '.') === false) {
			$this->full_table_name = $this->schema . '.' . $this->full_table_name;
		}
		// initialize query object
		$this->query = new \Object\Query\Builder($this->db_link);
		$this->query->check();
		$this->query->from($this->full_table_name, 'table_name');
		$this->query->from($this->full_check_name, 'check_name');
		$this->definition();
		$this->definition = $this->query->sql();
	}

	/**
	 * Definition
	 *
	 * Important: all columns in where clauses should be prefixed with "[NEW]."
	 */
	abstract public function definition();
}