<?php

class object_acl_maps extends object_data {
	public $column_key = null;
	public $column_prefix = 'no_object_acl_map_';
	public $columns = [
		'group_code' => ['name' => 'Group Code', 'domain' => 'acl_code'],
		'link_code' => ['name' => 'Link Code', 'domain' => 'acl_code'],
		'action_code' => ['name' => 'Action Code', 'domain' => 'acl_code']  // important we must inactivate and not delete
	];
	public $optmultis_map = [
		'group_code' => ['alias' => 'group_code', 'model' => 'object_acl_groups'],
		'link_code' => ['alias' => 'link_code', 'model' => 'object_acl_links'],
		'action_code' => ['alias' => 'action_code', 'model' => 'object_acl_actions']
	];
	public $data = [
		//'[group+link+action]' => ['group_code' => '[group_code]', 'link_code' => '[link_code]', 'action_code' => '[action_code]'],
		'entities.general.view' => ['group_code' => 'entities', 'link_code' => 'general', 'action_code' => 'view'],
		'entities.general.new' => ['group_code' => 'entities', 'link_code' => 'general', 'action_code' => 'new'],
		'admin.general.view' => ['group_code' => 'admin', 'link_code' => 'general', 'action_code' => 'view'],
		'admin.general.new' => ['group_code' => 'admin', 'link_code' => 'general', 'action_code' => 'new']
	];
}