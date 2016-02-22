<?php

class object_html_menu extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_object_html_menu_';
	public $columns = [
		'code' => ['name' => 'Menu Code', 'type' => 'varchar', 'length' => 255],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'i18n' => ['name' => 'I18n', 'type' => 'text'],
		'url' => ['name' => 'URL', 'type' => 'text'],
		'parent' => ['name' => 'Parent', 'type' => 'varchar', 'length' => 50]
	];
	public $options_map = [
		'name' => 'name',
		'i18n' => 'i18n',
		'url' => 'url',
		'parent' => 'parent'
	];
	public $data = [
		'test.test.test1' => ['name' => 'Test 1', 'url' => '/', 'parent' => null],
		'test.test.test2' => ['name' => 'Test 2', 'url' => '/', 'parent' => null],
		'test.test.test3' => ['name' => 'Test 3', 'url' => '/', 'parent' => null],
		'test.test.test4' => ['name' => 'Test 4', 'url' => '/', 'parent' => null],
		'test.test.test5' => ['name' => 'Test 5', 'url' => '/', 'parent' => null],
		'test.test.test6' => ['name' => 'Test 6', 'url' => '/', 'parent' => null],
		'test.test.test7' => ['name' => 'Test 7', 'url' => '/', 'parent' => null],
		'test.test.test8' => ['name' => 'Test 8', 'url' => '/', 'parent' => null],
		'test.test.test9' => ['name' => 'Test 9', 'url' => '/', 'parent' => null],
		'test.test.test10' => ['name' => 'Test 10', 'url' => '/', 'parent' => null],
		'test.test.test11' => ['name' => 'Test 11', 'url' => '/', 'parent' => null],
		'test.test.test12' => ['name' => 'Test 12', 'url' => '/', 'parent' => null],
		'test.test.test13' => ['name' => 'Test 13', 'url' => '/', 'parent' => null],
		'test.test.test14' => ['name' => 'Test 14', 'url' => '/', 'parent' => null],
	];
}
