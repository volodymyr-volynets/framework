<?php

namespace Object\Controller\Model;
class UsageCodes extends \Object\Data {
	public $column_key = 'code';
	public $column_prefix = '';
	public $columns = [
		'code' => ['name' => 'Usage Code', 'type' => 'varchar', 'length' => 100],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'message' => ['name' => 'Message', 'type' => 'text'],
		'methods' => ['name' => 'Methods', 'type' => 'array'],
		'history' => ['name' => 'History', 'type' => 'boolean'],
	];
	public $data = [
		'menu_item_click' => ['name' => 'Menu Item Click', 'message' => 'User clicked on [menu_name] menu item in [module_name] module.', 'methods' => ['GET'], 'history' => true],
		'controller_opened' => ['name' => 'Page Opened', 'message' => 'User opened [page_name] page in [module_name] module.', 'methods' => ['GET'], 'history' => true],
		// form related actions
		'form_new' => ['name' => 'Form New', 'message' => 'User clicked to create new record in [form_name] form.', 'methods' => ['*'], 'history' => true],
		'form_opened' => ['name' => 'Form Opened', 'message' => 'User opened a record in [form_name] form, record # is [id].', 'methods' => ['*'], 'history' => true],
		'form_inserted' => ['name' => 'Form Inserted', 'message' => 'User created new record in [form_name] form, record # is [id].', 'methods' => [], 'history' => false],
		'form_updated' => ['name' => 'Form Updated', 'message' => 'User updated a record in [form_name] form, record # is [id].', 'methods' => [], 'history' => false],
		'form_deleted' => ['name' => 'Form Deleted', 'message' => 'User deleted a record in [form_name] form, record # is [id].', 'methods' => [], 'history' => false],
		// list and report
		'list_opened' => ['name' => 'List Opened', 'message' => 'User opened [list_name] list.', 'methods' => ['*'], 'history' => true],
		'report_opened' => ['name' => 'Report Opened', 'message' => 'User opened [report_name] report.', 'methods' => ['*'], 'history' => true],
		// notification sent
		// chat sent
	];
}