<?php

class debug {

	/**
	 * Whether or not we need to debug our application
	 *
	 * @var boolean
	 */
	public static $debug = false;

	/**
	 * Email to administrator
	 *
	 * @var boolean
	 */
	public static $email;

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
		'classes' => [], // autoloaded classes
		'errors' => [], // number of errors
		'suppressed' => [], // if we need to see suppressed errors
		'js' => [], // if we have javascript errors
	];

	/**
	 * Initialize debug
	 *
	 * @param array $options
	 */
	public static function init($options) {
		self::$debug = !empty($options['debug']) ? true : false;
		if (self::$debug) {
			self::$email = !empty($options['email']) ? $options['email'] : null;
			self::$toolbar = !empty($options['toolbar']) ? true : false;
			self::benchmark('application start');
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
		return microtime(true);
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
				$temp = self::get_microtime();
				$benchmark = $temp - $start;
				$start = $temp;
			}
			$total+= $benchmark;
			self::$data['benchmark'][] = array('name' => $name, 'time' => format::time_seconds($benchmark) . '', 'total' => format::time_seconds($total), 'start' => format::datetime($start));
		}
	}

	/**
	 * Send errors to admin
	 */
	public static function send_errors_to_admin() {
		// determine if we need to send anything
		if (!empty(error_base::$errors) || !empty(self::$data['suppressed']) || !empty(self::$data['js'])) {
			$message = str_replace('display: none;', '', self::render());
			return mail::send([
				'to' => self::$email,
				'subject' => 'application error',
				'message' => $message
			]);
		}
	}

	/**
	 * Render debug toolbar
	 *
	 * @return string
	 */
	public static function render() {
		$loaded_classes = application::get(['application', 'loaded_classes']);
		self::$data['session'] = [$_SESSION];
		$result = '';
		$result.= '<div class="container">';
			$result.= '<table cellpadding="2" cellspacing="2" width="100%">';
				$result.= '<tr>';
					$result.= '<td>';
						$result.= '<table width="100%">';
							$result.= '<tr>';
								$result.= '<td nowrap>&nbsp;' . html::a(['value' => 'Hide All', 'href' => 'javascript:void(0);', 'onclick' => "$('.debuging_toolbar_class').hide();"]) . '&nbsp;</td>';
								foreach (self::$data as $k => $v) {
									if ($k == 'errors') {
										$count = count(error_base::$errors);
									} else if ($k == 'classes') {
										$count = count($loaded_classes);
									} else {
										$count = count($v);
									}
									$result.= '<td nowrap>&nbsp;' . html::a(['value' => ucwords($k) . ' (' . $count . ')', 'id' => "debuging_toolbar_{$k}_a", 'href' => 'javascript:void(0);', 'onclick' => "$('#debuging_toolbar_{$k}').toggle();"]) . '&nbsp;</td>';
								}
								$result.= '<td width="50%" align="right">' . html::a(['href' => '/numbers/framework/controller/dev', 'value' => 'Dev. Portal']) . '</td>';
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
									$result.= '<td valign="top">' . html::table(['options' => $v['rows']]) . '</td>';
									$result.= '<td valign="top">' . $temp . '</td>';
									$result.= '<td valign="top">' . html::table(['options' => $v['structure']]) . '</td>';
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

				// autoloaded classes
				$result.= '<tr id="debuging_toolbar_classes" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Loaded Classes (' . count($loaded_classes) . ')</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							$result.= '<tr>';
								$result.= '<th>Class Name</th>';
								$result.= '<th>File</th>';
								$result.= '<th>Media</th>';
							$result.= '</tr>';
							foreach ($loaded_classes as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . $v['class'] . '</b></td>';
									$result.= '<td>' . $v['file'] . '</td>';
									$result.= '<td>' . html::table(['options' => $v['media']]) . '</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// errors
				$result.= '<tr id="debuging_toolbar_errors" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Errors (' . count(error_base::$errors) . ')</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							foreach (error_base::$errors as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . error_base::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</b></td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td>File: ' . $v['file'] . ', Line: ' . $v['line'] . '</td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td><pre>' . $v['code'] . '</pre></td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td><pre>' . implode("\n", $v['backtrace']) . '</pre></td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// suppressed
				$result.= '<tr id="debuging_toolbar_suppressed" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Suppressed (' . count(self::$data['suppressed']) . ')</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							foreach (self::$data['suppressed'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . error_base::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</b></td>';
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

				$result.= '<tr id="debuging_toolbar_js" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Javascript Errors (' . count(self::$data['js']) . ')</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2">';
							foreach (self::$data['js'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . implode('<br/>', $v['error']) . '</b></td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td>File: ' . $v['file'] . ', Line: ' . $v['line'] . '</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
						$result.= '<div id="debuging_toolbar_js_data">';
							$result.= '&nbsp;';
						$result.= '</div>';
					$result.= '</td>';
				$result.= '</tr>';

			$result.= '<table>';
		$result.= '</div>';
		return $result;
	}
}