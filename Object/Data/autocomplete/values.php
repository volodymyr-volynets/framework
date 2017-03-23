<?php

class object_data_autocomplete_values extends object_datasource {
	public $db_link;
	public $db_link_flag;
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		$model = Factory::model($options['model']);
		$db = $model->db_object();
		$where = '';
		if (!empty($options['where'])) {
			$where = 'AND ' . $db->prepare_condition($options['where']);
		}
		$fields = $options['fields'];
		if (!in_array($options['pk'], $options['fields'])) {
			$fields[] = $options['pk'];
		}
		$fields = implode(', ', $fields);
		return <<<TTT
			SELECT
				{$fields}
			FROM [table[{$options['model']}]] a
			WHERE 1=1
					{$where}
TTT;
	}
}