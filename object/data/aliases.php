<?php

class object_data_aliases extends object_data {
	public $column_key = 'no_data_alias_code';
	public $column_prefix = 'no_data_alias_';
	public $orderby = ['no_data_alias_name' => SORT_ASC];
	public $columns = [
		'no_data_alias_code' => ['name' => 'Alias Code', 'type' => 'varchar', 'length' => 50],
		'no_data_alias_name' => ['name' => 'Name', 'type' => 'text'],
		'no_data_alias_model' => ['name' => 'Model', 'type' => 'text']
	];
	public $data = [
		// data would come from overrides
	];

	/**
	 * Get id by code/alias
	 *
	 * @param string $alias
	 * @param string $code
	 * @param boolean $id_only
	 * @return mixed
	 */
	public function get_id_by_code($alias, $code, $id_only = true) {
		$class = $this->data[$alias]['no_data_alias_model'];
		$model = new $class();
		$data = $model->get(['where' => [$model->column_prefix . 'code' => $code . '']]);
		if (!$id_only) {
			return current($data);
		} else {
			$temp = current($data);
			return $temp[$model->column_prefix . 'id'];
		}
	}
}