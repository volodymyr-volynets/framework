<?php

namespace Object;
class Function2 {

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
	 * @var string
	 */
	public $backend;

	/**
	 * Full function name
	 *
	 * @var string
	 */
	public $full_function_name;

	/**
	 * Header
	 *
	 * @var string
	 */
	public $header;

	/**
	 * Definition
	 *
	 * @var string
	 */
	public $definition;

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
				Throw new \Exception('Could not determine db link in function!');
			}
		}
		// SQL version
		if (empty($this->sql_version)) {
			Throw new \Exception('You must provide SQL version!');
		}
		// version in definition
		if (strpos($this->definition, '/* version */') === false) {
			Throw new \Exception('You must include /* version */ in definition!');
		}
		// see if we have special handling
		$db_object = \Factory::get(['db', $this->db_link, 'object']);
		if (method_exists($db_object, 'handleName')) {
			$this->full_function_name = $db_object->handleName($this->schema, $this->name);
		} else { // process table name and schema
			if (!empty($this->schema)) {
				$this->full_function_name = $this->schema . '.' . $this->name;
			} else {
				$this->full_function_name = $this->name;
				$this->schema = '';
			}
		}
		// replace version
		$this->definition = str_replace('/* version */', '/* [[[SQL Version: ' . $this->sql_version . ']]] */', $this->definition);
	}
}