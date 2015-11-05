<?php

class object_type_content extends object_data {

	/**
	 * A list of content types
	 *
	 * @var array
	 */
	public $data = [
		// data transfer
		'application/javascript' => ['name' => 'Javascript'],
		'application/json' => ['name' => 'JSON'],
		'application/xml' => ['name' => 'XML'],
		// presentation
		'text/html' => ['name' => 'HTML', 'presentation' => 1],
		'application/pdf' => ['name' => 'PDF', 'presentation' => 1],
		'text/plain' => ['name' => 'Text', 'presentation' => 1],
		'application/vnd.ms-excel' => ['name' => 'Excel (xls)', 'presentation' => 1],
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['name' => 'Excel (xlsx)', 'presentation' => 1],
		// images
		'image/png' => ['name' => 'Png Image'],
	];

	/**
	 * Check if its a presentational content type
	 *
	 * @param string $content_type
	 * @return boolean
	 */
	public function is_presentational($content_type) {
		$data = $this->get(['presentation' => 1]);
		return !empty($data[$content_type]);
	}
}