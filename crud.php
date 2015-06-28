<?php

class crud {
	
	/**
	 * We would store data in memory for future reuse
	 * 
	 * @var unknown_type
	 */
	public static $cache_memory_container = array();
	
	/**
	 * Constructor
	 * 
	 * @throws Exception
	 */
	function __construct( ) {
		if (empty($this->link)) Throw new Exception('Link?');
		if (empty($this->table)) Throw new Exception('Table?');
		if (empty($this->pk)) Throw new Exception('Primary Key?');
		if (empty($this->orderby)) Throw new Exception('Order By?');
	}
	
	/**
	 * Get data
	 * 
	 * @param array $where
	 * @return array
	 */
	public function get($where = array(), $options2 = array(), $search = array()) {
		$options = array();
		// cache
		if (!empty($this->cache) && empty($options2['no_cache'])) $options['cache'] = true;
		$options['cache_tags'] = !empty($this->cache_tags) ? array_values($this->cache_tags) : array();
		$options['cache_tags'][] = $this->table;
		// where
		$sql = '';
		$sql.= !empty($where) ? (' AND ' . db::prepare_condition($where)) : '';
		$sql.= !empty($search) ? (' AND (' . db::prepare_condition($search, 'OR') . ')') : '';
		$link = isset($this->link) ? $this->link : 'default';
		// order by
		if (!empty($options2['orderby'])) {
			$sql.= ' ORDER BY ' . $options2['orderby'];
		} else {
			$sql.= ' ORDER BY ' . $this->orderby . ($this->orderdesc ? ' DESC' : '');
		}
		// limit
		if (!empty($this->get_limit)) $sql.= ' LIMIT ' . $this->get_limit;
		// querying
		$sql_full = "SELECT * FROM " . $this->table . " WHERE 1=1 " . $sql;
		$sql_full_md5 = md5($sql_full);
		if (!empty($this->cache_memory) && isset(self::$cache_memory_container[$sql_full_md5])) {
			return self::$cache_memory_container[$sql_full_md5];
		}
		$result = db::query($sql_full, $this->pk, $options, $link);
		if (!empty($this->cache_memory) && !isset(self::$cache_memory_container[$sql_full_md5])) {
			self::$cache_memory_container[$sql_full_md5] = & $result['rows'];
		}
		return $result['rows'];
	}
	
	/**
	 * Get one row based on primary key
	 * 
	 * @param array $pk
	 * @return multitype:
	 */
	public function row($where, $one = true, $pk = null, $load_details = true, $order_by = '', $offset = 0, $limit = 0) {
		$link = !empty($this->link) ? $this->link : 'default';
		$sql = !empty($where) ? (' AND ' . db::prepare_condition($where, 'AND', $link)) : '';
		$order_by = $order_by ? (' ORDER BY ' . $order_by) : '';
		$offset = $offset ? (' OFFSET ' . $offset) : '';
		$limit = $limit ? (' LIMIT ' . $limit) : '';
		$result_main = db::query("SELECT * FROM " . $this->table . " WHERE 1=1" . $sql . $order_by . $offset . $limit, $pk, array(), $link);

		// loading details, key would be table name
		if ($load_details && !empty($this->details) && !empty($result_main['rows'])) {
			foreach ($this->details as $k=>$v) {
				$model = new $k();
				foreach ($result_main['rows'] as $k2=>$v2) {
					if (!empty($this->details['counter']) && empty($v2[$this->details['counter']])) continue;
					$where = array();
					foreach ($v['key'] as $k3=>$v3) $where[$v3] = $v2[$k3];
					$result_main['rows'][$k2][$model->table] = $model->row($where, false, @$v['pk']);
				}
			}
		}
		return $one ? array_shift($result_main['rows']) : $result_main['rows'];
	}
	
	/**
	 * Options for select element
	 * 
	 * @param array $where
	 * @param array $map
	 * @throws Exception
	 * @return array
	 */
	public function options($where = array(), $map = null) {
		$map = !empty($map) ? $map : $this->options_map;
		if (empty($map)) Throw new Exception('options_map?');
		$data = $this->get($where);
		$data = remap($data, $map);
		natsort2($data);
		return $data;
	}
	
	/**
	 * Active options for system use
	 * 
	 * @param array $map
	 * @return array
	 */
	public function options_active($map = null) {
		if (!isset($this->options_active)) Throw new Exception('options_active?');
		return $this->options(@$this->options_active, $map);
	}
	
	/**
	 * Searching for particular data
	 * 
	 * @param unknown_type $where
	 * @param unknown_type $map
	 * @param unknown_type $search_string
	 * @return Ambigous <multitype:, string>
	 */
	public function options_search($where, $map = null, $search_string = '') {
		$map = !empty($map) ? $map : $this->options_map;
		if (empty($map)) Throw new Exception('options_map?');
		$search = array();
		if (!empty($search_string)) {
			foreach ($map as $k=>$v) {
				$k = str_replace('*', '', $k);
				$search['lower(' . $k . ' || \'\'),ILIKE%'] = $search_string . '';
			}
		}
		$data = $this->get($where, array(), $search);
		$data = remap($data, $map);
		natsort2($data);
		return $data;
	}
	
	/**
	 * Active search
	 * 
	 * @param array $map
	 * @param string $search_string
	 * @return array
	 */
	public function options_search_active($where, $map = null, $search_string = '') {
		if (!isset($this->options_active)) Throw new Exception('options_active?');
		$where = array_merge2($where, $this->options_active);
		return $this->options_search($where, $map, $search_string);
	}
	
	/**
	 * Save/create a record
	 * 
	 * @param array $data
	 * @return array
	 */
	public function save($data) {
    	$result = array(
   			'success' => false,
    		'error' => array(),
    		'data' => array(),
    		'inserted' => false
    	);
		
		// populating fields
		$save = $this->process_fields($data, @$data['ignore_not_set_fields']);
		
		// verifying
		do {
			if (empty($save)) {
				$result['error'][] = 'You must specify atleast one field!';
			}
			
			// verification against columns
			if (!empty($this->save_columns)) {
				// verification
				$this->verify_fields($save, $this->save_columns, $result);
			
				// additional verification
				if (method_exists($this, 'save_verify')) {
					array_merge3($result['error'], $this->save_verify($save, $this->save_columns));
				}
			}
			
			if (!empty($result['error'])) break;
			
			// processing sequence
			if (!empty($this->save_columns)) {
				$settings = new model_co_settings();
				foreach ($this->save_columns as $k=>$v) {
					if (!empty($save[$k])) continue;
					if (!empty($v['sequence'])) {
						if (@$v['sequence']['extended']) {
							$save[$k] = $settings->extended_sequence($v['sequence']['ledger'], $v['sequence']['key']);
						} else {
							$save[$k] = $settings->sequence($v['sequence']['ledger'], $v['sequence']['key']);
						}
					}
				}
			}
			
			// saving record to database
			$link = isset($this->link) ? $this->link : 'default';
			$save_result = db::save($this->table, $save, $this->pk, $link);
			if (!$save_result['success']) {
				array_merge3($result['error'], $save_result['error']);
				break;
			}
			$result['data'] = $save_result['data'];
			$result['inserted'] = $save_result['inserted'];
			$result['success'] = true;
			// now we need to reset cache
			if (empty($data['do_not_reset_cache'])) {
				$this->reset_cache();
			}
		} while(0);
		return $result;
	}
	
	/**
	 * Remove one row based on primary key
	 *  
	 * @param array $data
	 */
	public function remove($data) {
		$result = array(
			'success' => false,
			'error' => array()
		);
		// populating fields
		$save = $this->process_fields($data);
		$pk = is_array($this->pk) ? $this->pk : array($this->pk);
		// where clause
		$where = array();
		foreach ($pk as $key) {
			if (!empty($save[$key])) $where[$key] = $save[$key];
		}
		if (!empty($where)) {
			$link = isset($this->link) ? $this->link : 'default';
			$delete_result = db::query("DELETE FROM {$this->table} WHERE 1=1 AND " . db::prepare_condition($where), null, array(), $link);
			if ($delete_result['error']) {
				array_merge3($result['error'], $delete_result['error']);
			}
		}
		
		if (empty($result['error'])) {
			$result['success'] = true;
			$this->reset_cache();
		}
		return $result;
	}
	
	/**
	 * Convert input into array
	 * 
	 * @param array $data
	 * @return array
	 */
	public function process_fields($data, $ignore_not_set_fields = false) {
		$save = array();
		$fields = db::table_structures($this->table, $this->link);
		foreach ($fields as $k=>$v) {
			if ($ignore_not_set_fields && !isset($data[$k]) && !array_key_exists($k, $data)) continue;
			// processing as per different data types
			if (@$v['type'][0] == '_') {
			    $save[$k] = is_array(@$data[$k]) ? $data[$k] : array();
			} else if (in_array($v['type'], array('int2', 'int4', 'int8'))) {
				$save[$k] = format::read_intval(@$data[$k]);
			} else if ($v['type'] == 'numeric') {
				$save[$k] = format::read_floatval(@$data[$k]);
			} else if ($v['type'] == 'date') {
				$save[$k] = format::read_date(@$data[$k]);
			} else {
				if (is_null(@$data[$k]) || @$data[$k]=='') {
					$save[$k] = null;
				} else {
					$save[$k] = @$data[$k] . '';
				}
			}
		}
		return $save;
	}
	
	/**
	 * Verify fields
	 * 
	 * @param array $save
	 * @param array $columns
	 * @param array $result
	 */
	public function verify_fields(& $save, & $columns, & $result) {
		foreach ($columns as $k=>$v) {
			// running value through a function first
			if (!empty($v['function'])) {
				if (strpos($v['function'], '::')!==false) {
					$save[$k] = call_user_func($v['function'], $save[$k]);
				} else {
					$save[$k] = function2($v['function'], $save[$k]);
				}
			}
			// checking if value is empty
			if (empty($v['empty']) && empty($save[$k])) {
				$result['error'][] = $v['name'] . ' cannot be empty!';
			}
			// checking if value is too long
			if (!empty($v['maxlength'])) {
				if (strlen($save[$k]) > $v['maxlength']) {
					$result['error'][] = $v['name'] . ' is too long, max length = ' . $v['maxlength'] . '!';
				}
			}
		}
	}
	
	/**
	 * Reset cache
	 */
	public function reset_cache() {
		// resetting cache only if its enabled
		if (@$this->cache) {
			$tags = !empty($this->cache_tags) ? array_values($this->cache_tags) : array();
			$tags[] = $this->table;
			cache::gc(2, $tags);
		}
	}
}