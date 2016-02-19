<?php

class object_content_types extends object_data {
	public $column_key = 'code';
	public $column_prefix = 'no_content_type_';
	public $columns = [
		'code' => ['name' => 'Content Type', 'type' => 'varchar', 'length' => 100],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'presentation' => ['name' => 'Is Presentational Flag', 'type' => 'boolean'],
	];
	public $data = [
		// data transfer
		'application/javascript' => ['name' => 'Javascript'],
		'application/json' => ['name' => 'JSON'],
		'application/xml' => ['name' => 'XML'],
		// presentation
		'text/html' => ['name' => 'HTML', 'presentation' => 1],
		'application/pdf' => ['name' => 'PDF', 'presentation' => 1],
		'text/plain' => ['name' => 'Text', 'presentation' => 1],
		'application/vnd.ms-excel' => ['name' => 'Excel (xls)', 'presentation' => 1],
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['name' => 'Excel (xlsx)', 'presentation' => 1],
		// images
		'image/png' => ['name' => 'Png Image'],
	];
}