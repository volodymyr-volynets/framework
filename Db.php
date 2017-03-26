<?php

class Db {

	/**
	 * Database object
	 *
	 * @var object
	 */
	public $object;

	/**
	 * Backend
	 *
	 * @var string
	 */
	public $backend;

	/**
	 * Options
	 *		cache_link
	 *		crypt_link
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Constructing database object
	 *
	 * @param string $db_link
	 * @param string $class
	 * @param array $options
	 */
	public function __construct($db_link = null, $class = null, $options = []) {
		// if we need to use default link from application
		if (empty($db_link)) {
			$db_link = Application::get('flag.global.default_db_link');
			if (empty($db_link)) {
				Throw new Exception('You must specify database link and/or class!');
			}
		}
		// get object from factory
		$temp = Factory::get(['db', $db_link]);
		// if we have class
		if (!empty($class) && !empty($db_link)) {
			// check if backend has been enabled
			$parts = explode('\\', trim($class, '\\'));
			array_pop($parts);
			if (!\Application::get('dep.submodule.' . implode('.', $parts))) {
				Throw new Exception('You must enable ' . implode('\\', $parts) . ' first!');
			}
			// if we are replacing database connection with the same link we
			// need to manually close database connection
			if (!empty($temp['object']) && $temp['class'] != $class) {
				$object = $temp['object'];
				$object->close();
				unset($this->object);
			}
			// creating new class
			$this->object = new $class($db_link, $options);
			// determining ddl class & object
			$ddl_class = str_replace('\\Base\\Abc123', '\\DDL', $class . '\\Abc123');
			$ddl_object = new $ddl_class();
			// backend
			$this->backend = str_replace(['numbers_backend_db_', '_base'], '', $class);
			// putting every thing into factory
			Factory::set(['db', $db_link], [
				'object' => $this->object,
				'class' => $class,
				'backend' => $this->backend,
				'ddl_class' => $ddl_class,
				'ddl_object' => $ddl_object
			]);
			// set options without credentials
			$this->options = $options;
		} else if (!empty($temp['object'])) {
			$this->object = & $temp['object'];
			$this->backend = $temp['backend'];
		} else {
			Throw new Exception('You must specify database link and/or class!');
		}
	}

	/**
	 * Open database connection
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
	public function escapeArray($value) {
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
	public function save($table, $data, $keys, $options = []) {
		return $this->object->save($table, $data, $keys, $options);
	}

	/**
	 * Insert row(s) into table
	 *
	 * @param string $table
	 * @param array $rows
	 * @param mixed $keys
	 * @return array
	 */
	public function insert($table, $rows, $keys = null, $options = []) {
		return $this->object->insert($table, $rows, $keys, $options);
	}

	/**
	 * Update table rows
	 *
	 * @param string $table
	 * @param array $data
	 * @param mixed $keys
	 * @param array $options
	 * @return array
	 */
	public function update($table, $data, $keys, $options = []) {
		return $this->object->update($table, $data, $keys, $options);
	}

	/**
	 * Delete rows from table
	 *
	 * @param string $table
	 * @param array $data
	 * @param mixed $keys
	 * @param array $options
	 * @return array
	 */
	public function delete($table, $data, $keys, $options = []) {
		return $this->object->delete($table, $data, $keys, $options);
	}

	/**
	 * Generate sequence
	 *
	 * @param string $sequence_name
	 * @param string $type - nextval or curval
	 * @param int $tenant
	 * @param int $module
	 * @return array
	 */
	public function sequence($sequence_name, $type = 'nextval', $tenant = null, $module = null) {
		return $this->object->sequence($sequence_name, $type, $tenant, $module);
	}

	/**
	 * Other methods inherited from base
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		return call_user_func_array([$this->object, $name], $arguments);
	}
}