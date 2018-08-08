<?php

class Debug {

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
	 * All variables will be stored in here
	 *
	 * @var array
	 */
	public static $data = [
		'errors' => [], // number of errors
		'suppressed' => [], // if we need to see suppressed errors
		'js' => [], // if we have javascript errors
		'sql' => [], // if we need to see queries
		'cache' => [], // if we need to see caches
		'dump' => [], // if we need to dump something
		'session' => [], // if we need to see session
		'input' => [], // if we need to see input
		'benchmark' => [], // if we need to know how long it takes
		'classes' => [], // autoloaded classes
		'application' => [], // variables set in application
		'phpinfo' => '', // phpinfo
		'acls' => [] // executed acls
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
	public static function getMicrotime() {
		return microtime(true);
	}

	/**
	 * Benchmark
	 *
	 * @staticvar type $start
	 * @staticvar int $total
	 * @param string $name
	 */
	public static function benchmark(string $name = '') {
		if (self::$debug) {
			static $start = NULL;
			static $total = 0;
			$benchmark = 0;
			if (is_null($start)) {
				$start = self::getMicrotime();
			} else {
				$temp = self::getMicrotime();
				$benchmark = $temp - $start;
				$start = $temp;
			}
			$total+= $benchmark;
			self::$data['benchmark'][] = [
				'name' => $name,
				'time' => \Format::timeSeconds($benchmark) . '',
				'total' => \Format::timeSeconds($total),
				'start' => \Format::datetime($start, ['skip_user_timezone' => true]),
				'memory' => memory_get_peak_usage(true)
			];
		}
	}

	/**
	 * Send errors to administrator
	 */
	public static function sendErrorsToAdmin() {
		// determine if we need to send anything
		$found = false;
		foreach (\Object\Error\Base::$errors as $k => $v) {
			if ($v['errno'] != -1) {
				$found = true;
				break;
			}
		}
		// we do not send suppresed errors to admin for now. !empty(self::$data['suppressed'])
		if ($found || !empty(self::$data['js'])) {
			$message = '<hr/>';
			$message.= '<br/>IP: ' . \Request::ip();
			$message.= '<br/>Host: ' . \Request::host();
			$message.= '<br/>Script folder: ' . getcwd();
			$message.= '<hr/>';
			$message.= str_replace('display: none;', '', self::render());
			return \Mail::send([
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
		$loaded_classes = \Application::get(['application', 'loaded_classes']);
		self::$data['session'] = [];
		if (!empty($_SESSION)) {
			self::$data['session'] = [$_SESSION];
		}
		$application = \Application::get();
		$result = '<div class="container" dir="ltr">';
			$result.= '<table cellpadding="2" cellspacing="2" width="100%">';
				$result.= '<tr>';
					$result.= '<td>';
						$result.= '<table width="100%" class="numbers_debug_links">';
							$result.= '<tr>';
								$result.= '<td nowrap>&nbsp;' . \HTML::a(['value' => 'Hide All', 'href' => 'javascript:void(0);', 'onclick' => "$('.debuging_toolbar_class').hide();"]) . '&nbsp;</td>';
								foreach (self::$data as $k => $v) {
									if ($k == 'errors') {
										$count = count(\Object\Error\Base::$errors);
									} else if ($k == 'classes') {
										$count = count($loaded_classes);
									} else if ($k == 'application') {
										$count = count($application);
									} else if ($k == 'phpinfo') {
										$count = 1;
									} else {
										$count = count($v);
									}
									$result.= '<td nowrap>&nbsp;' . \HTML::a(['value' => ucwords($k) . ' (' . $count . ')', 'id' => "debuging_toolbar_{$k}_a", 'href' => 'javascript:void(0);', 'onclick' => "$('#debuging_toolbar_{$k}').toggle();"]) . '&nbsp;</td>';
								}
								$result.= '<td width="50%" align="right">' . \HTML::a(['href' => '/Numbers/Backend/System/Modules/Controller/DevPortal', 'value' => 'Dev. Portal']) . '</td>';
							$result.= '</tr>';
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';
				
				// errors
				$result.= '<tr id="debuging_toolbar_errors" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Errors (' . count(\Object\Error\Base::$errors) . ')</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
							foreach (\Object\Error\Base::$errors as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . \Object\Error\Base::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</b></td>';
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
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
							foreach (self::$data['suppressed'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . \Object\Error\Base::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</b></td>';
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

				// javascript
				$result.= '<tr id="debuging_toolbar_js" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Javascript Errors (' . count(self::$data['js']) . ')</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
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

				// benchmark
				$result.= '<tr id="debuging_toolbar_benchmark" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Benchmark (' . count(self::$data['benchmark']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
							$result.= '<tr>';
								$result.= '<th>Name</th>';
								$result.= '<th>Time</th>';
								$result.= '<th>Start</th>';
								$result.= '<th>Total</th>';
								$result.= '<th>Memory</th>';
							$result.= '</tr>';
							foreach (self::$data['benchmark'] as $k => $v) {
								$result.= '<tr>';
									$result.= '<td>' . $v['name'] . '</td>';
									$result.= '<td align="right">' . $v['time'] . '</td>';
									$result.= '<td align="right">' . $v['start'] . '</td>';
									$result.= '<td align="right">' . $v['total'] . '</td>';
									$result.= '<td align="right">' . Format::memory($v['memory'], 'm') . '</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// sql
				$result.= '<tr id="debuging_toolbar_sql" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Sql (' . count(self::$data['sql']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
							foreach (self::$data['sql'] as $k => $v) {
								$temp = is_array($v['key']) ? implode('<br/>', $v['key']) : $v['key'];
								// header first
								$result.= '<tr>';
									$result.= '<th colspan="4" style="color: red;">Sql</th>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td valign="top" colspan="4"><pre style="width: 1130px;">' . $v['sql'] . '</pre></td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<th>Error</th>';
									$result.= '<th>Errno</th>';
									$result.= '<th>Num Rows</th>';
									$result.= '<th>Affected Rows</th>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td valign="top">' . implode('<br/>', $v['error']) . ' - ' . implode('<br/>', $v['error_original'] ?? []) . '</td>';
									$result.= '<td valign="top">' . $v['errno'] . '</td>';
									$result.= '<td valign="top">' . $v['num_rows'] . '</td>';
									$result.= '<td valign="top">' . $v['affected_rows'] . '</td>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<th>Key</th>';
									$result.= '<th>Structure</th>';
									$result.= '<th>Cache</th>';
									$result.= '<th>Time</th>';
								$result.= '</tr>';
								$result.= '<tr>';
									$result.= '<td valign="top">' . $temp . '</td>';
									$result.= '<td valign="top">' . \HTML::table(['options' => $v['structure']]) . '</td>';
									$result.= '<td valign="top">' . (!empty($v['cache']) ? 'Yes' : 'No') . '</td>';
									$result.= '<td valign="top">' . $v['time'] . '</td>';
								$result.= '</tr>';
								// cache tags
								if (!empty($v['cache_tags'])) {
									$result.= '<tr>';
										$result.= '<th>Cache Tags</th>';
										$result.= '<td valign="top" colspan="7" style="max-width: 1000px; overflow: scroll;">' . nl2br(implode("\n", $v['cache_tags']), true) . '</td>';
									$result.= '</tr>';
								}
								// results second
								if (!empty($v['rows'])) {
									$temp2 = current($v['rows']);
									if (!is_array($temp2)) $temp2 = $v['rows'];
									$temp = array_keys($temp2);
									$header = array_combine($temp, $temp);
									if (!empty($header)) {
										$result.= '<tr>';
											$result.= '<td valign="top" colspan="8" style="max-width: 1000px; overflow: scroll;">' . \HTML::table(['header' => $header, 'options' => $v['rows']]) . '</td>';
										$result.= '</tr>';
									}
								}
								// backtrace
								if (!empty($v['backtrace'])) {
									$result.= '<tr>';
										$result.= '<td valign="top" colspan="4"><pre style="width: 1130px;">' . $v['backtrace'] . '</pre></td>';
									$result.= '</tr>';
								}
								// empty separator
								$result.= '<tr>';
									$result.= '<td valign="top" colspan="4">&nbsp;</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// cache
				$result.= '<tr id="debuging_toolbar_cache" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Cache (' . count(self::$data['cache']) . ')' . '</h3>';
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
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
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
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
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
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
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
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
						$result.= '<table border="1" cellpadding="2" cellspacing="2" width="100%">';
							$result.= '<tr>';
								$result.= '<th>Class Name</th>';
								$result.= '<th>File</th>';
								$result.= '<th>Media</th>';
							$result.= '</tr>';
							foreach ($loaded_classes as $k => $v) {
								$result.= '<tr>';
									$result.= '<td><b>' . $v['class'] . '</b></td>';
									$result.= '<td>' . $v['file'] . '</td>';
									$result.= '<td>' . \HTML::table(['options' => $v['media']]) . '</td>';
								$result.= '</tr>';
							}
						$result.= '</table>';
					$result.= '</td>';
				$result.= '</tr>';

				// application
				$result.= '<tr id="debuging_toolbar_application" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Application (' . count($application) . ')</h3>';
						$result.= print_r2($application, 'Application Variables:', true);
					$result.= '</td>';
				$result.= '</tr>';

				// phpinfo
				$result.= '<tr id="debuging_toolbar_phpinfo" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>PHPInfo</h3>';
						\Helper\Ob::start();
						phpinfo();
						$str = \Helper\Ob::clean();
						$str = preg_replace( '%^.*<body>(.*)</body>.*$%ms', '$1', $str);
						$str.= <<<TTT
							<style type="text/css">
								#phpinfo table {
									border: 1px solid #000;
								}
								#phpinfo table tr {
									border-bottom: 1px solid #000;
								}
							</style>
TTT;
						$result.= '<div id="phpinfo">' . $str . '</div>';
					$result.= '</td>';
				$result.= '</tr>';

				// acls
				$result.= '<tr id="debuging_toolbar_acls" class="debuging_toolbar_class" style="display: none;">';
					$result.= '<td>';
						$result.= '<h3>Acls (' . count(\Debug::$data['acls']) . ')</h3>';
						$result.= print_r2(\Debug::$data['acls'], 'Acls', true);
					$result.= '</td>';
				$result.= '</tr>';

			$result.= '</table>';
		$result.= '</div>';
		return $result;
	}
}