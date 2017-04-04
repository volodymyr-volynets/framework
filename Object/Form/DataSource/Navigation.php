<?php

namespace Object\Form\DataSource;
class Navigation extends \Object\Datasource {
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
		'value' => ['name' => 'Value', 'type' => 'mixed', 'required' => true],
		'depends' => ['name' => 'Depends', 'type' => 'mixed', 'required' => false],
	];

	public function query($parameters, $options = []) {
		$model = \Factory::model($parameters['model'], true);
		$this->db_link = $model->db_link;
		//$this->pk = $parameters['pk'];
		$column = $parameters['column'];
		$this->query = $model->queryBuilder()->select();
		$this->query->columns($parameters['pk']);
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
				if (!empty($options['where']['depends'])) {
					$subquery->whereMultiple('AND', $parameters['depends']);
					$this->query->whereMultiple('AND', $parameters['depends']);
				}
				$this->query->where('AND', ["a.{$column}", '=', $subquery]);
				break;
			case 'previous':
			case 'next':
				if (!empty($options['where']['depends'])) {
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
				if (!empty($options['where']['depends'])) {
					$this->query->whereMultiple('AND', $parameters['depends']);
				}
				$this->query->limit(1);
		}
	}
}