<?php

namespace Object\Table;
class Complementary {

	/**
	 * Preload json data
	 *
	 * @param \Object\Table $model
	 * @param array $where
	 * @param array $columns
	 * @param array $values
	 */
	public static function jsonPreloadData(\Object\Table $model, array $where, array $columns, array & $values) {
		$result = $model->get([
			'where' => $where,
			'pk' => null
		]);
		foreach ($columns as $a => $c) {
			if (is_array($c)) {
				$only_columns = $c;
				$c = $a;
			} else {
				$only_columns = false;
			}
			if (!empty($result[0][$c]) && $result[0][$c] != 'null') {
				$temp = json_decode($result[0][$c], true);
				foreach ($temp as $k => $v) {
					if (!empty($only_columns) && !in_array($k, $only_columns)) continue;
					if (is_array($v) && !empty($v) && empty($values[$k])) {
						$values[$k] = $v;
					} else if (($values[$k] ?? '') == '') {
						$values[$k] = $v ?? null;
					}
				}
			}
		}
	}

	/**
	 * Save json data
	 *
	 * @param \Object\Table $model
	 * @param array $values
	 * @param \Object\Form\Base $form
	 * @param string $id_column
	 * @return bool
	 */
	public static function jsonSaveData(\Object\Table $model, array $values, \Object\Form\Base & $form, string $id_column) : bool {
		if (!empty($form->values[$id_column])) {
			$values[$id_column] = $form->values[$id_column];
		}
		$result = $model->collection()->merge($values);
		if (!$result['success']) {
			$form->error(DANGER, $result['error']);
			return false;
		}
		$form->values[$id_column] = $form->values[$id_column] ?? $result['new_serials'][$id_column];
		return true;
	}
}