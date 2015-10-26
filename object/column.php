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
		'type' => ['name' => 'Data Type', 'description' => 'Datatype comes from object.type.columns'],
		'length' => ['name' => 'Length', 'description' => 'String length'],
		'precision' => ['name' => 'Precision', 'description' => 'Numeric precision'],
		'scale' => ['name' => 'Scale', 'description' => 'Numeric scale'],
		'null' => ['name' => 'Null', 'description' => 'Whether column is null'],
		'default' => ['name' => 'Default', 'description' => 'Default value'],
		'sequence' => ['name' => 'Sequence', 'description' => 'Value would be from sequence'],
		// business logic attributes
		'function' => ['name' => 'Function', 'description' => 'Value to be run though function'],
		'empty' => ['name' => 'Empty', 'description' => 'Value can not be empty'],
		'maxlength' => ['name' => 'Max Length', 'description' => 'Values maximum length']
	];
}