<?php

class object_html_color_groups extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_html_color_group_';
	public $columns = [
		'code' => ['name' => 'Group Code', 'type' => 'varchar', 'length' => 30],
		'name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		'basic' => ['name' => 'Basic'],
		'reds' => ['name' => 'Red(s)'],
		'pinks' => ['name' => 'Pink(s)'],
		'oranges' => ['name' => 'Orange(s)'],
		'yellows' => ['name' => 'Yellow(s)'],
		'purples' => ['name' => 'Purple(s)'],
		'greens' => ['name' => 'Green(s)'],
		'blues' => ['name' => 'Blue(s)'],
		'browns' => ['name' => 'Brown(s)'],
		'whites' => ['name' => 'White(s)'],
		'grays' => ['name' => 'Gray(s)'],
	];
}