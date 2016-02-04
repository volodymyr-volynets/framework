<?php

class object_column {

	/**
	 * Available column attributes
	 *
	 * @var array 
	 */
	public $data = [
		'name' => ['name' => 'Name', 'description' => 'Name of a column'],
		// ddl related attributes
		'domain' => ['name' => 'Domain', 'description' => 'Domain from object_type_table_domain'],
		'type' => ['name' => 'Data Type', 'description' => 'Datatype from object_type_table_column'],
		'length' => ['name' => 'Length', 'description' => 'String length'],
		'precision' => ['name' => 'Precision', 'description' => 'Numeric precision'],
		'scale' => ['name' => 'Scale', 'description' => 'Numeric scale'],
		'null' => ['name' => 'Null', 'description' => 'Whether column is null'],
		'default' => ['name' => 'Default', 'description' => 'Default value'],
		// business logic attributes
		'function' => ['name' => 'Function', 'description' => 'Value to be run though function'],
		'empty' => ['name' => 'Empty', 'description' => 'Value can not be empty'],
		'maxlength' => ['name' => 'Max Length', 'description' => 'Values maximum length']
	];
}