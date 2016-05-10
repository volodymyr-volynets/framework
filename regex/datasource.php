<?php

class regex_datasource implements regex_interface {

	/**
	 * Parse
	 *
	 * @param string $value
	 * @return array
	 */
	public static function parse($value) {
		// parsing, examples:
		//  - [datasource[name][param_name]]
		//  - [model[name][param_name]]
		//  - [table[name][param_name]]
		//  - [subpart[name][param_name]]
		//  - [array[name]]
		return regex_base::parse($value, ['datasource', 'table', 'data']);
	}
}