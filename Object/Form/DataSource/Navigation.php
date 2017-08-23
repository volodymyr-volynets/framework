<?php

namespace Object\Form\DataSource;
class Navigation extends \Object\DataSource {
	public $db_link;
	public $db_link_flag;
	public $pk;
	public $columns;
	public $orderby;
	public $limit;
	public $single_row;
	public $single_value;
	public $options_map =[];
	public $column_prefix;

	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;

	public $primary_model;
	public $parameters = [
		'model' => ['name' => 'Model', 'type' => 'text', 'required' => true],
		'type' => ['name' => 'Type', 'type' => 'text', 'required' => true],
		'column' => ['name' => 'Column', 'type' => 'text', 'required' => true],
		'pk' => ['name' => 'Pk', 'type' => 'mixed', 'required' => true],
		'value' => ['name' => 'Value', 'type' => 'mixed'],
		'depends' => ['name' => 'Depends', 'type' => 'mixed'],
		'acl_datasource' => ['name' => 'ACL DataSource', 'type' => 'string'],
		'acl_parameters' => ['name' => 'ACL Parameters', 'type' => 'mixed'],
	];

	public function query($parameters, $options = []) {
		$model = \Factory::model($parameters['model'], true);
		$this->db_link = $model->db_link;
		//$this->pk = $parameters['pk'];
		$column = $parameters['column'];
		$this->query = $model->queryBuilder()->select();
		$this->query->columns($parameters['pk']);
		// acl datasource
		if (!empty($parameters['acl_datasource'])) {
			$acl_datasource = $parameters['acl_datasource'];
			$acl_pk = [];
			foreach ($parameters['pk'] as $v) {
				if ($v == $model->tenant_column) continue;
				$acl_pk[] = ['a.' . $v, '=', 'inner_a.' . $v, true];
			}
			$acl_parameters = $parameters['acl_parameters'] ?? [];
			$this->query->where('AND', function (& $query) use ($acl_datasource, $acl_pk, $acl_parameters) {
				$model = new $acl_datasource();
				$query = $model->queryBuilder(['alias' => 'inner_a', 'where' => $acl_parameters])->select();
				$query->columns(1);
				foreach ($acl_pk as $v) {
					$query->where('AND', $v);
				}
			}, 'EXISTS');
		}
		// adjust type based on value
		if (empty($parameters['value'])) {
			if ($parameters['type'] == 'previous') {
				$parameters['type'] = 'first';
			}
			if ($parameters['type'] == 'next') {
				$parameters['type'] = 'first';
			}
		} else {
			if ($parameters['type'] == 'previous') {
				$this->query->where('AND', ["a.{$column}", '<', $parameters['value']]);
			} else if ($parameters['type'] == 'next') {
				$this->query->where('AND', ["a.{$column}", '>', $parameters['value']]);
			} else if ($parameters['type'] == 'refresh') {
				$this->query->where('AND', ["a.{$column}", '=', $parameters['value']]);
			}
		}
		// generate query based on type
		switch ($parameters['type']) {
			case 'first':
			case 'last':
				$subquery = $model->queryBuilder()->select();
				if ($parameters['type'] == 'first') {
					$subquery->columns(['new_value' => "MIN({$column})"]);
				} else {
					$subquery->columns(['new_value' => "MAX({$column})"]);
				}
				$subquery->where('AND', ["a.{$column}", 'IS NOT', null]);
				if (!empty($parameters['depends'])) {
					$subquery->whereMultiple('AND', $parameters['depends']);
					$this->query->whereMultiple('AND', $parameters['depends']);
				}
				$this->query->where('AND', ["a.{$column}", '=', $subquery]);
				break;
			case 'previous':
			case 'next':
				if (!empty($parameters['depends'])) {
					$this->query->whereMultiple('AND', $parameters['depends']);
				}
				if ($parameters['type'] == 'previous') {
					$this->query->orderby([$column => SORT_DESC]);
				} else {
					$this->query->orderby([$column => SORT_ASC]);
				}
				$this->query->limit(1);
				break;
			case 'refresh':
			default:
				if (!empty($parameters['depends'])) {
					$this->query->whereMultiple('AND', $parameters['depends']);
				}
				$this->query->limit(1);
		}
	}
}