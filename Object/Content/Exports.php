<?php

namespace Object\Content;
class Exports extends \Object\Data {
	public $column_key = 'no_content_export_code';
	public $column_prefix = 'no_content_export_';
	public $columns = [
		'no_content_export_code' => ['name' => 'Export Code', 'type' => 'varchar', 'length' => 100],
		'no_content_export_name' => ['name' => 'Name', 'type' => 'text']
	];
	public $data = [
		// data transfer
		'html' => ['no_content_export_name' => 'HTML'],
		'html2' => ['no_content_export_name' => 'HTML (Printable)']
	];
}