<?php

class numbers_framework_object_form_model_content_types extends object_data {
	public $column_key = 'no_form_content_type_code';
	public $column_prefix = 'no_form_content_type_';
	public $orderby = ['no_form_content_type_order' => SORT_ASC];
	public $columns = [
		'no_form_content_type_code' => ['name' => 'Type', 'type' => 'text'],
		'no_form_content_type_name' => ['name' => 'Name', 'type' => 'text'],
		'no_form_content_type_model' => ['name' => 'Model', 'type' => 'text'],
		'no_form_content_type_order' => ['name' => 'Order', 'type' => 'smallint', 'default' => 0]
	];
	public $data = [
		'text/html' => ['no_form_content_type_name' => 'Screen (HTML)', 'no_form_content_type_model' => 'numbers_frontend_html_form_renderers_html_base', 'no_form_content_type_order' => -32000],
	];
}