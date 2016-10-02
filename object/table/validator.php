<?php

class object_table_validator {

	/**
	 * Validate multiple options/autocompletes at the same time
	 *
	 * @param array $options
	 * @return array
	 */
	public function validate_options_multiple($options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'discrepancies' => []
		];
		$mass_sql = [];
		foreach ($options as $k => $v) {
			$model = factory::model($v['model'], true);
			$values = [
				$v['field'] => $v['values']
			];
			$where2 = [];
			// values and options_active
			$temp2 = [];
			$temp2[] = $model->db_object->prepare_condition($values, 'AND');
			if (!empty($v['options_active'])) {
				$temp2[] = $model->db_object->prepare_condition($v['options_active'], 'AND');
			}
			$where2[] = '(' . implode(' AND ', $temp2) . ')';
			// processing existing values
			if (!empty($v['existing_values'])) {
				$existing_values = [
					$v['field'] => $v['existing_values']
				];
				$where2[] = $model->db_object->prepare_condition($existing_values, 'AND');
			}
			// params must be there
			$where3 = $model->db_object->prepare_condition($v['params'], 'AND');
			if (empty($where3)) $where3 = '1=1';
			$where = '((' . $where3 . ') AND (' . implode(' OR ', $where2) . '))';
			//$where = $this->db_object->prepare_condition(array_merge_hard($v['params'] ?? [], $temp), 'AND');
			$fields = "concat_ws('', " . implode(', ', array_keys($values)) . ")";
			$mass_sql[] = <<<TTT
				SELECT
					'{$k}' validate_name,
					{$fields} validate_value
				FROM {$model->name}
				WHERE 1=1
					AND {$where}
TTT;
		}
		$mass_sql = implode("\n\nUNION ALL\n\n", $mass_sql);
		$temp = $model->db_object->query($mass_sql);
		if ($temp['success']) {
			// generate array of unique values
			$unique = [];
			foreach ($temp['rows'] as $k => $v) {
				if (!isset($unique[$v['validate_name']])) {
					$unique[$v['validate_name']] = [];
				}
				$unique[$v['validate_name']][] = $v['validate_value'];
			}
			// find differencies
			foreach ($options as $k => $v) {
				// see if we found values
				if (!isset($unique[$k])) {
					$result['discrepancies'][$k] = count($v['values']);
				} else {
					foreach ($v['values'] as $v2) {
						if (!in_array($v2 . '', $unique[$k])) {
							if (!isset($result['discrepancies'][$k])) {
								$result['discrepancies'][$k] = 0;
							}
							$result['discrepancies'][$k]++;
						}
					}
				}
			}
			$result['success'] = true;
		}
		return $result;
	}
}
