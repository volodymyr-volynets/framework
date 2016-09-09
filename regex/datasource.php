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
		//  - [data[name][param_name]]
		//  - [table[name][param_name]]
		return regex_base::parse($value, ['datasource', 'table', 'data']);
	}
}