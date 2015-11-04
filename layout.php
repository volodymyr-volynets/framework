<?php

/**
 * Layout class
 * 
 * @author Volodymyr Volynets
 *
 */
class layout extends view {

	/**
	 * Title override
	 *
	 * @var string
	 */
	public static $title_override;

	/**
	 * Icon override
	 *
	 * @var string
	 */
	public static $icon_override;

	/**
	 * Version to be used when rendering js/css links
	 *
	 * @var int
	 */
	public static $version;

	/**
	 * Onload javascript
	 * 
	 * @var string
	 */
	private static $onload = '';

	/**
	 * Javascript data would be here
	 *
	 * @var array
	 */
	private static $js_data = [];

	/**
	 * Non HTML output
	 *
	 * @var boolean
	 */
	public static $non_html_output;

	/**
	 * Add css file to layout
	 * 
	 * @param string $css
	 * @param int $sort
	 */
	public static function add_css($css, $sort = 0) {
		application::set(array('layout', 'css', $css), $sort);
	}

	/**
	 * Render css files
	 *  
	 *  @return string
	 */
	public static function render_css() {
		$result = '';
		$css = application::get(array('layout', 'css'));
		if (!empty($css)) {
			asort($css);
			if (empty(self::$version)) {
				self::$version = filemtime('./../../deployed');
			}
			foreach ($css as $k=>$v) {
				$script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::$version;
				$result.= '<link href="' . $script . '" rel="stylesheet" type="text/css" />';
			}
		}
		return $result;
	}

	/**
	 * Add javascript file to the layout
	 * 
	 * @param string $js
	 * @param int $sort
	 */
	public static function add_js($js, $sort = 0) {
		$js = str_replace('\\', '/', $js);
		application::set(array('layout', 'js', $js), $sort);
	}

	/**
	 * Render javascript files 
	 * 
	 * @return string
	 */
	public static function render_js() {
		$result = '';
		$js = application::get(array('layout', 'js'));
		if (!empty($js)) {
			asort($js);
			// get deployment version
			if (empty(self::$version)) {
				self::$version = filemtime('./../../deployed');
			}
			foreach ($js as $k=>$v) {
				$script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::$version;
				$result.= '<script type="text/javascript" src="' . $script . '"></script>';
			}
		}
		return $result;
	}

	/**
	 * Onload js
	 * 
	 * @param script $js
	 */
	public static function onload($js) {
		self::$onload.= $js;
	}

	/**
	 * Render onload js
	 * 
	 * @return string
	 */
	public static function render_onload() {
		if (!empty(self::$onload)) {
			return html::script(array('value'=>'$(document).ready(function(){ ' . self::$onload . ' });'));
		}
	}

	/**
	 * Add array to js data
	 *
	 * @param array $data
	 */
	public static function js_data($data) {
		self::$js_data = array_merge2(self::$js_data, $data);
	}

	/**
	 * Render js data
	 */
	public static function render_js_data() {
		return html::script(['value' => 'var numbers_js_data = ' . json_encode(self::$js_data) . '; $.extend(numbers, numbers_js_data); numbers_js_data = null;']);
	}

	/**
	 * Render title
	 *
	 * @return string
	 */
	public static function render_title() {
		$result = '';
		$data = application::get(array('controller'));
		if (!empty(self::$title_override)) {
			$data['title'] = self::$title_override;
		}
		if (!empty(self::$icon_override)) {
			$data['icon'] = self::$icon_override;
		}
		if (!empty($data['title'])) {
			$data['icon'] = !empty($data['icon']) ? icon::render($data['icon']) : '';
			$result.= (!empty($data['icon']) ? ($data['icon'] . ' ') : '') . $data['title'];
		}
		return $result;
	}

	/**
	 * Render document title
	 * 
	 * @return string
	 */
	public static function render_document_title() {
		$title = strip_tags(self::render_title());
		return '<title>' . $title . '</title>';
	}

	/**
	 * Messages
	 * 
	 * @param string $msg
	 * @param string $type
	 */
	public static function add_message($msg, $type = 'error') {
		if (is_array($msg)) {
			foreach ($msg as $k=>$v) {
				application::set(array('messages', $type), $v, array('append'=>true));
			}
		} else {
			application::set(array('messages', $type), $msg, array('append'=>true));
		}
	}

	/**
	 * Render application messages
	 * 
	 * @return string
	 */
	public static function render_messages() {
		$result = '';
		$messages = application::get(array('messages'));
		if (!empty($messages)) {
			foreach ($messages as $k => $v) {
				$result.= html::message(['options' => $v, 'type' => $k]);
			}
		}
		return $result;
	}

	/**
	 * Add action
	 * 
	 * @param array $action
	 * @param string $code
	 */
	public static function add_action($code, $action) {
		application::set(array('layout', 'bar_action', $code), $action);
	}

	/**
	 * Render actions
	 * 
	 * @return string
	 */
	public static function render_actions() {
		$result = '';
		$data = application::get(array('layout', 'bar_action'));
		if (!empty($data)) {
			// sorting first
			array_key_sort($data, 'sort', SORT_ASC, 'intval');
			// looping through data and building html
			$temp = array();
			foreach ($data as $k=>$v) {
				$icon = !empty($v['icon']) ? $v['icon'] : '';
				$onclick = !empty($v['onclick']) ? $v['onclick'] : '';
				$value = !empty($v['value']) ? $v['value'] : '';
				$temp[] = html::a(array('value' => $icon . $value, 'href' => $v['href'], 'onclick' => $onclick));
			}
			$result = implode(' ', $temp);
		}
		return $result;
	}

	/**
	 * Render breadcrumbs
	 *
	 * @return string
	 */
	public static function render_breadcrumbs() {

	}

	/**
	 * Render json output
	 *
	 * @param mixed $data
	 */
	public static function render_as_json($data) {
		self::$non_html_output = true;
		echo json_encode($data);
		exit;
	}
}