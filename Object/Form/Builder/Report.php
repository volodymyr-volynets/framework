<?php

namespace Object\Form\Builder;
class Report {

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Construct
	 *
	 * @param string $name
	 * @param array $options
	 */
	public function __construct(array $options = []) {
		$this->options = $options;
	}

	/**
	 * Add report
	 *
	 * @param string $name
	 */
	public function addReport(string $report_name, array $options = []) {
		$this->data[$report_name] = [
			'name' => $report_name,
			'options' => $options,
			'header' => [],
			'data' => []
		];
	}

	/**
	 * Add header
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param array $header_column1
	 */
	public function addHeader(string $report_name, string $header_name, array $header_columns) {
		// set index
		$index = 0;
		foreach ($header_columns as $k => $v) {
			$header_columns[$k]['label_name'] = i18n(null, $v['label_name']);
			$header_columns[$k]['__index'] = $index;
			$index++;
		}
		// replace header
		$this->data[$report_name]['header'][$header_name] = $header_columns;
	}

	/**
	 * Add data
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param mixed $data_column1
	 */
	public function addData(string $report_name, string $header_name, $odd_even, array $data_columns) {
		// process header
		$header = $this->data[$report_name]['header'][$header_name];
		$temp = [];
		foreach ($header as $k => $v) {
			if (array_key_exists($k, $data_columns)) {
				$temp[$v['__index']] = $data_columns[$k];
			}
		}
		// add data
		$this->data[$report_name]['data'][] = [
			0 => $temp, // data
			1 => $odd_even,		// odd/even
			2 => false,			// separator
			3 => $header_name,  // header name
			4 => null,			// legend
		];
	}

	/**
	 * Add separator
	 *
	 * @param string $report_name
	 * @param string $header_name
	 */
	public function addSeparator(string $report_name) {
		$this->data[$report_name]['data'][] = [
			2 => true
		];
	}

	/**
	 * Add legend
	 *
	 * @param string $report_name
	 * @param string $message
	 */
	public function addLegend(string $report_name, string $message) {
		$this->data[$report_name]['data'][] = [
			4 => $message
		];
	}
}