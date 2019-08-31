<?php

namespace Object\Content;
class ImportFormats extends \Object\Data {
	public $module_code = 'NO';
	public $title = 'N/O Content Import Formats';
	public $column_key = 'format';
	public $column_prefix = '';
	public $columns = [
		'format' => ['name' => 'Format', 'type' => 'varchar', 'length' => 100],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'model' => ['name' => 'Model', 'type' => 'text'],
		'delimiter' => ['name' => 'Delimiter', 'type' => 'text'],
		'enclosure' => ['name' => 'Enclosure', 'type' => 'text'],
		'extension' => ['name' => 'Extension', 'type' => 'text'],
		'content_type' => ['name' => 'Content Type', 'type' => 'text']
	];
	public $data = [];
}