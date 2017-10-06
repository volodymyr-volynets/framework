<?php

namespace Object\Content;
class ExportFormats extends \Object\Data {
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