<?php

class object_function {

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
	 * Function name including schema in format [schema].[name]
	 *
	 * @var string
	 */
	public $function_name;

	/**
	 * SQLs per submodule
	 *
	 * @var array
	 */
	public $function_sql = [];

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
				$this->db_link = application::get($this->db_link_flag);
			}
			// get default link
			if (empty($this->db_link)) {
				$this->db_link = application::get('flag.global.db.default_db_link');
			}
			// if we could not determine the link we throw exception
			if (empty($this->db_link)) {
				Throw new Exception('Could not determine db link in function!');
			}
		}
		// processing function name
		$ddl = factory::get(['db', $this->db_link, 'ddl_object']);
		$temp = $ddl->is_schema_supported($this->function_name);
		$this->function_name = $temp['full_table_name'];
	}
}