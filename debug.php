<?php

class debug {

	/**
	 * Whether or not we need to debug our application
	 * 
	 * @var boolean 
	 */
	public static $debug = false;

	/**
	 * Whether or not we need to email administrator
	 *
	 * @var boolean 
	 */
	public static $email = false;

	/**
	 * Whether or not we show toolbar
	 *
	 * @var boolean 
	 */
	public static $toolbar = false;

	/**
	 * All variables will be sotred in here
	 * 
	 * @var array 
	 */
	public static $data = [
		'sql' => [], // if we need to see queries
		'cache' => [], // if we need to see caches
		'dump' => [], // if we need to dump something
		'session' => [], // if we need to see session
		'input' => [], // if we need to see input
		'benchmark' => [], // if we need to know how long it takes
		'errors' => [], // number of errors
		'suppressed' => [], // if we need to see suppressed errors
	];

	/**
	 * Initialize debug
	 *
	 * @param array $options
	 */
	public static function init($options) {
		self::$debug = !empty($options['debug']) ? true : false;
		if (self::$debug) {
			self::$email = !empty($options['email']) ? true : false;
			self::$toolbar = !empty($options['toolbar']) ? true : false;
		}
	}

	/**
	 * Dump value
	 *
	 * @param mixed $value
	 * @param string $name
	 */
	public static function dump($value, $name = '') {
		if (self::$debug) {
			self::$data['dump'][] = array(
				'name' => $name,
				'value' => $value,
			);
		}
	}

	/**
	 * Get microtime
	 *
	 * @return float
	 */
	public static function get_microtime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}

	/**
	 * Benchmark
	 *
	 * @staticvar type $start
	 * @staticvar int $total
	 * @param string $name
	 */
	public static function benchmark($name = '') {
		if (self::$debug) {
			static $start = NULL;
			static $total = 0;
			$benchmark = 0;
			if (is_null($start)) {
				$start = self::get_microtime();
			} else {
				$benchmark = self::get_microtime() - $start;
				$start = self::get_microtime();
			}
			$total+= $benchmark;
			self::$data['benchmark'][] = array('name' => $name, 'time' => format::time($benchmark, true) . '', 'total' => format::time($total, true), 'start' => format::datetime($start));
		}
	}

	/**
	 * Render debug toolbar
	 *
	 * @return string
	 */
	public static function render() {
		if (!self::$toolbar) {
			return '';
		}
		$result = '';
		$result.= '<div class="container">';
			$result.= '<table cellpadding="2" cellspacing="2">';
				$result.= '<tr>';
					$result.= '<td>';
						$result.= '<table>';
							$result.= '<tr>';
								$result.= '<td>&nbsp;' . h::a(array('value' => 'Hide All', 'href' => 'javascript:void(0);', 'onclick' => "$('.debuging_toolbar_class').hide();")) . '&nbsp;</td>';
								foreach (self::$data as $k => $v) {
									if ($k == 'errors') {
										$v = error::$errors;
									}
									$result.= '<td>&nbsp;' . h::a(array('value' => ucwords($k) . ' (' . count($v) . ')', 'href' => 'javascript:void(0);', 'onclick' => "$('#debuging_toolbar_{$k}').toggle();")) . '&nbsp;</td>';
								}
							$result.= '</tr>';
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// benchmark first
				$result.= '<tr id="debuging_toolbar_benchmark" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Benchmark (' . count(self::$data['benchmark']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							$result.= '<tr>';
								$result.= '<th>Name</th>';
								$result.= '<th>Time</th>';
								$result.= '<th>Start</th>';
								$result.= '<th>Total</th>';
							$result.= '</tr>';
							foreach (self::$data['benchmark'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td>' . $v['name'] . '</td>';
									$result.= '<td align="right">' . $v['time'] . '</td>';
									$result.= '<td align="right">' . $v['start'] . '</td>';
									$result.= '<td align="right">' . $v['total'] . '</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// sql
				$result.= '<tr id="debuging_toolbar_sql" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Sql (' . count(self::$data['sql']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							$result.= '<tr>';
								$result.= '<th>Sql</th>';
								$result.= '<th>Error</th>';
								$result.= '<th>Errno</th>';
								$result.= '<th>Num Rows</th>';
								$result.= '<th>Affected Rows</th>';
								$result.= '<th>Rows</th>';
								$result.= '<th>Key</th>';
								$result.= '<th>Structure</th>';
								$result.= '<th>Time</th>';
							$result.= '</tr>';
							foreach (self::$data['sql'] as $k => $v) {
								$temp = is_array($v['key']) ? implode('<br/>', $v['key']) : $v['key'];
								$result.= '<tr>';
									$result.= '<td valign="top"><pre>' . nl2br($v['sql']) . '</pre></td>';
									$result.= '<td valign="top">' . implode('<br/>', $v['error']) . '</td>';
									$result.= '<td valign="top">' . $v['errno'] . '</td>';
									$result.= '<td valign="top">' . $v['num_rows'] . '</td>';
									$result.= '<td valign="top">' . $v['affected_rows'] . '</td>';
									$result.= '<td valign="top">' . h::array2table($v['rows'], $v['key']) . '</td>';
									$result.= '<td valign="top">' . $temp . '</td>';
									$result.= '<td valign="top">' . h::array2table($v['structure'], '') . '</td>';
									$result.= '<td valign="top">' . $v['time'] . '</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// cache
				$result.= '<tr id="debuging_toolbar_cache" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Cache (' . count(self::$data['cache']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							$result.= '<tr>';
								$result.= '<th>Type</th>';
								$result.= '<th>Link</th>';
								$result.= '<th>Cache #</th>';
								$result.= '<th>Has Data</th>';
							$result.= '</tr>';
							foreach (self::$data['cache'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td valign="top">' . $v['type'] . '</td>';
									$result.= '<td valign="top">' . $v['link'] . '</td>';
									$result.= '<td valign="top">' . $v['cache_id'] . '</td>';
									$result.= '<td valign="top">' . ($v['have_data'] ? 'Yes' : 'No') . '</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// dump
				$result.= '<tr id="debuging_toolbar_dump" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Dump (' . count(self::$data['dump']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							$result.= '<tr>';
								$result.= '<th>Name</th>';
								$result.= '<th>Dump</th>';
							$result.= '</tr>';
							foreach (self::$data['dump'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td valign="top">' . $v['name'] . '</td>';
									$result.= '<td valign="top"><pre>' . print_r($v['value'], true) . '</pre></td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// input
				$result.= '<tr id="debuging_toolbar_input" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Input (' . count(self::$data['input']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							$result.= '<tr>';
								$result.= '<th>Input</th>';
							$result.= '</tr>';
							foreach (self::$data['input'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td valign="top"><pre>' . print_r($v, true) . '</pre></td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// session
				$result.= '<tr id="debuging_toolbar_session" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Session (' . count(self::$data['session']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							$result.= '<tr>';
								$result.= '<th>Session</th>';
							$result.= '</tr>';
							foreach (self::$data['session'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td valign="top"><pre>' . print_r($v, true) . '</pre></td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// errors
				$result.= '<tr id="debuging_toolbar_errors" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Errors (' . count(error::$errors) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							foreach (error::$errors as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . error::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</b></td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td>File: ' . $v['file'] . ', Line: ' . $v['line'] . '</td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td><pre>' . $v['code'] . '</pre></td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// suppressed
				$result.= '<tr id="debuging_toolbar_suppressed" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Errors (' . count(self::$data['suppressed']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							foreach (self::$data['suppressed'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . error::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</b></td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td>File: ' . $v['file'] . ', Line: ' . $v['line'] . '</td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td><pre>' . $v['code'] . '</pre></td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

			$result.= '<table>';
		$result.= '</div>';
		return $result;
	}
}