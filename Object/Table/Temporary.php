<?php

namespace Object\Table;
class Temporary extends \Object\Table {

	/**
	 * Temporary
	 *
	 * @var bool
	 */
	public $temporary = true;

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	public function __construct($options = array()) {
		parent::__construct($options);
		// create temp table
		if (!empty($options['create'])) {
			$this->createTempTable();
		}
	}

	/**
	 * Create temporary table
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function createTempTable() : array {
		// create temporary table
		$result = $this->db_object->createTempTable($this->full_table_name, $this->columns, $this->pk, $this->options);
		if (!$result['success']) {
			Throw new \Exception('Could not create temporary table!');
		}
		return $result;
	}
}