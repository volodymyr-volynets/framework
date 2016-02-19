<?php

class object_sequence {

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
	 * Sequence name including schema in format [schema].[name]
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Sequence type
	 *
	 * @var string
	 */
	public $type = "simple";

	/**
	 * Sequence prefix
	 *
	 * @var string
	 */
	public $prefix;

	/**
	 * Sequence length
	 *
	 * @var int
	 */
	public $length = 0;

	/**
	 * Sequence suffix
	 *
	 * @var string
	 */
	public $suffix;

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
				Throw new Exception('Could not determine db link in sequnce!');
			}
		}
		// processing sequence name
		$ddl = factory::get(['db', $this->db_link, 'ddl_object']);
		$temp = $ddl->is_schema_supported($this->name);
		$this->name = $temp['full_table_name'];
	}

	/**
	 * Get next sequence number
	 *
	 * @return array
	 */
	public function nextval() {
		return $this->get_by_type('nextval');
	}

	/**
	 * Cet current sequence value
	 *
	 * @return type
	 */
	public function currval() {
		return $this->get_by_type('currval');
	}

	/**
	 * Get next sequence number
	 *
	 * @return type
	 */
	private function get_by_type($type) {
		$result = [
			'success' => false,
			'error' => [],
			'simple' => null,
			'advanced' => null
		];

		// todo: move it to model

		$db = new db($this->db_link);
		$table_model = new numbers_backend_db_class_model_sequences();
		$temp = $db->sequence($this->name, $table_model->name, $type);
		if (!$temp['success']) {
			$result['error'] = $temp['error'];
		} else if (!empty($temp['rows'][0])) {
			if ($temp['rows'][0]['sm_sequence_type'] == 'advanced') {
				$result['simple'] = $temp['rows'][0]['counter'];
				$sequence = $result['simple'] . '';
				// if we need to pad sequence
				if (strlen($sequence) < $temp['rows'][0]['sm_sequence_length']) {
					$sequence = str_pad($sequence, $temp['rows'][0]['sm_sequence_length'], '0', STR_PAD_LEFT);
				}
				$result['advanced'] = $temp['rows'][0]['sm_sequence_prefix'] . $sequence . $temp['rows'][0]['sm_sequence_suffix'];
			} else {
				$result['simple'] = $result['advanced'] = $temp['rows'][0]['counter'];
			}
			$result['success'] = true;
		}
		return $result;
	}
}