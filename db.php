<?php

class db {

	/**
	 * Links to databases
	 * 
	 * @var array
	 */
	public static $links = array();

	/**
	 * Connect to database
	 * 
	 * @param array $parameters
	 * @param string $link
	 * @return array
	 */
	public static function connect($parameters, $link = 'default') {
		$result = array(
		    'version' => null,
		    'status' => 0,
		    'error' => array(),
		    'errno' => 0,
		    'success' => false
		);
		if (empty(self::$links[$link]['index'])) {
			// we could pass an array or connection string right a way
			if (!is_string($parameters)) {
				$str = 'host=' . $parameters['host'] . ' port=' . $parameters['port'] . ' dbname=' . $parameters['dbname'] . ' user=' . $parameters['username'] . ' password=' . $parameters['password'];
			} else {
				$str = $parameters;
			}
		    $connection = pg_connect($str);
		    if ($connection !== false) {
				self::$links[$link]['index'] = $connection;
				self::$links[$link]['parameters'] = $parameters;
				self::$links[$link]['commit_status'] = 0;
				pg_set_error_verbosity($connection, PGSQL_ERRORS_VERBOSE);
				pg_set_client_encoding($connection, 'UNICODE');
				$result['version'] = pg_version($connection);
				$result['status'] = pg_connection_status(self::$links[$link]['index']) === PGSQL_CONNECTION_OK ? 1 : 0;
				$result['success'] = true;
		    } else {
				$result['error'][] = 'db::connect() : Could not connect to ' . $str;
				$result['errno'] = 1;
		    }
		} else {
			// use an existing link
			$result['success'] = true;
		}
		return $result;
	}

	/**
	 * Get status of db connection
	 * 
	 * @param unknown_type $link
	 * @return multitype:
	 */
	public static function status($link = 'default') {
		return @self::$links[$link];
	}

	/** 
	 * Closes a connection
	 * 
	 * @param string $link
	 * @return boolean
	 */
	public static function close($link = 'default') {
		if (!empty(self::$links[$link]['index'])) {
			pg_close(self::$links[$link]['index']);
			unset(self::$links[$link]);
		}
		return true;
	}

	/**
	 * Structure of our fields (type, length and null)
	 * 
	 * @param resource $resource
	 * @return array
	 */
	public static function field_structures($resource) {
		$result = array();
		if ($resource) {
		    for ($i = 0; $i < pg_num_fields($resource); $i++) {
				$name = pg_field_name($resource, $i);
				$result[$name]['type'] = pg_field_type($resource, $i);
				$result[$name]['null'] = pg_field_is_null($resource, $i);
				$result[$name]['length'] = pg_field_size($resource, $i);
		    }
		}
		return $result;
	}

	/**
	 * Structure of the table
	 * 
	 * @param string $table
	 * @return array
	 */
	public static function table_structures($table, $link = 'default') {
		if (strpos($table, '.') === false) {
			$table = 'public.' . $table;
		}
		$table = explode('.', $table);
		$columns_result = self::query("SELECT * FROM information_schema.columns WHERE table_schema = '{$table[0]}' AND table_name = '{$table[1]}' ORDER BY ordinal_position", array('column_name'), array('cache'=>true), $link);
		// remapping data
		$map = array(
			'udt_name' => 'type',
			'data_type' => 'ftype',
			'character_maximum_length' => 'length',
			'column_default' => 'default',
			'numeric_precision' => 'precision',
			'numeric_scale' => 'scale',
			'is_nullable' => 'null'
		);
		$result = remap($columns_result['rows'], $map);
		foreach ($result as $k=>$v) {
			$result[$k]['null'] = $result[$k]['null'] == 'YES' ? true : false;
		}
		return $result;
	}

	/** 
	 * Error message
	 * 
	 * @param unknown_type $link
	 * @return string
	 */
	public static function error($link = 'default') {
		return pg_last_error(self::$links[$link]['index']);
	}

	/**
	 * Error code
	 *  
	 * @param string $link
	 * @return mixed
	 */
	public static function errno($link = 'default') {
		$error = pg_last_error(self::$links[$link]['index']);
		if (!empty($error)) {
		    preg_match("|ERROR:\s(.*?):|i", $error, $matches);
		    return @$matches[1] ? $matches[1] : 1;
		} else {
		    return 0;
		}
	}

	/**
	 * This will return structured data
	 * 
	 * @param string $sql
	 * @param mixed $key
	 * @param array $options
	 * @param string $link
	 * @return array
	 */
	public static function query($sql, $key = null, $options = array(), $link = 'default') {
		$result = array(
			'sql' => & $sql,
			'error' => array(),
		    'errno' => 0,
		    'num_rows' => 0,
		    'affected_rows' => 0,
		    'rows' => array(),
		    'key' => & $key,
		    'structure' => array(),
		);

		// cache id
		$cache_id = !empty($options['cache_id']) ? $options['cache_id'] : 'db_query_' . md5($sql);

		// if we cache this query
		if (!empty($options['cache'])) {
		    $cached_result = cache::get($cache_id, @$options['cache_link']);
		    if ($cached_result !== false) {
				return $cached_result;
		    }
		}

		// quering
		$resource = @pg_query(self::$links[$link]['index'], $sql);
		$result['status'] = pg_result_status($resource);
		if (!$resource || $result['status'] > 4) {
		    $result['error'][] = 'Link ' . $link . ': ' . self::error($link);
		    $result['errno'] = self::errno($link);
		    if (empty($result['error'])) {
				$result['error'][] = 'Link ' . $link . ': ' . 'Unspecified error!';
		    }
		    // we log this error message
			error_log('Query error: ' . implode(' ', $result['error']) . ' [' . $sql . ']');
		} else {
		    $result['affected_rows'] = pg_affected_rows($resource);
		    $result['num_rows'] = pg_num_rows($resource);
		    $result['structure'] = self::field_structures($resource);
		    if ($result['num_rows'] > 0) {
				while ($rows = pg_fetch_assoc($resource)) {
					// transforming pg arrays to php arrays and casting types
				    foreach ($rows as $k => $v) {
						if (@$result['structure'][$k]['type'][0] == '_') {
						    $rows[$k] = self::pg_parse_array($v);
						} else if (in_array(@$result['structure'][$k]['type'], array('int2', 'int4', 'int8'))) {
						    $rows[$k] = (int) $v;
						} else if (@$result['structure'][$k]['type'] == 'numeric') {
						    $rows[$k] = (float) $v;
						}
				    }

				    // assigning keys
				    if (!empty($key)) {
						array_key_set_by_key_name($result['rows'], $key, $rows);
				    } else {
						$result['rows'][] = $rows;
				    }
				}
		    }
		    pg_free_result($resource);
		}

		// caching if no error
		if (!empty($options['cache']) && empty($result['error'])) {
		    cache::set($cache_id, $result, null, array('tags' => @$options['cache_tags']), @$options['cache_link']);
		}
		return $result;
	}

	/**
	 * Begin transaction
	 * 
	 * @param string $link
	 * @return array
	 */
	public static function begin($link = 'default') {
		if (!isset(self::$links[$link]['commit_status'])) self::$links[$link]['commit_status'] = 0;
		if (self::$links[$link]['commit_status']==0) {
			//echo " BEGIN X ";
			//debug_print_backtrace();
			self::$links[$link]['commit_status']++;
			return self::query('BEGIN', null, array(), $link);
		}
		self::$links[$link]['commit_status']++;
	}

	/**
	 * Commit transaction
	 * 
	 * @param string $link
	 * @return array
	 */
	public static function commit($link = 'default') {
		if (self::$links[$link]['commit_status']==1) {
			//echo " COMMIT X ";
			//debug_print_backtrace();
			self::$links[$link]['commit_status'] = 0;
			return self::query('COMMIT', null, array(), $link);
		}
		self::$links[$link]['commit_status']--;
	}

	/**
	 * Roll back to savepoint or entire transaction
	 * 
	 * @param string $savepoint_name
	 * @param string $link
	 * @return array 
	 */
	public static function rollback($savepoint_name = '', $link = 'default') {
		self::$links[$link]['commit_status'] = 0;
		//echo " ROLLBACK ";
		//debug_print_backtrace();
		return self::query('ROLLBACK' . ($savepoint_name ? (' TO SAVEPOINT ' . $savepoint_name) : ''), null, array(), $link);
	}

	/**
	 * Save point within transaction
	 * 
	 * @param string $savepoint_name
	 * @param string $link
	 * @return array
	 */
	public static function savepoint($savepoint_name, $link = 'default') {
		return self::query('SAVEPOINT ' . $savepoint_name, null, array(), $link);
	}

	/**
	 * Release saved point within transaction
	 * 
	 * @param string $savepoint_name
	 * @param string $link
	 * @return array
	 */
	public static function release_savepoint($savepoint_name, $link = 'default') {
		return self::query('RELEASE SAVEPOINT ' . $savepoint_name, null, array(), $link);
	}

	/**
	 * Prepare values for insert query
	 * 
	 * @param mixed $parameters
	 * @return string
	 */
	static public function prepare_values($parameters, $link = 'default') {
		$result = array();
		foreach ($parameters as $k => $v) {
		    $temp = explode(',', $k);
		    $key = $temp[0];
		    $operator = @$temp[1] ? $temp[1] : '=';
		    if (is_string($v)) {
				$result[] = "'" . self::escape($v, $link) . "'";
		    } else if (@$temp[2] == '~~' || is_numeric($v)) {
				$result[] = $v;
		    } else if (is_array($v)) {
				$result[] = "'" . self::prepare_array($v, $link) . "'";
		    } else if (is_null($v)) {
				$result[] = 'NULL'; // problem is here
		    } else {
		    	// todo fix here!!!
		    	die('Unknown data type');
		    }
		}
		return implode(', ', $result);
	}

	/**
	 * Prepare expression for insert query
	 * 
	 * @param mixed $parameters
	 * @param mixed $delimiter
	 * @return string
	 */
	public static function prepare_expression($parameters, $delimiter = ', ') {
		if (is_array($parameters)) {
		    $temp = array();
		    foreach ($parameters as $v) {
				$par = explode(',', $v);
				$temp[] = $par[0];
		    }
		    $parameters = implode($delimiter, $temp);
		}
		return $parameters;
	}

	/**
	 * Accepts an array of values and then returns delimited and comma separated list of
	 * value for use in an sql statement. 
	 * 
	 * @param array $parameters
	 * @return string 
	 */
	public static function prepare_array($arr, $link = 'default') {
		$result = array();
		if (empty($arr)) $arr = array();
		foreach ($arr as $v) {
		    if (is_array($v)) {
				$result[] = self::prepare_array($v, $link);
		    } else {
				$str = self::escape($v, $link);
				$str = str_replace('"', '\\\"', $str);
				if (strpos($str, ',') !== false || strpos($str, ' ') !== false || strpos($str, '{') !== false || strpos($str, '}') !== false || strpos($str, '"') !== false) {
				    $result[] = '"' . $str . '"';
				} else {
				    $result[] = $str;
				}
		    }
		}
		return '{' . implode(',', $result) . '}';
	}

	/**
	 * Escape takes a value and escapes the value for the database in a generic way
	 * 
	 * @param type $value
	 * @return string 
	 */
	public static function escape($value, $link = 'default') {
		return pg_escape_string(self::$links[$link]['index'], $value);
	}

	/**
	 * Escape array
	 * 
	 * @param array $value
	 * @param string $link
	 * @return array
	 */
	public static function escape_array($value, $link = 'default') {
		$result = array();
		foreach ($value as $k=>$v) $result[$k] = db::escape($v, $link);
		return $result;
	}

	/**
	 * Convert an array into sql string  
	 *  
	 * @param  array $parameters
	 * @param  string $delimiter
	 * @return string 
	 */
	public static function prepare_condition($parameters, $delimiter = 'AND', $link = 'default') {
		$result = '';
		if (is_array($parameters)) {
		    $temp = array();
		    $string = '';
		    foreach ($parameters as $k => $v) {
				$par = explode(',', $k);
				$key = $par[0];
				$operator = @$par[1] ? $par[1] : '=';
				$as_is = (@$par[2] == '~~') ? true : false;
				$string = $key;
				switch ($operator) {
				    case 'LIKE%':
				    case 'ILIKE%':
						$v = '%' . $v . '%';
				    case 'LIKE':
				    case 'ILIKE':
						$string.= ' ILIKE ';
						break;
				    default:
						$string.= ' ' . $operator . ' ';
				}

				// value
				if ($as_is) {
					// no changes
				} else if (is_string($v)) {
					$v = "'" . self::escape($v, $link) . "'";
				} else if (is_numeric($v)) {
					// no changes
				} else if (is_array($v)) {
					$v = "'" . self::prepare_array($v, $link) . "'";
			    } else if (is_null($v)) {
					$v = 'NULL';
			    } else {
					// todo fix here
					die('Unknown data type');
				}
				$string .= $v;
				// special for array operators: ANY, ALL
				if ($operator == 'ANY' || $operator == 'ALL') {
				    $string = $v . ' = ' . $operator . '(' . $key . ')';
				}
				$temp[] = $string;
		    }
		    $delimiter = ' ' . $delimiter . ' ';
		    $result = implode($delimiter, $temp);
		} else if (!empty($parameters)) {
		    $result = $parameters;
		}
		return $result;
	}

	/**
	 * Parsing pg array string into array
	 * 
	 * @param string $arraystring
	 * @param boolean $reset
	 * @return array
	 */
	public static function pg_parse_array($arraystring, $reset = true) {
		static $i = 0;
		if ($reset) $i = 0;
		$matches = array();
		$indexer = 0; // by default sql arrays start at 1
		// handle [0,2]= cases
		if (preg_match('/^\[(?P<index_start>\d+):(?P<index_end>\d+)]=/', substr($arraystring, $i), $matches)) {
		    $indexer = (int) $matches['index_start'];
		    $i = strpos($arraystring, '{');
		}
		if ($arraystring[$i] != '{') {
		    return array();
		}
		$i++;
		$work = array();
		$curr = '';
		$length = strlen($arraystring);
		$count = 0;
		while ($i < $length) {
		    switch ($arraystring[$i]) {
				case '{':
				    $sub = self::pg_parse_array($arraystring, false);
				    if (!empty($sub)) {
						$work[$indexer++] = $sub;
				    }
				    break;
				case '}':
				    $i++;
				    //if ($curr<>'') 
					$work[$indexer++] = $curr;
				    return $work;
				    break;
				case '\\':
				    $i++;
				    $curr.= $arraystring[$i];
				    $i++;
				    break;
				case '"':
				    $openq = $i;
				    do {
						$closeq = strpos($arraystring, '"', $i + 1);
						if ($closeq > $openq && $arraystring[$closeq - 1] == '\\') {
						    $i = $closeq + 1;
						} else {
						    break;
						}
				    } while (true);
				    if ($closeq <= $openq) {
						die;
				    }
				    $curr.= substr($arraystring, $openq + 1, $closeq - ($openq + 1));
				    $i = $closeq + 1;
				    break;
				case ',':
				    //if ($curr<>'') 
					$work[$indexer++] = $curr;
				    $curr = '';
				    $i++;
				    break;
				default:
				    $curr.= $arraystring[$i];
				    $i++;
		    }
		}
	}

	/**
	 * Escape in tsquery
	 * @param string $str
	 * @param string $operator
	 * @return string
	 */
	static public function escapets($str, $operator = '&', $link = 'default') {
		$str = preg_replace('/\s\s+/', ' ', trim($str));
		if (empty($str)) {
		    return "";
		} else {
		    return str_replace(' ', ":*$operator", self::escape($str, $link)) . ":*";
		}
	}

	/**
	 * Preparing sql for full text search
	 * 
	 * @param mixed $fields
	 * @param string $str
	 * @param string $operator [&,|]
	 * @param boolean $desc how to sort results
	 * @return array
	 */
	static public function tsquery($fields, $str, $operator = '&', $desc = true, $subquery = array(), $link = 'default') {
		$result = array(
		    'where' => '',
		    'orderby' => ''
		);
		$str_escaped = self::escape(trim($str), $link);
		$flag_do_not_escape = false;
		if (!empty($fields)) {
		    $sql = '';
		    $sql2 = '';
		    if (is_array($fields)) {
				$sql = "concat_ws(' ', " . implode(", ", $fields) . ")";
				$temp = array();
				foreach ($fields as $f) {
				    $temp[] = "$f::text ILIKE '%" . $str_escaped . "'";
				}
				$sql2 = " OR (" . implode(" OR ", $temp) . ")";
		    } else {
		    	if (strpos($fields, '::tsvector')!==false) $flag_do_not_escape = true;
				$sql = $fields;
				$sql2 = " OR $fields::text ILIKE '%" . $str_escaped . "'";
			}
		    if ($subquery) {
				if (isset($subquery['table'])) $subquery = array($subquery);
				$temp = array();
				foreach ($subquery as $k => $v) {
				    $t1 = self::tsquery($v['columns'], $str, $operator, $desc, @$v['subquery']);
				    $temp[] = "EXISTS (SELECT 1 FROM {$v['table']} WHERE 1=1 {$v['join']} {$t1['where']} ORDER BY {$t1['orderby']})";
				}
				$sql2.= ' OR ' . implode(' OR ', $temp);
		    }
		    $escaped = self::escapets($str, $operator, $link);
		    if ($escaped) {
				if ($flag_do_not_escape) {
					$result['where'] = " AND ($sql @@ to_tsquery('simple', '" . $escaped . "') $sql2)";
					$result['orderby'] = " (ts_rank_cd($sql, to_tsquery('simple', '" . $escaped . "')))" . ($desc ? " DESC" : "ASC");
					$result['rank'] = "(ts_rank_cd($sql, to_tsquery('simple', '" . $escaped . "')))";
				} else {
					$result['where'] = " AND (to_tsvector('simple', $sql) @@ to_tsquery('simple', '" . $escaped . "') $sql2)";
					$result['orderby'] = " (ts_rank_cd(to_tsvector($sql), to_tsquery('simple', '" . $escaped . "')))" . ($desc ? " DESC" : "ASC");
					$result['rank'] = "(ts_rank_cd(to_tsvector($sql), to_tsquery('simple', '" . $escaped . "')))";
				}
		    }
		}
		return $result;
	}

	/**
	 * Copy data directly into db, rows are key=>value pairs
	 * 
	 * @param string $table
	 * @param array $rows
	 * @param array $headers
	 * @param string $link
	 * @return array
	 */
	public static function copy($table, $rows, $headers, $link = 'default') {
		$result = array(
		    'error' => array(),
		    'success' => false
		);
		$replaces = array(
		    'from' => array("\t", "\n", "\r", "\\"),
		    'to' => array(' ', '\\\\n', '', "\\\\")
		);
		do {
		    // todo: we might need to have transaction here
		    pg_query(self::$links[$link]['index'], "COPY $table (" . implode(", ", $headers) . ") FROM STDIN");
		    foreach ($rows as $k => $v) {
				foreach ($v as $k2 => $v2) {
				    if (is_array($v2)) {
						foreach ($v2 as $k3 => $v3) {
						    $rows[$k][$k2][$k3] = str_replace($replaces['from'], $replaces['to'], $rows[$k][$k2][$k3]);
						}
						$rows[$k][$k2] = self::prepare_array($rows[$k][$k2], $link);
				    } else if (is_null($v2)) {
						$rows[$k][$k2] = '\N';
				    } else {
						$rows[$k][$k2] = str_replace($replaces['from'], $replaces['to'], $rows[$k][$k2]);
				    }
				}
				$row = implode("\t", $rows[$k]) . "\n";
				pg_put_line(self::$links[$link]['index'], $row);
		    }
		    // end line
		    pg_put_line(self::$links[$link]['index'], "\\.\n");
		    // end copying
		    if (!@pg_end_copy(self::$links[$link]['index'])) {
				$result['error'][] = pg_last_error(self::$links[$link]['index']);
		    } else {
				$result['success'] = true;
		    }
		} while (0);
		return $result;
	}

	/**
	 * Insert multiple rows to database
	 * 
	 * @param string $table
	 * @param array $rows
	 * @param array $headers
	 * @param string $link
	 * @return array
	 */
	public static function insert($table, $rows, $headers, $link = 'default') {
		$result = array(
			'error' => array(),
			'success' => false
		);
		do {
			$sql = "INSERT INTO $table (" . db::prepare_expression($headers) . ") VALUES ";
			$sql_values = array();
			foreach ($rows as $k=>$v) {
				$sql_values[]= "(" . db::prepare_values($v, $link) . ")";
			}
			$sql.= implode(', ', $sql_values);
			$query_result = self::query($sql, null, array(), $link);
			if ($query_result['error']) {
				array_merge3($result['error'], $query_result['error']);
			} else {
				$result['success'] = true;
			}
		} while (0);
		return $result;
	}

	/**
	 * Save row to database
	 * 
	 * @param string $table
	 * @param array $data
	 * @param mixed $keys
	 * @param string $link
	 * @return boolean
	 */
	public static function save($table, $data, $keys, $link = 'default') {
		$result = array(
			'success' => false,
			'error' => array(),
			'data' => array(),
			'inserted' => false
		);

		do {

	    	// converting string to an array
			if (!is_array($keys)) {
			    $keys = array($keys);
			}

			// where clause
			$where = array();
			$empty = true;
			foreach ($keys as $key) {
				if (!empty($data[$key])) {
					$empty = false;
				}
				$where[$key] = @$data[$key];
			}

			// if keys are empty we must insert
			$row_found = false;
			if (!$empty) {
				$select_result = self::query("SELECT * FROM $table WHERE " . self::prepare_condition($where, 'AND', $link), '', array(), $link);
				if ($select_result['error']) {
					$result['error'] = $select_result['error'];
					break;
				} else if ($select_result['num_rows']) {
					$row_found = true;
				}
			}

			// if row found we update
			if ($row_found) {
			    $flag_inserted = false;
			    $sql = "UPDATE $table SET " . db::prepare_condition($data, ', ', $link) . ' WHERE ' . self::prepare_condition($where, 'AND', $link) . ' RETURNING *';
			} else {
			    $flag_inserted = true;
			    // we need to unset key fields
			    if ($empty) foreach ($keys as $key) unset($data[$key]);
			    // we insert
			    $sql = "INSERT INTO $table (" . db::prepare_expression(array_keys($data)) . ") VALUES (" . db::prepare_values($data, $link) . ") RETURNING *";
			}
			$result_sql = self::query($sql, '', array(), $link);
			if ($result_sql['error']) {
				$result['error'] = $result_sql['error'];
				break;
			}
			$result['data'] = $result_sql['rows'][0];
			$result['inserted'] = $flag_inserted;
			$result['success'] = true;
		} while(0);
		return $result;
	}

	/**
	 * Sequences next values, $num_rows = 0 will return a single sequence value
	 * 
	 * @param string $sequnce_name
	 * @param int $num_rows
	 * @return mixed
	 */
	public static function sequence($sequence_name, $num_rows = 0, $link = 'default') {
		$num_new = $num_rows;
		if ($num_new <= 1) $num_new = 1;
		$result = db::query("SELECT nextval('$sequence_name') AS seq_id FROM generate_series(1, $num_new)", "seq_id", array(), $link);
		if (empty($result['rows'])) {
		    return false;
		} else {
		    if ($num_rows == 0) {
				$temp = array_shift($result['rows']);
				return $temp['seq_id'];
		    } else {
				$keys = array_keys($result['rows']);
				return array_combine($keys, $keys);
		    }
		}
	}
}