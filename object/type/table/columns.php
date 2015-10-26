<?php

class object_type_table_columns {

	/**
	 * A list of data types
	 *
	 * @var array
	 */
	public $data = [
		'boolean' => ['name' => 'Boolean'],
		// numeric types
		'smallint' => ['name' => 'Small Integer'],
		'integer' => ['name' => 'Integer'],
		'bigint' => ['name' => 'Big Integer'],
		'numeric' => ['name' => 'Numeric'],
		// numbers with sequences, will be converted to autoincrement for some databases
		'smallserial' => ['name' => 'Serial Smallint'],
		'serial' => ['name' => 'Serial Integer'],
		'bigserial' => ['name' => 'Big Serial'],
		// text data types
		'char' => ['name' => 'Character'],
		'varchar' => ['name' => 'Character Varying'],
		'text' => ['name' => 'Text'],
		// json types
		'json' => ['name' => 'JSON'],
		// date types
		'date' => ['name' => 'Date'],
		'time' => ['name' => 'Time'],
		'datetime' => ['name' => 'Date & time'],
		// types specific to numbers framework
		'numbers_code' => ['name' => 'Code / ID'], // varchar(50)
		'numbers_hash' => ['name' => 'Hash'], // varchar(128) for sha512
		//  array data types, not supported everywhere
		//'smallint[]' => ['name' => 'Array Small Integer'],
		//'integer[]' => ['name' => 'Array Integer'],
		//'bigint[]' => ['name' => 'Array Big Integer'],
		//'text[]' => ['name' => 'Array Text'],
	];
}