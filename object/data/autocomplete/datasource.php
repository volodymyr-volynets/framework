<?php

class object_data_autocomplete_datasource extends object_datasource {
	public $db_link;
	public $db_link_flag;
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		$model = factory::model($options['model']);
		$db = $model->db_object();
		$where = '';
		if (!empty($options['where'])) {
			$where = 'AND ' . $db->prepare_condition($options['where']);
		}
		$ts = $db->full_text_search_query($options['fields'], $options['search_text'] . '');
		$fields = $options['fields'];
		$sql_pk = '';
		// we automatically include main pk into a query
		if (!in_array($options['pk'], $options['fields'])) { // in_array($options['pk'], $model->pk) && 
			$fields[] = $options['pk'];
			// we need to include integer types to the query
			$temp = intval($options['search_text']);
			if ($model->columns[$options['pk']]['php_type'] == 'integer' && $temp != 0) {
				$sql_pk.= " OR {$options['pk']} = " . (int) $options['search_text'];
			}
		}
		$fields[] = $ts['rank'];
		$fields = implode(', ', $fields);
		$tmp = <<<TTT
			SELECT
				{$fields}
			FROM [table[{$options['model']}]] a
			WHERE 1=1
					{$where}
					AND (({$ts['where']}){$sql_pk})
			ORDER BY {$ts['orderby']} DESC, {$options['fields'][0]}
			LIMIT 11
TTT;
		return $tmp;
	}
}