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
	 * Other
	 *
	 * @var array
	 */
	public $other = [];

	/**
	 * Subtotals
	 *
	 * @var array
	 */
	public $subtotals = [];

	/**
	 * Construct
	 *
	 * @param array $options
	 */
	public function __construct(array $options = []) {
		$this->options = $options;
		if (!empty($options['in_group_id'])) {
			\I18n::init([
				'group_id' => $options['in_group_id'],
				'skip_user_settings' => true
			]);
		}
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
	public function addReport(string $report_name, $form = null, array $options = []) {
		$this->data[$report_name] = [
			'name' => $report_name,
			'options' => $options,
			'header' => [],
			'header_options' => [],
			'filter' => [],
			'data' => [],
			'form_name' => '',
		];
		// type
		$this->data[$report_name]['options']['type'] = $this->data[$report_name]['options']['type'] ?? 'list';
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
		// add to others
		$this->other[] = [
			'type' => 'list',
			'report_name' => $report_name,
		];
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
		$temp = $temp2 = [];
		foreach ($header as $k => $v) {
			if (array_key_exists($k, $header_columns)) {
				$temp[$v['__index']] = $header_columns[$k];
				$temp2[$k] = $v['__index'];
			}
		}
		// replace header
		$this->data[$report_name]['header_summary'][$header_name] = $temp;
		$this->data[$report_name]['header_summary2'][$header_name] = $temp2;
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
					if ($v2['function'] == 'avg_not_null') {
						if (!empty($row_data[6][$k2])) {
							$this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter']++;
							$this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum']+= $row_data[6][$k2];
							goto calc_other_fields;
						}
					} else if ($v2['function'] != 'summary') {
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter']++;
						$this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum']+= $row_data[6][$k2];
calc_other_fields:
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
					case 'avg_not_null':
						if (!empty($this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter'])) {
							$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = $this->data[$report_name]['header_summary_calculated'][$k][$k2]['sum'] / $this->data[$report_name]['header_summary_calculated'][$k][$k2]['counter'];
						} else {
							$this->data[$report_name]['header_summary_calculated'][$k][$k2]['final'] = 0;
						}
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
			// custom calculator
			if (!empty($this->data[$report_name]['header_summary_options'][$k]['custom_calculator'])) {
				$method = explode('::', $this->data[$report_name]['header_summary_options'][$k]['custom_calculator']);
				call_user_func_array($method, [$k, & $this->data[$report_name]['header_summary2'][$k], & $this->data[$report_name]['header_summary_calculated'][$k]]);
			}
		}
	}

	/**
	 * Add subtotal
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param array $keys
	 * @param array $values
	 */
	public function addSubtotalData(string $report_name, string $header_name, array $keys, array $values) {
		$key_original = $keys;
		// prepend report name and header name to keys
		array_unshift($keys, $header_name);
		array_unshift($keys, $report_name);
		// add values
		foreach ($values as $k => $v) {
			// set key as well
			foreach ($key_original as $k2 => $v2) {
				$keys2 = $keys;
				if (is_numeric($k2)) {
					$keys2[]= 'key' . $k2;
				} else {
					$keys2[]= $k2;
				}
				array_key_set($this->subtotals, $keys2, $v2);
			}
			// set value
			$keys2 = $keys;
			$keys2[]= $k;
			$current = array_key_get($this->subtotals, $keys2) ?? '0';
			$current = \Math::add($current, $v);
			array_key_set($this->subtotals, $keys2, $current);
		}
	}

	/**
	 * Get subtotal
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param array $keys
	 * @return array
	 */
	public function getSubtotalData(string $report_name, string $header_name, array $keys = []) : array {
		// prepend report name and header name to keys
		array_unshift($keys, $header_name);
		array_unshift($keys, $report_name);
		return array_key_get($this->subtotals, $keys) ?? [];
	}

	/**
	 * Render subtotal
	 *
	 * @param string $report_name
	 * @param string $header_name
	 * @param array $keys
	 * @param array $options
	 *	array header - list of column styles
	 *	int even
	 */
	public function renderSubtotalData(string $report_name, string $header_name, array $keys = [], array $options = []) {
		$subtotal = $this->getSubtotalData($report_name, $header_name, $keys);
		if (empty($subtotal)) return;
		// render header
		$options['even'] = $options['even'] ?? 1;
		$subtotal_counter = 1;
		$header = $this->getHeaderForRender($report_name, $header_name);
		foreach ($options['header'] as $k => $v) {
			$options['header'][$k]['value'] = $v['label_name'] ?? $header[$k]['label_name'];
		}
		$this->addData($report_name, $header_name, $options['even'], $options['header'], [
			'cell_even' => $subtotal_counter % 2 ? ODD : EVEN
		]);
		$subtotal_counter++;
		foreach ($subtotal as $k4 => $v4) {
			$temp = [];
			foreach ($header as $k5 => $v5) {
				if (!isset($v4[$k5])) {
					if (!empty($v5['zero_out'])) {
						$v4[$k5] = '0';
					} else {
						$v4[$k5] = null;
					}
				}
				if (empty($v5['format'])) {
					$temp[$k5] = [
						'value' => $v4[$k5],
						'bold' => true,
					];
				} else {
					$v5['format_options'] = $v5['format_options'] ?? [];
					if (!empty($v5['format_depends'])) {
						foreach ($v5['format_depends'] as $k6 => $v6) {
							$v5['format_options'][$k6] = $v4[$v6] ?? null;
						}
					}
					$v5['format_options']['fs'] = $v5['fs'] ?? false;
					$method = \Factory::method($v5['format'], 'Format');
					$temp[$k5] = [
						'value' => call_user_func_array([$method[0], $method[1]], [$v4[$k5], $v5['format_options']]) ?? '',
						'value_export' => $v4[$k5],
						'alarm' => \Math::isLess($v4[$k5]),
						'bold' => true,
					];
				}
			}
			$this->addData($report_name, $header_name, $options['even'], $temp, [
				'cell_even' => $subtotal_counter % 2 ? ODD : EVEN
			]);
			$subtotal_counter++;
		}
	}

	/**
	 * Add image
	 *
	 * @param float $x
	 * @param float $y
	 * @param int $file_id
	 */
	public function addImage(float $x, float $y, float $w, float $h, int $file_id, array $options = []) {
		$this->other[] = [
			'type' => 'image',
			'x' => $x,
			'y' => $y,
			'w' => $w,
			'h' => $h,
			'file_id' => $file_id,
			'options' => $options
		];
	}

	/**
	 * Add text
	 *
	 * @param float|string $x
	 * @param float|string $y
	 * @param float|string $w
	 * @param float|string $h
	 * @param type $text
	 * @param array $options
	 */
	public function addText($x, $y, $w, $h, $text, array $options = []) {
		$this->other[] = [
			'type' => 'text',
			'x' => $x,
			'y' => $y,
			'w' => $w,
			'h' => $h,
			'text' => $text,
			'options' => $options
		];
	}

	/**
	 * Set XY
	 *
	 * @param type $x
	 * @param type $y
	 */
	public function setXY($x, $y) {
		$this->other[] = [
			'type' => 'setxy',
			'x' => $x,
			'y' => $y,
		];
	}
}