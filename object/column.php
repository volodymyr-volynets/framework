<?php

class object_column {
	
	/**
	 * Available column attributes
	 *
	 * @var array 
	 */
	public $attributes = [
		'name' => ['name' => 'Name', 'description' => 'Name of a column'],
		// ddl related attributes
		'type' => ['name' => 'Data Type', 'description' => 'Datatype comes from object.type.columns'],
		'length' => ['name' => 'Length', 'description' => 'String length'],
		'null' => ['name' => 'Null', 'description' => 'Whether column is null'],
		'default' => ['name' => 'Default', 'description' => 'Default value'],
	];
}