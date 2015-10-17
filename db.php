<?php

class db implements numbers_backend_db_interface_base {

	/**
	 * Database object
	 *
	 * @var object
	 */
	public $object;

	/**
	 * Constructing database object
	 *
	 * @param string $db_link
	 * @param string $class
	 */
	public function __construct($db_link = null, $class = null) {
		// if we need to use default link from application
		if ($db_link == null) {
			$db_link = application::get(['flag', 'global', 'db', 'default_link']);
			if (empty($db_link)) {
				Throw new Exception('You must specify database link and/or class!');
			}
		}

		// get object from factory
		$temp = factory::get(['db', $db_link]);

		// if we have class
		if (!empty($class) && !empty($db_link)) {
			// replaces in case we have it as submodule
			$class = str_replace('.', '_', trim($class));

			// if we are replacing database connection with the same link we
			// need to manually close database connection
			if (!empty($temp['object']) && $temp['class'] != $class) {
				$object = $temp['object'];
				$object->close();
				unset($this->object);
			}

			// creating new class
			$this->object = new $class($db_link);
			factory::set(['db', $db_link], ['object' => $this->object, 'class' => $class]);
		} else if (!empty($temp['object'])) {
			$this->object = $temp['object'];
		} else {
			Throw new Exception('You must specify database link and/or class!');
		}
	}

	/**
	 * Open datbase connecton
	 *
	 * @param array $options
	 * @return array
	 */
	public function connect($options) {
		return $this->object->connect($options);
	}

	/**
	 * Close database connection
	 */
	public function close() {
		$this->object->close();
	}

	/**
	 * Query database
	 * 
	 * @param string $sql
	 * @param mixed $key
	 * @param array $options
	 * @return array
	 */
	public function query($sql, $key = null, $options = []) {
		return $this->object->query($sql, $key, $options);
	}

	/**
	 * Begin transaction
	 *
	 * @return array
	 */
	public function begin() {
		return $this->object->begin();
	}

	/**
	 * Commit transaction
	 *
	 * @return array
	 */
	public function commit() {
		return $this->object->commit();
	}

	/**
	 * Roll transaction back
	 *
	 * @return array
	 */
	public function rollback() {
		return $this->object->rollback();
	}

	/**
	 * Escape value
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function escape($value) {
		return $this->object->escape($value);
	}

	/**
	 * Escape array of string
	 *
	 * @param array $value
	 * @return array
	 */
	public function escape_array($value) {
		return $this->object->escape_array($value);
	}

	/**
	 * Save row
	 *
	 * @param string $table
	 * @param array $data
	 * @param mixed $keys
	 * @return array
	 */
	public function save($table, $data, $keys) {
		return $this->object->save($table, $data, $keys);
	}

	/**
	 * Insert row(s) into table
	 *
	 * @param string $table
	 * @param array $rows
	 * @return array
	 */
	public function insert($table, $rows) {
		return $this->object->insert($table, $rows);
	}
}