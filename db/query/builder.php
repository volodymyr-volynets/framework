<?php

/**
 * Db query builder
 */
class db_query_builder {

	/**
	 * Db link
	 *
	 * @var string
	 */
	private $db_link;

	/**
	 * Options
	 *
	 * @var array
	 */
	private $options = [];

	
	private $data = [];

	/**
	 * Constructor
	 *
	 * @param string $db_link
	 * @param array $options
	 */
	public function __construct($db_link, $options = []) {
		$this->db_link = $db_link;
		$this->options = $options;
	}

	public function select() {
		
	}

	public function sql() : string {
		
	}

	public function query() {
		
	}
}