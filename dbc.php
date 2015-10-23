<?php

/**
 * Support
 *      Schema [new, delete, owner change]
 *      Table [new, delete, owner change]
 *      View [new, delete, owner change, change]
 *      Column [new, delete, change]
 *      Index [new, delete, change]
 *      Unique Constraint [new, delete, change]
 *      Primary Key Constraint [new, delete, change]
 *      Check Constraint [new, delete, change]
 *      Domains [new, delete, owner change, change]
 *      Sequences [new, delete, owner change]
 *      Functions [new, delete, owner change, change]
 *      Trigger [new, delete,  change]
 * 
 * Limitations:
 *      No exclusion constraints
 *      No rules
 *      No comments syncronization support
 *      Only tables with atleast one column is compared
 *      Array data types only allowed one dimension i.e. text[]
 */
class dbc {

	/**
	 * Get data 
	 * 
	 * @param string $type
	 * @param string $link
	 * @param array $options
	 * @return array
	 * @throws Exception
	 */
	public static function get($type, $link, $options = array()) {
		$result = array(
			'success' => false,
			'error' => array(),
			'data' => array(),
			'hint' => array()
		);

		// getting proper query
		switch($type) {
			case 'schemas':
				$key = array('schema_name');
				$sql = <<<TTT
					SELECT 
							schema_name,
							schema_owner
					FROM information_schema.schemata
					WHERE schema_name !~ 'pg_' AND schema_name != 'information_schema'
TTT;
				break;
			case 'tables':
				$key = array('schema_name', 'table_name');
				$sql = <<<TTT
					SELECT
							schemaname schema_name,
							tablename table_name,
							tableowner table_owner
					FROM pg_tables a
					WHERE 1=1
							AND schemaname NOT IN ('pg_catalog', 'information_schema')
					ORDER BY schema_name, table_name
TTT;
				break;
			case 'constraints':
				$key = array('constraint_type', 'schema_name', 'table_name', 'constraint_name');
				$sql = <<<TTT
					SELECT
							*
					FROM (
							-- indexes
							SELECT
									'INDEX' constraint_type,
									n.nspname schema_name,
									t.relname table_name,
									i.relname constraint_name,
									max(f.amname) index_type,
									array_agg(a.attname) column_names,
									'' foreign_schema_name,
									'' foreign_table_name,
									'{}'::text[] foreign_column_names,
									null match_option,
									null update_rule,
									null delete_rule
							FROM pg_class t, pg_class i, pg_index ix, pg_attribute a, pg_namespace n, pg_am f
							WHERE 1=1
								AND t.oid = ix.indrelid
								and i.oid = ix.indexrelid
								and a.attrelid = t.oid
								and a.attnum = ANY(ix.indkey)
								and t.relkind = 'r'
								AND n.oid = t.relnamespace
								AND n.nspname NOT IN ('pg_catalog', 'information_schema')
								AND ix.indisprimary != 't'
								AND ix.indisunique != 't'
								AND f.oid = i.relam
							GROUP BY n.nspname, t.relname, i.relname

							UNION ALL

							-- unique and primary key
							SELECT
									min(tc.constraint_type) constraint_type,
									tc.table_schema schema_name,
									tc.table_name table_name,
									tc.constraint_name constraint_name,
									null index_type,
									array_agg(kc.column_name::text) column_names,
									'' foreign_schema_name,
									'' foreign_table_name,
									'{}'::text[] foreign_column_names,
									null match_option,
									null update_rule,
									null delete_rule
							FROM information_schema.table_constraints tc, information_schema.key_column_usage kc  
							WHERE 1=1
									and kc.table_name = tc.table_name 
									and kc.table_schema = tc.table_schema
									and kc.constraint_name = tc.constraint_name
									AND tc.constraint_type IN ('PRIMARY KEY', 'UNIQUE')
							GROUP BY tc.table_schema, tc.table_name, tc.constraint_name

							UNION ALL

							-- foreign key
							SELECT
									'FOREIGN_KEY' constraint_type,
									x.table_schema schema_name,
									x.table_name table_name,
									c.constraint_name constraint_name,
									null index_type,
									array_agg(x.column_name::text) column_names,
									y.table_schema foreign_schema_name,
									y.table_name foreign_table_name,
									array_agg(y.column_name::text) foreign_column_name,
									min(match_option::text) match_option,
									min(update_rule::text) update_rule,
									min(delete_rule::text) delete_rule
							FROM information_schema.referential_constraints c
							JOIN information_schema.key_column_usage x ON x.constraint_name = c.constraint_name
							JOIN information_schema.key_column_usage y ON y.ordinal_position = x.position_in_unique_constraint and y.constraint_name = c.unique_constraint_name
							GROUP BY x.table_schema, x.table_name, c.constraint_name, y.table_schema, y.table_name

							UNION ALL

							SELECT
								'CHECK' constraint_type,
								n.nspname schema_name,
								r.relname table_name,
								c.conname constraint_name,
								'' index_type,
								'{}'::text[] column_names,
								'' foreign_schema_name,
								'' foreign_table_name,
								'{}'::text[] foreign_column_names,
								c.consrc match_option,
								null update_rule,
								null delete_rule
							FROM pg_class r, pg_constraint c, pg_namespace n, pg_class i
							WHERE r.oid = c.conrelid
								AND c.contype = 'c'
								AND n.oid = r.relnamespace
					) a
TTT;
				break;
			 case 'columns':
				$key = array('schema_name', 'table_name', 'column_name');
				$sql = <<<TTT
					SELECT 
							b.table_schema schema_name,
							b.table_name table_name,
							c.tableowner table_owner,
							a.column_name column_name,
							a.data_type data_type,
							CASE when a.is_nullable='NO' THEN 'NOT NULL' ELSE '' END is_nullable,
							a.column_default column_default,
							a.character_maximum_length character_maximum_length,
							a.numeric_precision numeric_precision,
							a.numeric_scale numeric_scale,
							a.udt_name data_type_udt
					FROM information_schema.columns a
					LEFT JOIN information_schema.tables b ON a.table_schema = b.table_schema AND a.table_name = b.table_name
					LEFT JOIN pg_tables c ON a.table_schema = c.schemaname AND a.table_name = c.tablename
					WHERE 1=1
							AND b.table_schema NOT IN ('pg_catalog', 'information_schema')
							AND b.table_type = 'BASE TABLE'
					ORDER BY b.table_schema, b.table_name, a.ordinal_position
TTT;
				break;
			case 'views':
				$key = array('schema_name', 'view_name');
				$sql = <<<TTT
					SELECT
							schemaname schema_name,
							viewname view_name,
							viewowner view_owner,
							definition view_definition
					FROM pg_views 
					WHERE 1=1
							AND schemaname NOT IN('information_schema', 'pg_catalog')
TTT;
				break;
			case 'domains':
				$key = array('schema_name', 'domain_name');
				$sql = <<<TTT
					SELECT 
							a.domain_schema schema_name,
							a.domain_name domain_name,
							a.data_type data_type,
							CASE WHEN b.typnotnull = 't' THEN 'NOT NULL' ELSE '' END is_nullable,
							a.domain_default domain_default,
							a.character_maximum_length character_maximum_length,
							a.numeric_precision numeric_precision,
							a.numeric_scale numeric_scale,
							a.udt_name data_type_udt,
							c.constraint_name constraint_name,
							c.constraint_definition constraint_definition,
							b.rolname domain_owner
					FROM information_schema.domains a
					LEFT JOIN (
							SELECT 
									n.nspname schema_name,
									pg_catalog.format_type(t.oid, NULL) type_name,
									t.typnotnull,
									x.rolname,
									t.typowner
							FROM pg_catalog.pg_type t
							LEFT JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace
							LEFT JOIN pg_catalog.pg_authid x ON x.oid = t.typowner
					) b ON a.domain_schema = b.schema_name AND b.type_name = (case when b.schema_name='public' then a.domain_name ELSE b.schema_name || '.' || a.domain_name END)
					LEFT JOIN (
							SELECT 
									s.nspname as schema_name, 
									pg_type.typname as domain_name,
									array_agg(c.conname) constraint_name,
									array_agg(pg_get_constraintdef(c.oid)) AS constraint_definition
							FROM (SELECT oid,* FROM pg_constraint WHERE contypid>0) as c
							LEFT JOIN pg_type ON pg_type.oid = c.contypid
							JOIN pg_namespace s ON s.oid = c.connamespace
							WHERE s.nspname NOT IN ('information_schema', 'pg_catalog')
							GROUP BY s.nspname, pg_type.typname
					) c ON a.domain_schema = c.schema_name AND c.domain_name = a.domain_name
					WHERE domain_schema NOT IN ('information_schema', 'pg_catalog')
TTT;
				break;
			case 'sequences':
				$key = array('schema_name', 'sequence_name');
				$sql = <<<TTT
					SELECT 
							s.nspname schema_name, 
							c.relname sequence_name, 
							c.rolname sequence_owner,
							(pg_sequence_parameters(c.oid))."increment" sequence_increment, 
							(pg_sequence_parameters(c.oid)).minimum_value sequence_minvalue, 
							(pg_sequence_parameters(c.oid)).maximum_value sequence_maxvalue, 
							(pg_sequence_parameters(c.oid)).start_value sequence_start, 
							CASE WHEN (pg_sequence_parameters(c.oid)).cycle_option THEN 1 ELSE 0 END sequence_is_cycle, 
							d.description AS description
					FROM (
							SELECT 
									a.oid,
									a.relnamespace, 
									a.relname,
									x.rolname
							FROM pg_class a
							LEFT JOIN pg_catalog.pg_authid x ON x.oid = a.relowner
							WHERE a.relkind = 'S'
					) c
					LEFT JOIN pg_namespace s ON s.oid = c.relnamespace
					LEFT JOIN pg_description d ON d.objoid = c.oid AND d.classoid = 'pg_class'::regclass::oid
TTT;
				break;
			case 'functions':
				$key = array('schema_name', 'function_name');
				$sql = <<<TTT
					SELECT
							n.nspname schema_name,
							p.proname || '(' || pg_get_function_identity_arguments(p.oid) || ')' function_name,
							o.rolname function_owner,
							pg_catalog.pg_get_functiondef(p.oid) function_definition
					FROM pg_catalog.pg_proc p
					LEFT JOIN pg_catalog.pg_namespace n ON p.pronamespace = n.oid
					LEFT JOIN pg_authid o ON o.oid = p.proowner
					WHERE 1=1
							AND n.nspname NOT IN ('pg_catalog', 'information_schema')
							AND p.proisagg = 'f'
TTT;
				break;
			case 'triggers':
				$key = array('schema_name', 'table_name', 'trigger_name');
				$sql = <<<TTT
					SELECT
							n.nspname schema_name,
							b.relname table_name,
							a.tgname trigger_name,
							pg_get_triggerdef(a.oid) trigger_definition
					FROM pg_trigger a
					LEFT JOIN pg_class b ON a.tgrelid = b.oid
					LEFT JOIN pg_namespace n ON n.oid = b.relnamespace
					WHERE 1=1
							AND tgisinternal = 'f'
TTT;
				break;
			default:
				Throw new Exception('type?');
		}
		// options
		if (!empty($options['where'])) {
			$sql = "SELECT * FROM (" . $sql . ") a99 WHERE 1=1 AND " . db::prepare_condition($options['where'], 'AND', $link);
		}
		$result2 = db::query($sql, $key, array(), $link);
		if ($result2['error']) {
			$result['error'] = array_merge($result['error'], $result2['error']);
		} else {
			$result['data'] = $result2['rows'];
			$result['success'] = true;
		}
		return $result;
	}

	/**
	 * Compare schemas
	 * 
	 * @param string $master_link
	 * @param string $slave_link
	 * @param array $options
	 * @return array
	 */
	public static function compare_schema($master_link, $slave_link, $template, $options = array()) {
		$result = array(
			'success' => true,
			'error' => array(),
			'data' => array()
		);

		do {
			// getting information
			$data = array();
			foreach (array('schemas', 'columns', 'constraints', 'views', 'domains', 'sequences', 'functions', 'triggers') as $v) {
				// master database first
				$temp = self::get($v, $master_link);
				foreach ($temp['data'] as $k2=>$v2) if (!isset($template[$k2])) unset($temp['data'][$k2]);
				if (!$temp['success']) {
					$result['error'] = array_merge($result['error'], $temp['error']);
				} else {
					$data['master'][$v] = $temp['data'];
				}
				// slave database second
				$temp = self::get($v, $slave_link);
				foreach ($temp['data'] as $k2=>$v2) if (!isset($template[$k2])) unset($temp['data'][$k2]);
				if (!$temp['success']) {
					$result['error'] = array_merge($result['error'], $temp['error']);
				} else {
					$data['slave'][$v] = $temp['data'];
				}
			}

			// initializing tables in specific order
			$result['data'] = array();
			// delete first
			$result['data']['delete_triggers'] = array();
			$result['data']['delete_views'] = array();
			$result['data']['delete_constraints'] = array();
			$result['data']['delete_functions'] = array();
			$result['data']['delete_columns'] = array();
			$result['data']['delete_tables'] = array();
			$result['data']['delete_domains'] = array(); // after tables and columns
			$result['data']['delete_sequences'] = array(); // after domains
			$result['data']['delete_schemas'] = array(); // last

			// new second
			$result['data']['new_schemas'] = array(); // first
			$result['data']['new_schema_owners'] = array();
			$result['data']['new_domains'] = array(); // after schema
			$result['data']['new_domain_owners'] = array();
			$result['data']['new_sequences'] = array();
			$result['data']['new_sequence_owners'] = array();
			$result['data']['new_tables'] = array();
			$result['data']['new_table_owners'] = array();
			$result['data']['new_columns'] = array();
			$result['data']['change_columns'] = array();
			$result['data']['new_constraints'] = array();
			$result['data']['new_views'] = array(); // views goes after we add columns
			$result['data']['change_views'] = array();
			$result['data']['new_view_owners'] = array();
			$result['data']['new_functions'] = array();
			$result['data']['new_function_owner'] = array();
			$result['data']['change_functions'] = array();
			$result['data']['new_triggers'] = array(); // after functions
			$result['data']['change_triggers'] = array();

			// new schemas
			foreach ($data['master']['schemas'] as $k=>$v) {
				if (empty($data['slave']['schemas'][$k])) {
					$result['data']['new_schemas'][$k] = array('type'=>'schema', 'name'=>$k, 'owner'=>$v['schema_owner']);
				} else if ($v['schema_owner']!=$data['slave']['schemas'][$k]['schema_owner']) {
					$result['data']['new_schema_owners'][$k] = array('type'=>'schema_owner', 'name'=>$k, 'owner'=>$v['schema_owner']);
				}
			}

			// delete schema
			foreach ($data['slave']['schemas'] as $k=>$v) {
				if (empty($data['master']['schemas'][$k])) {
					$result['data']['delete_schemas'][$k] = array('type'=>'schema_delete', 'name'=>$k, 'owner'=>$v['schema_owner']);
				}
			}

			// new tables
			foreach ($data['master']['columns'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					$owner_master = current($v2);
					if (empty($data['slave']['columns'][$k][$k2])) {
						$result['data']['new_tables'][$k . '.' . $k2] = array('type'=>'table_new', 'name'=>$k . '.' . $k2, 'owner'=>$owner_master['table_owner'], 'columns'=>$v2);
					} else {
						$owner_slave = current($data['slave']['columns'][$k][$k2]);
						if ($owner_master['table_owner']!=$owner_slave['table_owner']) {
							$result['data']['new_table_owners'][$k . '.' . $k2] = array('type'=>'table_owner', 'name'=>$k . '.' . $k2, 'owner'=>$owner_master['table_owner']);
						}
					}
				}
			}

			// delete table
			foreach ($data['slave']['columns'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					if (empty($data['master']['columns'][$k][$k2])) {
						$result['data']['delete_tables'][$k . '.' . $k2] = array('type'=>'table_delete', 'name'=>$k . '.' . $k2);
					}
				}
			}

			// new columns
			foreach ($data['master']['columns'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					// if we have new table we do not need to check for new columns
					if (!empty($result['data']['new_tables'][$k . '.' . $k2])) continue;
					// finding new column
					foreach ($v2 as $k3=>$v3) {
						if (empty($data['slave']['columns'][$k][$k2][$k3])) {
							$result['data']['new_columns'][$k . '.' . $k2 . '.' . $k3] = array('type'=>'column_new', 'name'=>$k3, 'table' => $k . '.' . $k2, 'column'=>$v3);
						} else {
							// comparing data types
							$master = $v3;
							$slave = $data['slave']['columns'][$k][$k2][$k3];
							unset($master['table_owner'], $slave['table_owner']);
							if (md5(serialize($master))!=md5(serialize($slave))) {
								$result['hint'][] = "Field $k.$k2.$k3 has different structure!";
								$result['data']['change_columns'][$k . '.' . $k2 . '.' . $k3] = array('type'=>'column_change', 'name'=>$k3, 'table' => $k . '.' . $k2, 'column'=>$master, 'existing_column'=>$slave);
							}
						}
					}

					// finding columns to be deleted
					foreach ($data['slave']['columns'][$k][$k2] as $k3=>$v3) {
						if (empty($data['master']['columns'][$k][$k2][$k3])) {
							$result['data']['delete_columns'][$k . '.' . $k2 . '.' . $k3] = array('type'=>'column_delete', 'name'=>$k3, 'table' => $k . '.' . $k2);
						}
					}
				}
			}

			// new constraints
			foreach ($data['master']['constraints'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					foreach ($v2 as $k3=>$v3) {
						foreach ($v3 as $k4=>$v4) {
							if (empty($data['slave']['constraints'][$k][$k2][$k3][$k4])) {
								$result['data']['new_constraints'][$k . '.' . $k2 . '.' . $k3 . '.' . $k4] = array('type'=>'constraint_new', 'name'=>$k4, 'table' => $k2 . '.' . $k3, 'index'=>$v4);
							} else {
								// comparing structure
								if (md5(serialize($v4))!=md5(serialize($data['slave']['constraints'][$k][$k2][$k3][$k4]))) {
									$result['hint'][] = "Constraint/index $k4 in table $k2.$k3 has changed! Rebuilding...";
									$result['data']['delete_constraints'][$k . '.' . $k2 . '.' . $k3 . '.' . $k4] = array('type'=>'constraint_delete', 'name'=>$k4, 'table'=>$k2 . '.' . $k3, 'index_full_name'=>$k2 . '.' . $k4, 'index'=>$v4);
									$result['data']['new_constraints'][$k . '.' . $k2 . '.' . $k3 . '.' . $k4] = array('type'=>'constraint_new', 'name'=>$k4, 'table' => $k2 . '.' . $k3, 'index'=>$v4);
								}
							}
						}
					}
				}
			}
			// delete constraints
			foreach ($data['slave']['constraints'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					foreach ($v2 as $k3=>$v3) {
						foreach ($v3 as $k4=>$v4) {
							if (empty($data['master']['constraints'][$k][$k2][$k3][$k4])) {
								$result['data']['delete_constraints'][$k . '.' . $k2 . '.' . $k3 . '.' . $k4] = array('type'=>'constraint_delete', 'name'=>$k4, 'table'=>$k2 . '.' . $k3, 'index_full_name'=>$k2 . '.' . $k4, 'index'=>$v4);
							}
						}
					}
				}
			}

			// new views
			foreach ($data['master']['views'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					if (empty($data['slave']['views'][$k][$k2])) {
						$result['data']['new_views'][$k . '.' . $k2] = array('type'=>'view_new', 'name'=>$k . '.' . $k2, 'owner'=>$v2['view_owner'], 'definition'=>$v2['view_definition']);
					} else {
						// checking owner information
						if ($v2['view_owner']!=$data['slave']['views'][$k][$k2]['view_owner']) {
							$result['data']['new_view_owners'][$k . '.' . $k2] = array('type'=>'view_owner', 'name'=>$k . '.' . $k2, 'owner'=>$v2['view_owner']);
						}
						// if view has changed
						if ($v2['view_definition']!=$data['slave']['views'][$k][$k2]['view_definition']) {
							$result['hint'][] = "View $k.$k2 has different definition!";
							$result['data']['change_views'][$k . '.' . $k2] = array('type'=>'view_change', 'name'=>$k . '.' . $k2, 'owner'=>$v2['view_owner'], 'definition'=>$v2['view_definition']);
						}
					}
				}
			}

			// delete view
			foreach ($data['slave']['views'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					if (empty($data['master']['views'][$k][$k2])) {
						$result['data']['delete_views'][$k . '.' . $k2] = array('type'=>'view_delete', 'name'=>$k . '.' . $k2);
					}
				}
			}

			// functions
			if (!empty($data['master']['functions'])) {
	            foreach ($data['master']['functions'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    if (empty($data['slave']['functions'][$k][$k2])) {
	                        $result['data']['new_functions'][$k . '.' . $k2] = array('type'=>'function_new', 'name'=>$k . '.' . $k2, 'owner'=>$v2['function_owner'], 'definition'=>$v2['function_definition']);
	                    } else {
	                        // checking owner information
	                        if ($v2['function_owner']!=$data['slave']['functions'][$k][$k2]['function_owner']) {
	                            $result['data']['new_function_owners'][$k . '.' . $k2] = array('type'=>'function_owner', 'name'=>$k . '.' . $k2, 'owner'=>$v2['function_owner']);
	                        }
	                        // if view has changed
	                        if ($v2['function_definition']!=$data['slave']['functions'][$k][$k2]['function_definition']) {
	                            $result['hint'][] = "Function $k.$k2 has different definition!";
	                            $result['data']['change_functions'][$k . '.' . $k2] = array('type'=>'function_change', 'name'=>$k . '.' . $k2, 'owner'=>$v2['function_owner'], 'definition'=>$v2['function_definition']);
	                        }
	                    }
	                }
	            }
			}

			// delete function
			if (!empty($data['slave']['functions'])) {
	            foreach ($data['slave']['functions'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    if (empty($data['master']['functions'][$k][$k2])) {
	                        $result['data']['delete_function'][$k . '.' . $k2] = array('type'=>'function_delete', 'name'=>$k . '.' . $k2);
	                    }
	                }
	            }
			}

			// new trigger
			if (!empty($data['master']['triggers'])) {
	            foreach ($data['master']['triggers'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    foreach ($v2 as $k3=>$v3) {
	                        if (empty($data['slave']['triggers'][$k][$k2][$k3])) {
	                            $result['data']['new_triggers'][$k . '.' . $k2 . '.' . $k3] = array('type'=>'trigger_new', 'name'=>$k3, 'table' => $k . '.' . $k2, 'definition'=>$v3['trigger_definition']);
	                        } else {
	                            // its safe to compare definition
	                            if ($v3['trigger_definition']!=$data['slave']['triggers'][$k][$k2][$k3]['trigger_definition']) {
	                                $result['hint'][] = "Trigger $k.$k2.$k3 has different structure!";
	                                $result['data']['change_triggers'][$k . '.' . $k2 . '.' . $k3] = array('type'=>'trigger_change', 'name'=>$k3, 'table' => $k . '.' . $k2, 'definition'=>$v3['trigger_definition']);
	                            }
	                        }
	                    }
	                }
	            }
			}

			// delete triggers
			if (!empty($data['slave']['triggers'])) {
	            foreach ($data['slave']['triggers'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    foreach ($v2 as $k3=>$v3) {
	                        if (empty($data['master']['triggers'][$k][$k2][$k3])) {
	                            $result['data']['delete_triggers'][$k . '.' . $k2 . '.' . $k3] = array('type'=>'trigger_delete', 'name'=>$k3, 'table' => $k . '.' . $k2, 'definition'=>$v3['trigger_definition']);
	                        }
	                    }
	                }
	            }
			}

			// new domains
			if (!empty($data['master']['domains'])) {
	            foreach ($data['master']['domains'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    if (empty($data['slave']['domains'][$k][$k2])) {
	                        $result['data']['new_domains'][$k . '.' . $k2] = array('type'=>'domain_new', 'name'=>$k . '.' . $k2, 'owner'=>$v2['domain_owner'], 'definition'=>$v2);
	                    } else {
	                        // checking owner information
	                        if ($v2['domain_owner']!=$data['slave']['domains'][$k][$k2]['domain_owner']) {
	                            $result['data']['new_domain_owners'][$k . '.' . $k2] = array('type'=>'domain_owner', 'name'=>$k . '.' . $k2, 'owner'=>$v2['domain_owner']);
	                        }
	                        // comparing structure
	                        $slave = $data['slave']['domains'][$k][$k2];
	                        $master = $v2;
	                        unset($slave['domain_owner'], $master['domain_owner']);
	                        if (md5(serialize($slave))!=md5(serialize($master))) {
	                            $result['hint'][] = "Domain $k.$k2 has different structure!";
	                        }
	                    }
	                }
	            }
			}

			// delete domain
			if (!empty($data['slave']['domains'])) {
	            foreach ($data['slave']['domains'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    if (empty($data['master']['domains'][$k][$k2])) {
	                        $result['data']['delete_domains'][$k . '.' . $k2] = array('type'=>'domain_delete', 'name'=>$k . '.' . $k2);
	                    }
	                }
	            }
			}

			// find all sequences for serial and bigserial columns
			$sequence_serial_lock = array();
			foreach ($data['master']['columns'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					foreach ($v2 as $k3=>$v3) {
						if (in_array($v3['data_type'], array('bigint', 'integer')) && strpos($v3['column_default'], 'nextval(')!==false && strpos($v3['column_default'], "_seq'::regclass)")) {
							$temp = str_replace(array("nextval('", "'::regclass)"), '', $v3['column_default']);
							$temp = explode('.', $temp);
							$s = isset($temp[1]) ? $temp[1] : $temp[0];
							$sequence_serial_lock[$v3['schema_name']][$s] = $v3;
						}
					}
				}
			}
			foreach ($data['slave']['columns'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					foreach ($v2 as $k3=>$v3) {
						if (in_array($v3['data_type'], array('bigint', 'integer')) && strpos($v3['column_default'], 'nextval(')!==false && strpos($v3['column_default'], "_seq'::regclass)")) {
							$temp = str_replace(array("nextval('", "'::regclass)"), '', $v3['column_default']);
							$temp = explode('.', $temp);
							$s = isset($temp[1]) ? $temp[1] : $temp[0];
							$sequence_serial_lock[$v3['schema_name']][$s] = $v3;
						}
					}
				}
			}

			// new sequences
			if (!empty($data['master']['sequences'])) {
	            foreach ($data['master']['sequences'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    // do not process serial sequences
	                    if (!empty($sequence_serial_lock[$k][$k2])) continue;
	                    if (empty($data['slave']['sequences'][$k][$k2])) {
	                        $result['data']['new_sequences'][$k . '.' . $k2] = array('type'=>'sequences_new', 'name'=>$k . '.' . $k2, 'owner'=>$v2['sequence_owner'], 'definition'=>$v2);
	                    } else {
	                        // checking owner information
	                        if ($v2['sequence_owner']!=$data['slave']['sequences'][$k][$k2]['sequence_owner']) {
	                            $result['data']['new_sequence_owners'][$k . '.' . $k2] = array('type'=>'sequence_owner', 'name'=>$k . '.' . $k2, 'owner'=>$v2['sequence_owner']);
	                        }
	                    }
	                }
	            }
			}

			// delete sequences
			if (!empty($data['slave']['sequences'])) {
	            foreach ($data['slave']['sequences'] as $k=>$v) {
	                foreach ($v as $k2=>$v2) {
	                    // do not process serial sequences
	                    if (!empty($sequence_serial_lock[$k][$k2])) continue;
	                    if (empty($data['master']['sequences'][$k][$k2])) {
	                        $result['data']['delete_sequences'][$k . '.' . $k2] = array('type'=>'sequence_delete', 'name'=>$k . '.' . $k2);
	                    }
	                }
	            }
			}

			// final step generating sql
			foreach ($result['data'] as $k=>$v) {
				foreach ($v as $k2=>$v2) {
					$result['data'][$k][$k2]['sql'] = self::render_sql($v2['type'], $v2);
				}
			}

		} while(0);

		// we need to clean up the data
		if (!empty($options['clean'])) {
			foreach ($result['data'] as $k=>$v) {
				if (empty($v)) unset($result['data'][$k]);
			}
		}

		// success if no errors
		if (empty($result['error'])) $result['success'] = true;
		return $result;
	}

	/**
	 * Render sql
	 * 
	 * @param string $type
	 * @param array $data
	 * @param arary $options
	 * @return string
	 * @throws Exception
	 */
	public static function render_sql($type, $data, $options = array()) {
		$result = '';
		switch ($type) {
			// schema
			case 'schema':
				$result = "CREATE SCHEMA {$data['name']} AUTHORIZATION {$data['owner']};";
				break;
			case 'schema_owner':
				$result = "ALTER SCHEMA {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'schema_delete':
				$result = "DROP SCHEMA {$data['name']};";
				break;
			// columns
			case 'column_delete':
				$result = "ALTER TABLE {$data['table']} DROP COLUMN {$data['name']};";
				break;
			case 'column_new':
				$temp = self::render_type($data['column']);
				$type = $temp['type'];
				$default = $temp['default'];
				if (empty($options['column_new_no_alter'])) {
					$result = "ALTER TABLE {$data['table']} ADD COLUMN {$data['name']} {$type}" . ($default!==null ? (' DEFAULT ' . $default) : '') . (!empty($data['column']['is_nullable']) ? (' ' . $data['column']['is_nullable']) : '') . ";";
				} else {
					$result = "{$data['name']} {$type}" . ($default!==null ? (' DEFAULT ' . $default) : '') . (!empty($data['column']['is_nullable']) ? (' ' . $data['column']['is_nullable']) : '');
				}
				break;
			case 'column_change':
				$result = '';
				$master = self::render_type($data['column']);
				$slave = self::render_type($data['existing_column']);
				if ($master['type']!=$slave['type']) {
					$result.= "ALTER TABLE {$data['table']} ALTER COLUMN {$data['name']} SET DATA TYPE {$master['type']};\n";
				}
				if ($master['default']!=$slave['default']) {
					$temp = empty($master['default']) ? ' DROP DEFAULT' : ('SET DEFAULT ' . $master['default']);
					$result.= "ALTER TABLE {$data['table']} ALTER COLUMN {$data['name']} $temp;\n";
				}
				if ($master['not_null']!=$slave['not_null']) {
					$temp = empty($master['not_null']) ? 'DROP'  : 'SET';
					$result.= "ALTER TABLE {$data['table']} ALTER COLUMN {$data['name']} $temp NOT NULL;\n";
				}
				break;
			// table
			case 'table_owner':
				$result = "ALTER TABLE {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'table_new':
				$columns = array();
				foreach ($data['columns'] as $k=>$v) {
					$columns[] = self::render_sql('column_new', array('table'=>'', 'name'=>$k, 'column'=>$v), array('column_new_no_alter'=>true));
				}
				$result = "CREATE TABLE {$data['name']} (\n\t";
					$result.= implode(",\n\t", $columns);
				$result.= "\n);";
				$result.= "\nALTER TABLE {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'table_delete':
				$result = "DROP TABLE {$data['name']};";
				break;
			// view
			case 'view_new':
				$result = "CREATE OR REPLACE VIEW {$data['name']} AS {$data['definition']}\nALTER VIEW {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'view_change':
				$result = "DROP VIEW {$data['name']};\nCREATE OR REPLACE VIEW {$data['name']} AS {$data['definition']}\nALTER VIEW {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'view_delete':
				$result = "DROP VIEW {$data['name']};";
				break;
			case 'view_owner':
				$result = "ALTER TABLE {$data['name']} OWNER TO {$data['owner']};";
				break;
			// index/foreign key/unique/primary key
			case 'constraint_new':
				if ($data['index']['constraint_type']=='INDEX') {
					$result = "CREATE INDEX {$data['name']} ON {$data['table']} USING {$data['index']['index_type']} (" . implode(", ", $data['index']['column_names']) . ");";
				} else if (in_array($data['index']['constraint_type'], array('PRIMARY KEY', 'UNIQUE'))) {
					$result = "ALTER TABLE {$data['table']} ADD CONSTRAINT {$data['name']} {$data['index']['constraint_type']} (" . implode(", ", $data['index']['column_names']) . ");";
				} else if ($data['index']['constraint_type']=='FOREIGN_KEY') {
					if ($data['index']['match_option']=='NONE') $data['index']['match_option'] = 'SIMPLE';
					$result = "ALTER TABLE {$data['table']} ADD CONSTRAINT {$data['name']} FOREIGN KEY (" . implode(", ", $data['index']['column_names']) . ") REFERENCES {$data['index']['foreign_schema_name']}.{$data['index']['foreign_table_name']} (" . implode(", ", $data['index']['foreign_column_names']) . ") MATCH {$data['index']['match_option']} ON UPDATE {$data['index']['update_rule']} ON DELETE {$data['index']['delete_rule']};";
				} else if ($data['index']['constraint_type']=='CHECK') {
					$result = "ALTER TABLE {$data['table']} ADD CONSTRAINT {$data['name']} CHECK {$data['index']['match_option']};";
				}
				break;
			case 'constraint_delete':
				if ($data['index']['constraint_type']=='INDEX') {
					$result = "DROP INDEX {$data['index_full_name']};";
				} else if (in_array($data['index']['constraint_type'], array('PRIMARY KEY', 'UNIQUE', 'FOREIGN_KEY', 'CHECK'))) {
					$result = "ALTER TABLE {$data['table']} DROP CONSTRAINT {$data['name']};";
				}
				break;
			// domains
			case 'domain_new':
				$result = "CREATE DOMAIN {$data['name']} AS {$data['definition']['data_type']}" . ($data['definition']['domain_default']!==null ? (' DEFAULT ' . $data['definition']['domain_default']) : '') . (!empty($data['definition']['is_nullable']) ? (' ' . $data['definition']['is_nullable']) : '') . ";\n";
				// adding constraints
				if (!empty($data['definition']['constraint_name'])) {
					foreach ($data['definition']['constraint_name'] as $k=>$v) {
						$result.= "ALTER DOMAIN {$data['name']} ADD CONSTRAINT {$v} {$data['definition']['constraint_definition'][$k]};\n"; 
					}
				}
				// adding owner name
				$result.= "ALTER DOMAIN {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'domain_delete':
				$result = "DROP DOMAIN {$data['name']};";
				break;
			case 'domain_owner':
				$result.= "ALTER DOMAIN {$data['name']} OWNER TO {$data['owner']};";
				break;
			// sequences
			case 'sequences_new':
				$result = "CREATE SEQUENCE {$data['name']} INCREMENT {$data['definition']['sequence_increment']} MINVALUE {$data['definition']['sequence_minvalue']} MAXVALUE {$data['definition']['sequence_maxvalue']} START {$data['definition']['sequence_start']}" . ($data['definition']['sequence_is_cycle'] ? ' NO' : '') . " CYCLE;\n";
				$result.= "ALTER SEQUENCE {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'sequence_delete':
				$result = "DROP SEQUENCE {$data['name']};";
				break;
			case 'sequence_owner':
				$result = "ALTER SEQUENCE {$data['name']} OWNER TO {$data['owner']};";
				break;
			// functions
			case 'function_new':
				$result = trim($data['definition']) . ";";
				$result.= "ALTER FUNCTION {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'function_change':
				$result = "DROP FUNCTION {$data['name']};\n";
				$result.= trim($data['definition']) . ";";
				$result.= "ALTER FUNCTION {$data['name']} OWNER TO {$data['owner']};";
				break;
			case 'function_delete':
				$result = "DROP FUNCTION {$data['name']};";
				break;
			case 'function_owner':
				$result = "ALTER FUNCTION {$data['name']} OWNER TO {$data['owner']};";
				break;
			// trigger
			case 'trigger_new':
				$result.= trim($data['definition']) . ";";
				break;
			case 'trigger_delete':
				$result = "DROP TRIGGER {$data['name']} ON {$data['table']};";
				break;
			case 'trigger_change':
				$result = "DROP TRIGGER {$data['name']} ON {$data['table']};\n";
				$result.= trim($data['definition']) . ";";
				break;
			default:
				// nothing
				Throw new Exception($type . '?');
		}
		return $result;
	}

	/**
	 * Fix data type, default and not null
	 * 
	 * @param array $column
	 * @return array
	 */
	private static function render_type($column) {
		$type = $column['data_type'];
		$default = $column['column_default'];
		if (!empty($column['character_maximum_length'])) $type.= '(' . $column['character_maximum_length'] . ')';
		// numeric data type
		if ($column['data_type']=='numeric') {
			$type.= '(' . $column['numeric_precision'] . ',' . $column['numeric_scale'] . ')';
		}
		// serial data type
		if ($type=='integer' && strpos($default, 'nextval(')!==false) {
			$type = 'serial';
			$default = null;
		}
		// bigserial data type
		if ($type=='bigint' && strpos($default, 'nextval(')!==false) {
			$type = 'bigserial';
			$default = null;
		}
		// array data type
		if ($type=='ARRAY') {
			$type = str_replace('_', '', $column['data_type_udt']) . '[]';
		}
		return array('type'=>$type, 'default'=>$default, 'not_null'=>$column['is_nullable']);
	}

	/**
	 * Compare data 
	 * @param string $master_link
	 * @param string $slave_link
	 * @param array $tables
	 * @param string $table
	 * @param boolean $delete
	 * @return array
	 */
	public static function compare_data($master_link, $slave_link, $tables, $table, $delete = false) {
		$result = array(
			'success' => true,
			'error' => array(),
			'data' => array()
		);

		do {
			// getting information
			$data = array();
			unset($tables['*']);

			// get a list of primary keys
			$pks = self::get('constraints', $master_link, array('where'=>array('constraint_type'=>'PRIMARY KEY')));

			foreach ($tables as $k=>$v) {
				$data['master'] = array();
				$data['slave'] = array();
				// checking if we need to fix this table
				if (!empty($table)) {
					if (is_array($table)) {
						if (!in_array($k, $table)) continue;
					} else {
						if ($table!=$k) continue;
					}
				}
				// load primary key
				$temp = explode('.', $k);
				$current = current($pks['data']['PRIMARY KEY'][$temp[0]][$temp[1]]);
				$pk = $current['column_names'];

				// we skip tables without primary keys
				if (empty($pk)) continue;

				// select
				$sql = "SELECT * FROM $k";
				// master database first
				$temp = db::query($sql, $pk, array(), $master_link);
				if (!empty($temp['error'])) {
					$result['error'] = array_merge($result['error'], $temp['error']);
				} else {
					$data['master'] = $temp['rows'];
				}
				// slave database second
				$temp = db::query($sql, $pk, array(), $slave_link);
				// we have situations when tables does not exists but we need to insert the data
				if (!empty($temp['error']) && strpos($temp['error'][0], 'ERROR:  42P01:')===false) {
					$result['error'] = array_merge($result['error'], $temp['error']);
				} else {
					$data['slave'] = $temp['rows'];
				}

				$full = array_merge5($data['slave'], $data['master']);
				$diff = array();
				$pk = array_fix($pk);
				self::array_diff_assoc_recursive($full, $pk, $pk, $full, $data['master'], $data['slave'], $diff);
				if (!$delete) unset($diff['delete']);

				// if we found differences
				if (!empty($diff)) {
					// update first
					if (!empty($diff['update'])) {
						foreach ($diff['update'] as $k2=>$v2) {
							$key = implode('::', $v2['pk']);
							$result['data']['update'][$k][$key]['sql'] = 'UPDATE ' . $k . ' SET ' . db::prepare_condition($v2['value'], ', ', $slave_link) . ' WHERE ' . db::prepare_condition($v2['pk'], 'AND', $slave_link) . ';';
						}
					}
					// we insert
					if (!empty($diff['insert'])) {
						foreach ($diff['insert'] as $k2=>$v2) {
							$key = implode('::', $v2['pk']);
							$result['data']['insert'][$k][$key]['sql'] = 'INSERT INTO ' . $k . ' (' . db::prepare_expression(array_keys($v2['value'])) . ') VALUES (' . db::prepare_values($v2['value'], $slave_link) . ");";
						}
					}
					// we delete
					if (!empty($diff['delete'])) {
						foreach ($diff['delete'] as $k2=>$v2) {
							$key = implode('::', $v2['pk']);
							$result['data']['delete'][$k][$key]['sql'] = 'DELETE FROM ' . $k . ' WHERE ' . db::prepare_condition($v2['pk'], ' AND ', $slave_link) . ';';
						}
					}
				}
			}

		} while(0);
		if (empty($result['error'])) $result['success'] = true;
		return $result;
	}

	/**
	 * Compare recursive
	 * 
	 * @param array $arr
	 * @param mixed $pk
	 * @param mixed $pk_full
	 * @param array $full
	 * @param array $master
	 * @param array $slave
	 * @param array $diff
	 */
	public static function array_diff_assoc_recursive($arr, $pk, $pk_full, & $full, & $master, & $slave, & $diff) {
		if (!empty($pk)) {
			$pk2 = array_shift($pk);
			foreach ($arr as $k=>$v) {
				$subdata2 = $v;
				self::array_diff_assoc_recursive($subdata2, $pk, $pk_full, $full, $master, $slave, $diff);
			}
		} else {
			// comparing
			$pk3 = array();
			$pk4 = array();
			$pk_full = array_fix($pk_full);
			foreach ($pk_full as $v) {
				$pk3[]= trim($arr[$v]);
				$pk4[$v] = trim($arr[$v]);
			}
			$master_data = array_key_get($master, $pk3);
			$slave_data = array_key_get($slave, $pk3);
			if (empty($slave_data) && !empty($master_data)) {
				// we need to add one row
				$diff['insert'][] = array('value'=>$master_data, 'pk'=>$pk4);
			} else if (empty($master_data) && !empty($slave_data)) {
				// we delete
				$diff['delete'][] = array('value'=>$arr, 'pk'=>$pk4);
			} else {
				if (!empty($slave_data) && !empty($master_data)) {
					foreach ($slave_data as $k2=>$v2) $slave_data[$k2] = str_replace("\r\n", "\n", $v2);
					foreach ($master_data as $k2=>$v2) $master_data[$k2] = str_replace("\r\n", "\n", $v2);
					if ($slave_data!=$master_data) {
						// we update
						$temp = array();
						foreach ($master_data as $k=>$v) if ($v!=$slave_data[$k]) $temp[$k] = $v;
						$diff['update'][] = array('value'=>$temp, 'pk'=>$pk4);
					}
				}
			}
		}
	}
}
