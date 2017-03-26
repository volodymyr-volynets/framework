<?php

namespace Object\Data;
class Aliases extends \Object\Data {
	public $column_key = 'no_data_alias_code';
	public $column_prefix = 'no_data_alias_';
	public $orderby = ['no_data_alias_name' => SORT_ASC];
	public $columns = [
		'no_data_alias_code' => ['name' => 'Alias Code', 'type' => 'varchar', 'length' => 50],
		'no_data_alias_name' => ['name' => 'Name', 'type' => 'text'],
		'no_data_alias_model' => ['name' => 'Model', 'type' => 'text'],
		'no_data_alias_column' => ['name' => 'Code Column', 'type' => 'text']
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
	public function getIdByCode($alias, $code, $id_only = true) {
		$class = $this->data[$alias]['no_data_alias_model'];
		$model = new $class();
		$columns = [];
		if ($id_only) {
			$columns[] = $model->column_prefix . 'id';
		}
		$data = $model->get([
			'columns' => $columns,
			'where' => [
				$this->data[$alias]['no_data_alias_column'] => $code . ''
			],
			'single_row' => true
		]);
		if (!$id_only) {
			return $data;
		} else {
			return $data[$model->column_prefix . 'id'];
		}
	}
}