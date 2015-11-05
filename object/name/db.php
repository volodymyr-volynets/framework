<?php

class object_name_db implements object_name_interface {

	/**
	 * Various types
	 *
	 * @var array
	 */
	public static $types = [
		'common' => ['name' => 'Common'],
		'schema' => ['name' => 'Schema'],
		'table' => ['name' => 'Table'],
		'column' => ['name' => 'Column'],
		'constraint' => ['name' => 'Constraint'],
		'index' => ['name' => 'Index'],
		'function' => ['name' => 'Function'],
		'sequence' => ['name' => 'Sequence']
	];

	/**
	 * Explain naming conventions
	 *
	 * @param string $type
	 * @return string
	 */
	public static function explain($type = null, $options = []) {
		$result = [];
		foreach (self::$types as $k => $v) {
			if (isset($type) && $type != $k) {
				continue;
			}
			switch ($k) {
				case 'common':
					$result[]= 'Common:';
					$result[]= '1. Only letters, numbers and the underscore are allowed in names, no longer than 30 characters.';
					$result[]= '2. All names are in "lowercase". Ignoring this rule usually leads referencing to tables and columns very clumsy because all names must be included in double quotes.';
					$result[]= '3. The first character in the name must be letter.';
					$result[]= '4. Words in names should be separated by underscores.';
					$result[]= '5. Keep the names meaningful and as short as possible.';
					break;
				case 'schema':
					$result[]= 'Schema:';
					$result[]= '1. Follow Common rules.';
					$result[]= '2. Suggestion to use two character abbreviation.';
					break;
				case 'table':
					$result[]= 'Table:';
					$result[]= '1. Follow Common rules with exception that table name can contain schema separated by dot.';
					$result[]= '2. Table names are in plural form (users, addresses, services).';
					$result[]= '3. If table name contains more than one word, they are separated with underscore. Only the last one is in plural, for example entity_addresses.';
					$result[]= '4. All tables have short aliases that are unique in a database. Aliases are not directly used in name of table, but they are used to create column names. For example, sm.sessions - sm_session, em.entities - em_entity.';
					break;
				case 'column':
					$result[]= 'Column:';
					$result[]= '1. All columns are in form {table_alias}_{column_name}. For example em_entity_id. This guarantees that column names are unique in a schema, except columns from history tables.';
					$result[]= '2. All columns are in singular.';
					$result[]= '3. If several tables contain columns with the same content use the same consistent column names, {column_alias} should be created for example em_entity_id has alias entity_id.';
					$result[]= '4. All tables should have surrogate primary key column in form {table_alias}_id, which is the first column in the table.';
					$result[]= '5. All foreign key columns are in form {table_alias}_{column_alias}. For example em_entaddr_entity_id.';
					break;
				case 'constraint':
					$result[]= 'Constraint:';
					$result[]= '1. Follow Common rules.';
					$result[]= '2. Primary Key name is formed {table_name}_pk.';
					$result[]= '3. Unique Constraint name is formed {column_name}_un. If we have multiple columns first column is used.';
					$result[]= '4. Foreign Key name is formed {column_name}_fk. If we have multiple columns first column is used.';
					break;
				case 'index':
					$result[]= 'Index:';
					$result[]= '1. Follow Common rules.';
					$result[]= '2. Name is formed {column_name}_idx. If we have multiple columns first column is used.';
					break;
				case 'function':
					$result[]= 'Function:';
					$result[]= '1. Follow Common rules with exception that function name can contain schema separated by dot.';
					break;
				case 'sequence':
					$result[]= 'Sequence:';
					$result[]= '1. Follow Common rules with exception that sequence name can contain schema separated by dot.';
					break;
			}
			$result[]= '';
		}
		if (!empty($options['html'])) {
			return nl2br(implode("\n", $result));
		} else {
			return $result;
		}
	}

	/**
	 * Check
	 *
	 * @param string $type
	 * @param string $name
	 */
	public static function check($type, $name) {
		$result = [
			'success' => false,
			'error' => []
		];
		if (!preg_match('/^[a-z]{1}[a-z0-9_\.]{0,30}$/', $name . '')) {
			$result['error'][] = 'Only letters, numbers and the underscore, no longer than 30 characters!';
		} else {
			if (!in_array($type, ['table', 'function', 'sequence']) && strpos($name, '.') !== false) {
				$result['error'][] = 'Dot is only allowed in table, function and sequence!';
			}
		}
		if (!$result['error']) {
			$result['success'] = true;
		}
		return $result;
	}
}