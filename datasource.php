<?php

class datasource {

	/**
	 * Primary key, final result will be remapped using this settings
	 *
	 * @var array
	 */
	public $pk = [];

	/**
	 * Whether we have SQL query
	 *
	 * @var bool
	 */
	public $is_sql = false;

	/**
	 * Query parts would be kept here
	 *
	 * @var array
	 */
	public $parts = [];

	/**
	 * Subparts would be here, used in parts
	 *
	 * @var array
	 */
	public $subparts = [];

	/**
	 * All flags related to this datasource would be here
	 *
	 * @var array
	 */
	public $flags = [];

	/**
	 * SQL syntax related data
	 *
	 * @var array
	 */
	public $sql_syntax = [
		'operators' => ['AND', 'OR', 'NOT', 'IN', 'EXISTS', 'ANY', 'ALL']
	];

	/**
	 * Constructing datasource
	 *
	 * @param array $options
	 */
	public function __construct($options = []) {
		// todo we need to merge these three:
		//	$application[flags][datasource][this class name]
		//	$session[numbers][flags][datasource][this class name]
		//	$options
		$this->flags = $options;
	}

	/**
	 * Process value
	 *
	 * @param string $value
	 * @param bool $child
	 * @param int $level
	 * @return array
	 */
	private function process_value($value, $child, $level) {
		$result = [
			'success' => false,
			'error' => [],
			'value' => null,
			'subquery' => false,
		];
		// checking if we have datasource or subpart
		$temp = self::sql_has_datasource($value);
		if ($temp['success']) {
			foreach ($temp['data'] as $k2 => $v2) {
				if ($v2['type'] == 'subpart') {
					$subpart = $this->array_to_query($this->subparts[$v2['name']], true, $level);
					if (!$subpart['success']) {
						$result['error'] = array_merge($result['error'], $subpart['error']);
					} else {
						$result['subquery'] = true;
						$result['value'] = str_replace($k2, $subpart['sql'], $value);
					}
				} else {
					// todo: add datasource handling
				}
			}
		} else {
			$result['value'] = $value;
		}
		if (empty($result['error'])) $result['success'] = true;
		return $result;
	}

	/**
	 * Process condition for where, on and having clauses
	 *
	 * @param array $condition
	 * @param string $shift
	 * @return array
	 */
	private function process_condition($condition, $level, $shift, $no_where = false) {
		$result = [
			'success' => false,
			'error' => [],
			'where' => [],
			'sql' => null
		];

		if (!is_array($condition)) {
			$condition = [$condition];
		}

		// looping and processing data
		$subquery_mutex = [];
		$error_found = false;
		foreach ($condition as $k => $v) {
			if (!is_array($v)) {
				$temp = $this->process_value($v, true, $level);
				if (!$temp['success']) {
					$result['error'] = array_merge($result['error'], $temp['error']);
					$error_found = true;
				} else {
					$result['where'][$k] = $temp['value'];
					if ($temp['subquery']) {
						$subquery_mutex[$k] = 1;
					}
				}
			} else {
				foreach ($v as $k2 => $v2) {
					$temp = $this->process_value($v2, true, $level);
					if (!$temp['success']) {
						$result['error'] = array_merge($result['error'], $temp['error']);
						$error_found = true;
					} else {
						$result['where'][$k][$k2] = $temp['value'];
						if ($temp['subquery']) {
							$subquery_mutex[$k][$k2] = 1;
						}
					}
				}
			}
			if ($error_found) unset($result['where'][$k]);
		}

		// generating sql
		if (!empty($result['where'])) {
			$temp = [];
			foreach ($result['where'] as $k => $v) {
				if (!is_array($v)) {
					$temp_command = strtoupper($v . '');
					if (in_array($temp_command, $this->sql_syntax['operators'])) {
						if ($no_where) {
							$temp[] = $temp_command;
						} else {
							$temp[] = $shift . "\t\t" . $temp_command;
						}
					} else {
						// if we have a subquery we add extra tab
						if (!empty($subquery_mutex[$k])) {
							$v = str_replace("\n", "\n\t", $v);
						}
						if ($no_where) {
							$temp[] = '(' . $v . ')';
						} else {
							$temp[] = $shift . "\t" . '(' . $v . ')';
						}
					}
				} else {
					$temp2 = [];
					foreach ($v as $k2 => $v2) {
						$temp_command = strtoupper($v2 . '');
						if (in_array($temp_command, $this->sql_syntax['operators'])) {
							$temp2[] = $temp_command;
						} else {
							// if we have a subquery we add extra tab
							if (!empty($subquery_mutex[$k][$k2])) {
								$v2 = str_replace("\n", "\n\t", $v2);
							}
							$temp2[] = '(' . $v2 . ')';
						}
					}
					if ($no_where) {
						$temp[] = '(' . implode(' ', $temp2) . ')';
					} else {
						$temp[] = $shift . "\t(" . implode(' ', $temp2) . ')';
					}
				}
			}
			if ($no_where) {
				$result['sql'] = implode(" ", $temp);
			} else {
				$result['sql'] = implode("\n", $temp);
			}
		}
		if (empty($result['error'])) {
			$result['success'] = true;
		}
		return $result;
	}

	/**
	 * Convert array to SQL
	 *
	 * @param array $options
	 * @param bool $child
	 * @param int $level
	 * @return array
	 */
	public function array_to_query($options, $child = false, $level = 0) {
		$result = [
			'success' => false,
			'error' => [],
			'sql' => null
		];

		$level++;
		$sql = '';
		$data = [];

		// processing select
		if (isset($options['select'])) {
			$data['select'] = [];

			// processing values one by one for every field
			foreach ($options['select'] as $k => $v) {
				$temp = $this->process_value($v, true, $level);
				if (!$temp['success']) {
					$result['error'] = array_merge($result['error'], $temp['error']);
				} else {
					$data['select'][$k] = $temp['value'];
				}
			}
		} else {
			$data['select'] = ['*'];
		}

		// converting select to sql
		$shift = "";
		for ($i = 1; $i < $level; $i++) $shift.= "\t";
		$temp = [];
		foreach ($data['select'] as $k => $v) {
			$temp2 = $shift . "\t";
			if (is_numeric($k)) {
				$temp2.= $v;
			} else {
				$temp2.= $v . ' AS ' . $k;
			}
			$temp[] = $temp2;
		}
		$sql.= $shift . "SELECT\n" . implode(",\n", $temp);

		// processing from
		if (isset($options['from'])) {
			$data['from'] = [];
			foreach ($options['from'] as $k => $v) {
				$temp = $this->process_value($v, true, $level);
				if (!$temp['success']) {
					$result['error'] = array_merge($result['error'], $temp['error']);
				} else {
					$data['from'][$k] = $temp['value'];
				}
			}

			// building sql
			if (!empty($data['from'])) {
				$temp = [];
				foreach ($data['from'] as $k => $v) {
					$temp2 = $shift . "\t";
					if (is_numeric($k)) {
						$temp2.= $v;
					} else {
						$temp2.= $v . ' AS ' . $k;
					}
					$temp[] = $temp2;
				}
				$sql.= "\n" . $shift . "FROM\n" . implode(",\n", $temp);
			}
		}

		// processing joins
		if (isset($options['join'])) {
			$data['join'] = [];
			foreach ($options['join'] as $k => $v) {
				$join_table = isset($v[0]) ? $v[0] : (isset($v['table']) ? $v['table'] : '');
				if (empty($join_table)) {
					$result['error'][] = 'You must specify join table';
				} else {
					$type = isset($v['type']) ? $v['type'] : 'left';
					$on = isset($v['on']) ? $v['on'] : '';
					if (empty($on)) {
						$result['error'][] = 'You must specify on clause for join';
					} else {
						$error_found = false;
						// we are good to process join here, processing table first
						$temp = $this->process_value($join_table, true, $level);
						if (!$temp['success']) {
							$result['error'] = array_merge($result['error'], $temp['error']);
							$error_found = true;
						} else {
							$data['join'][$k]['table'] = $temp['value'];
							$data['join'][$k]['type'] = $type;
						}
						// processing on clause
						$temp = $this->process_condition($on, 0, null, 1);
						if (!$temp['success']) {
							$result['error'] = array_merge($result['error'], $temp['error']);
						} else {
							$data['join'][$k]['on'] = $temp['where'];
							$data['join'][$k]['on_sql'] = $temp['sql'];
						}
						if ($error_found) unset($data['join'][$k]);
					}
				}
			}

			// building sql
			if (!empty($data['join'])) {
				$temp = [];
				foreach ($data['join'] as $k => $v) {
					// table first
					$temp2 = $shift . strtoupper($v['type']) . ' JOIN ';
					if (is_numeric($k)) {
						$temp2.= $v['table'];
					} else {
						$temp2.= $v['table'] . ' AS ' . $k;
					}
					// on clause
					$temp2.= ' ON (' . $v['on_sql'] . ')';
					$temp[] = $temp2;
				}
				$sql.= "\n" . implode("\n", $temp);
			}
		}

		// processing where clause
		if (!empty($options['where'])) {
			$temp = $this->process_condition($options['where'], $level, $shift);
			if (!$temp['success']) {
				$result['error'] = array_merge($result['error'], $temp['error']);
			} else {
				$data['where'] = $temp['where'];
				$sql.= "\n" . $shift . "WHERE\n" . $temp['sql'];
			}
		}

		// group by clause
		if (!empty($options['groupby'])) {
			$data['groupby'] = [];
			if (!is_array($options['groupby'])) {
				$options['groupby'] = [$options['groupby']];
			}
			foreach ($options['groupby'] as $k => $v) {
				$data['groupby'][] = $v;
			}
			$sql.= "\n" . $shift . "GROUP BY " . implode(", ", $data['groupby']);
		}

		// processing having
		if (!empty($options['having'])) {
			$temp = $this->process_condition($options['having'], $level, $shift);
			if (!$temp['success']) {
				$result['error'] = array_merge($result['error'], $temp['error']);
			} else {
				$data['having'] = $temp['where'];
				$sql.= "\n" . $shift . "HAVING\n" . $temp['sql'];
			}
		}

		// processing union, intersect and except before order by
		if (isset($options['union'])) {
			foreach ($options['union'] as $k => $v) {
				$temp = $this->array_to_query($v['select'], $child, $level - 1);
				if (!$temp['success']) {
					$result['error'] = array_merge($result['error'], $temp['error']);
				} else {
					$sql.= "\n\n\n" . $shift . strtoupper($v['type']) . "\n\n\n";
					$sql.= $temp['sql'];
				}
			}
		} else {
			// order by
			if (!empty($options['orderby'])) {
				$data['orderby'] = [];
				if (!is_array($options['orderby'])) {
					$options['orderby'] = [$options['orderby']];
				}
				foreach ($options['orderby'] as $k => $v) {
					$data['orderby'][] = $v;
				}
				$sql.= "\n" . $shift . "ORDER BY " . implode(", ", $data['orderby']);
			}

			// limit
			if (isset($options['limit'])) {
				$sql.= "\n" . $shift . "LIMIT " . $options['limit'];
			}

			// offset
			if (isset($options['offset'])) {
				$sql.= "\n" . $shift . "OFFSET " . $options['offset'];
			}
		}

		// wrapping sql into braces if child
		if ($child) {
			$sql = "(\n" . $sql . "\n" . $shift . ")";
		}

		$result['sql'] = $sql;
		if (empty($result['error'])) {
			$result['success'] = true;
		}
		return $result;
	}

	/**
	 * Build SQL query
	 *
	 * @return array
	 */
	public function build_query() {
		$result = [
			'success' => false,
			'error' => [],
			'sql' => null
		];
		do {
			// merging parts together
			$main = [];
			foreach ($this->parts as $k => $v) {
				$main = array_merge_recursive($main, $v);
			}

			$result = $this->array_to_query($main, false, 0);
		} while(0);
		return $result;
	}

	/**
	 * Determine if we have datasource within a query
	 *
	 * todo: add this function to db::query()
	 *
	 * @param string $query
	 * @return array
	 */
	public static function sql_has_datasource($query) {
		return regex_datasource::parse($query);
	}
}

/*
$sql = <<<TTT
	SELECT
		a.*
	FROM [datasource[name][param_name]] AS a
	INNER JOIN [table[name]] AS e ON ...
	LEFT JOIN [model[name][param_name]] AS b ON ...
	LEFT JOIN [array[name]] AS d ON ...
	WHERE 1=1
		AND EXISTS (SELECT 1 FROM [datasource[name][param_name]] AS c)
	LIMIT 1
TTT;

$result = datasource::sql_has_datasource($sql);
echo '<pre>' . print_r($result, true) . '</pre>';
*/


/*
$result = dbs::is_datasource($json);
print_r($result);

$result = dbs::is_datasource('model::datasource_co_entities_permissions');
print_r($result);
*/

/*
$array = array(
	array('id' => 1, 'name' => 2),
	array('id' => 2, 'name' => 3),
	array('id' => 4, 'name' => 5)
);

$result = dbs::query($array, 'id');
print_r($result);

/*
SELECT * FROM (
	SELECT * FROM (VALUES (3,2),(2,3),(1,4)) AS names(id1, id2)
) a
LEFT JOIN (
	SELECT * FROM (VALUES (1,2),(2,3),(3,4)) AS names(id1, id2)
) b ON a.id1 = b.id1
ORDER BY a.id1
*/