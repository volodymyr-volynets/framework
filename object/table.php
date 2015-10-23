<?php

class object_table {

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
	 * Table name including schema in format [schema].[name]
	 *
	 * @var string
	 */
	public $table_name;

	/**
	 * Table primary key in format 'id' or ['id1', 'id2']
	 *
	 * @var string or array
	 */
	public $table_pk;

	/**
	 * Table default order
	 *
	 * @var string or array
	 */
	public $table_orderby = null;

	/**
	 * Table columns
	 *
	 * @var array
	 */
	public $table_columns = [
		//'id' => array('name' => '#', 'type' => 'bigserial'),
		//'name' => array('name' => 'Name', 'type' => 'varchar', 'length' => 255),
	];

	/**
	 * Tables constraints
	 *
	 * @var array
	 */
	public $table_constraints = [
		//'name_un' => array('type' => 'unique', 'columns' => ['name']),
	];

	/**
	 * Tables indexes
	 *
	 * @var array
	 */
	public $table_indexes = [
		//'name_idx' => array('type' => 'btree', 'columns' => ['name']),
	];

	/**
	 * Final table columns after processing inheritance
	 *
	 * @var type
	 */
	public $table_columns_final;

	/**
	 * Whether its a table with a history, and we do point in time quering
	 *
	 * @var bool
	 */
	public $table_history = false;

	/**
	 * Whether we need to keep audit log for this table
	 *
	 * @var bool
	 */
	public $table_audit = false;

	/**
	 * Table row details for crud::row() function
	 *
	 * @var type
	 */
 	public $table_row_details = [
		//'[model]' => ['map' => ['[filed from parent table]'=>'[field from child table]'], 'pk' => ['[arranges child data]'], 'counter' => '[conter filed form parent table]']
 	];

	/**
	 * Mapping for crud::options(),
	 * Note if you need to map the same field to multiple array keys we could prepend one or more "*" (asterisks)
	 *
	 * @var array
	 */
	public $table_options_map = [
		//'[table field]' => '[key in array]',
	];

	/**
	 * Condition for crud::options_active()
	 *
	 * @var type
	 */
	public $table_options_active = [
		//'[table field]' => [value],
	];

	/**
	 * Wherether we need to cache this table
	 *
	 * @var bool
	 */
	public $cache = false;

	/**
	 * Cache link
	 *
	 * @var string 
	 */
	public $cache_link;

	/**
	 * Cache link override
	 *
	 * @var string
	 */
	public $cache_link_flag;

	/**
	 * These tags will be added to caches and then will be used in cache::gc();
	 *
	 * @var type
	 */
	public $cache_tags = '';

	/**
	 * Whether we need to cache in memory
	 *
	 * @var bool
	 */
	public $cache_memory = false;

	/**
	 * Verify data against columns
	 *
	 * @param array $data
	 * @param array $columns
	 */
	public function table_save_verify(& $data) {
		$result = [
			'success' => false,
			'error' => []
		];

		// todo: add processing here, basic one

		return $result;
	}

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
				$this->db_link = application::get(explode('.', $this->db_link_flag));
			}
			// get default link
			if (empty($this->db_link)) {
				$this->db_link = application::get(explode('.', 'flag.global.db.default_db_link'));
			}
			// if we could not determine the link we throw exception
			if (empty($this->db_link)) {
				Throw new Exception('Could not determine db link in model!');
			}
		}
	}
}