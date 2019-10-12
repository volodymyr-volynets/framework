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
	 * @param array $options
	 */
	public function __construct(array $options = []) {
		$this->options = $options;
	}

	/**
	 * From
	 *
	 * @var \Object\Content\Form
	 */
	private $form;

	/**
	 * Add report
	 *
	 * @param string $report_name
	 * @param object $form
	 * @param array $options
	 */
	public function addReport(string $report_name, & $form = null, array $options = []) {
		$this->data[$report_name] = [
			'name' => $report_name,
			'options' => $options,
			'header' => [],
			'header_options' => [],
			'filter' => [],
			'data' => [],
			'form_name' => '',
		];
		// extrace filter out of form
		if (isset($form)) {
			if (empty($options['skip_filter'])) {
				$this->data[$report_name]['filter'] = $form->generateFilter();
			}
			$this->form = & $form;
			$this->data[$report_name]['form_name'] = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $form->title);
			if (strlen($this->data[$report_name]['form_name']) > 31) {
				$this->data[$report_name]['form_name'] = substr($this->data[$report_name]['form_name'], 0, 28) . '...';
			}
		}
	}

	/**
	 * Add header
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param array $header_column1
	 */
	public function addHeader(string $report_name, string $header_name, array $header_columns, array $options = []) {
		// set index
		$index = 0;
		foreach ($header_columns as $k => $v) {
			$header_columns[$k]['label_name'] = i18n(null, $v['label_name']);
			$header_columns[$k]['__index'] = $index;
			$index++;
		}
		// replace header
		$this->data[$report_name]['header'][$header_name] = $header_columns;
		$this->data[$report_name]['header_options'][$header_name] = $options;
	}

	/**
	 * Get header for rendering
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @return array
	 */
	public function getHeaderForRender(string $report_name, string $header_name) : array {
		$columns = $this->data[$report_name]['header'][$header_name];
		foreach ($columns as $k => $v) {
			if ($k == 'blank' || empty($v['label_name'])) continue;
			$columns[$k]['as_header'] = true;
			$columns[$k]['value'] = $v['label_name'];
		}
		return $columns;
	}

	/**
	 * Add data
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param type $odd_even
	 * @param array $data_columns
	 * @param array $options
	 */
	public function addData(string $report_name, string $header_name, $odd_even, array $data_columns, array $options = [], array $summary_columns = []) {
		// process header
		$header = $this->data[$report_name]['header'][$header_name];
		$temp = [];
		foreach ($header as $k => $v) {
			if (array_key_exists($k, $data_columns)) {
				$temp[$v['__index']] = $data_columns[$k];
			}
		}
		// summary
		$summary = [];
		foreach ($header as $k => $v) {
			if (array_key_exists($k, $summary_columns)) {
				$summary[$v['__index']] = $summary_columns[$k];
			}
		}
		// add data
		$this->data[$report_name]['data'][] = [
			0 => $temp, // data
			1 => $odd_even, // odd/even
			2 => false, // separator
			3 => $header_name, // header name
			4 => null, // legend
			5 => $options, // options
			6 => $summary, // summary
		];
	}

	/**
	 * Add separator
	 *
	 * @param string $report_name
	 * @param string $type
	 */
	public function addSeparator(string $report_name, string $type = 'data') {
		$this->data[$report_name][$type][] = [
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
		$this->data[$report_name]['data_legend'][] = [
			4 => $message
		];
	}

	/**
	 * Add number of rows
	 *
	 * @param string $report_name
	 * @param int $num_rows
	 */
	public function addNumberOfRows(string $report_name, int $num_rows) {
		$this->addSeparator($report_name, 'data_legend');
		$this->addLegend($report_name, i18n(null, \Object\Content\Messages::REPORT_ROWS_NUMBER, ['replace' => ['[Number]' => \Format::id($num_rows)]]));
		// we need to trigger form update
		if (!empty($this->form)) {
			$this->form->misc_settings['report']['num_rows'] = $num_rows;
		}
	}

	/**
	 * Add summary
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param array $header_column1
	 */
	public function addSummary(string $report_name, string $header_name, array $header_columns, array $options = []) {
		// process header
		$header = $this->data[$report_name]['header'][$header_name];
		$temp = [];
		foreach ($header as $k => $v) {
			if (array_key_exists($k, $header_columns)) {
				$temp[$v['__index']] = $header_columns[$k];
			}
		}
		// replace header
		$this->data[$report_name]['header_summary'][$header_name] = $temp;
		$this->data[$report_name]['header_summary_options'][$header_name] = $options;
	}

	/**
	 * Calculate summary
	 *
	 * @param string $report_name
	 * @throws \Exception
	 */
	public function calculateSummary(string $report_name) {
		$this->data[$report_name]['header_summary_calculated'] = [];
		foreach ($this->data[$report_name]['header_summary'] as $k => $v) {
			foreach ($v as $k2 => $v2) {
				if (empty($v2['function'])) {
					continue;
				}
				$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = null;
				$this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter'] = 0;
				$this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum'] = 0;
				$this->data[$report_name]['header_summary_calculated'][$k][$k2]['min'] = null;
				$this->data[$report_name]['header_summary_calculated'][$k][$k2]['max'] = null;
				foreach ($this->data[$report_name]['data'] as $row_number => $row_data) {
					if (($row_data[3] ?? '') != $k) {
						continue;
					}
					if ($v2['function'] != 'summary') {
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter']++;
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum']+= $row_data[6][$k2];
						if (!isset($this->data[$report_name]['header_summary_calculated'][$k][$k2]['min'])) {
							$this->data[$report_name]['header_summary_calculated'][$k][$k2]['min'] = $row_data[6][$k2];
						} else if ($this->data[$report_name]['header_summary_calculated'][$k][$k2]['min'] > $row_data[6][$k2]) {
							$this->data[$report_name]['header_summary_calculated'][$k][$k2]['min'] = $row_data[6][$k2];
						}
						if (!isset($this->data[$report_name]['header_summary_calculated'][$k][$k2]['max']) || $this->data[$report_name]['header_summary_calculated'][$k][$k2]['max'] < $row_data[6][$k2]) {
							$this->data[$report_name]['header_summary_calculated'][$k][$k2]['max'] = $row_data[6][$k2];
						}
					}
				}
				switch ($v2['function']) {
					case 'sum':
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = $this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum'];
						break;
					case 'avg':
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = $this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum'] / $this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter'];
						break;
					case 'min':
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = $this->data[$report_name]['header_summary_calculated'][$k][$k2]['min'];
						break;
					case 'max':
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = $this->data[$report_name]['header_summary_calculated'][$k][$k2]['max'];
						break;
					case 'summary':
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = i18n(null, 'Summary:');
						break;
					default:
						Throw new \Exception('Unknown function: ' . $v2['function']);
				}
				unset($this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter']);
				unset($this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum']);
				unset($this->data[$report_name]['header_summary_calculated'][$k][$k2]['min']);
				unset($this->data[$report_name]['header_summary_calculated'][$k][$k2]['max']);
			}
		}
	}
}