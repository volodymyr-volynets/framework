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
	 * Onload JavaScript
	 * 
	 * @var string
	 */
	public static $onload = '';

	/**
	 * JavaScript data would be here
	 *
	 * @var array
	 */
	private static $js_data = [];

	/**
	 * HTML to be added last to the page
	 *
	 * @var string
	 */
	public static $onhtml = '';

	/**
	 * Get application version
	 *
	 * @return int
	 */
	public static function get_version() {
		if (empty(self::$version)) {
			$filename = application::get(['application', 'path_full']) . (application::is_deployed() ? '../../../deployed' : '../../deployed');
			self::$version = filemtime($filename);
		}
		return self::$version;
	}

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
			foreach ($css as $k=>$v) {
				$script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::get_version();
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
			foreach ($js as $k=>$v) {
				$script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::get_version();
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
	 * OnHTML
	 *
	 * @param string $html
	 */
	public static function onhtml($html) {
		self::$onhtml.= $html;
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
	 * Add array to JavaScript data
	 *
	 * @param array $data
	 */
	public static function js_data($data) {
		self::$js_data = array_merge2(self::$js_data, $data);
	}

	/**
	 * Render JavaScript data
	 */
	public static function render_js_data() {
		return html::script(['value' => 'var numbers_js_data = ' . json_encode(self::$js_data) . '; $.extend(true, numbers, numbers_js_data); numbers_js_data = null;']);
	}

	/**
	 * Render title
	 *
	 * @return string
	 */
	public static function render_title() {
		$result = '';
		$data = application::get('controller');
		if (!empty(self::$title_override)) {
			$data['title'] = self::$title_override;
		}
		if (!empty(self::$icon_override)) {
			$data['icon'] = self::$icon_override;
		}
		if (!empty($data['title'])) {
			$result.= (!empty($data['icon']) ? (html::icon(['type' => $data['icon']]) . ' ') : '') . i18n(null, $data['title']);
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
	 *		type one of:
	 *			danger
	 *			warning
	 *			success
	 *			info
	 *			other
	 * @param boolean $postponed
	 */
	public static function add_message($msg, $type = 'danger', $postponed = false) {
		if (!$postponed) {
			if (is_array($msg)) {
				foreach ($msg as $k=>$v) {
					application::set(['messages', $type], $v, ['append'=>true]);
				}
			} else {
				application::set(['messages', $type], $msg, ['append'=>true]);
			}
		} else { // postponed messages go into session
			if (is_array($msg)) {
				foreach ($msg as $k=>$v) {
					session::set(['numbers', 'messages', $type], $v, ['append'=>true]);
				}
			} else {
				session::set(['numbers', 'messages', $type], $msg, ['append'=>true]);
			}
		}
	}

	/**
	 * Render application messages
	 * 
	 * @return string
	 */
	public static function render_messages() {
		$result = '';
		// we need to see if we have postponed messages and render them first
		$postponed = session::get(['numbers', 'messages']);
		if (!empty($postponed)) {
			session::set(['numbers', 'messages'], []);
			foreach ($postponed as $k => $v) {
				$result.= html::message(['options' => $v, 'type' => $k]);
			}
		}
		// regular messages
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
		$action['orderby'] = $action['orderby'] ?? 0;
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
			array_key_sort($data, ['orderby' => SORT_ASC], ['orderby' => SORT_NUMERIC]);
			// looping through data and building html
			$temp = array();
			foreach ($data as $k=>$v) {
				$icon = !empty($v['icon']) ? (html::icon(['type' => $v['icon']]) . ' ') : '';
				$onclick = !empty($v['onclick']) ? $v['onclick'] : '';
				$value = !empty($v['value']) ? $v['value'] : '';
				$href = $v['href'] ?? 'javascript:void(0);';
				$temp[] = html::a(array('value' => $icon . $value, 'href' => $href, 'onclick' => $onclick));
			}
			$result = implode(' ', $temp);
		}
		return $result;
	}

	/**
	 * Render bread crumbs
	 *
	 * @return string
	 */
	public static function render_breadcrumbs() {
		$data = application::get(['controller', 'breadcrumbs']);
		if ($data) {
			return html::breadcrumbs($data);
		}
	}

	/**
	 * Render as content type, non html output should go though this function
	 *
	 * @param mixed $data
	 * @param string $content_type
	 */
	public static function render_as($data, $content_type) {
		// clena up output buffer
		helper_ob::clean_all();
		application::set('flag.global.__content_type', $content_type);
		application::set('flag.global.__skip_layout', 1);
		header("Content-type: " . $content_type);
		switch ($content_type) {
			case 'application/json':
				echo json_encode($data);
				break;
			case 'text/html':
				helper_ob::start();
				require(application::get(['application', 'path_full']) . 'layout/blank.html');
				$from = [
					'<!-- [numbers: document title] -->',
					'<!-- [numbers: document body] -->'
				];
				$to = [
					layout::render_document_title(),
					$data
				];
				echo str_replace($from, $to, helper_ob::clean());
				break;
			default:
				echo $data;
		}
		exit;
	}

	/**
	 * Include all media files for controller
	 *
	 * @param string $path
	 * @param string $controller
	 * @param string $view
	 * @param string $class
	 */
	public static function include_media($path, $controller, $view, $class) {
		// generating a list of extensions
		$valid_extensions = ['js', 'css'];
		if (application::get('dep.submodule.numbers.frontend.media.scss')) {
			$valid_extensions[] = 'scss';
		}
		// we need to fix path for submodules
		$path_fixed = str_replace('/', '_', $path);
		$path_js = str_replace('_' . $controller, '', $class) . '_';
		if (substr($path, 0, 8) == 'numbers/') {
			$path = '../libraries/vendor/' . $path;
		}
		//$path = application::get(['application', 'path_full']) . $path;
		// build an iterator
		$iterator = new FilesystemIterator($path);
		$filter = new RegexIterator($iterator, '/' . $controller . '(.' . $view . ')?.(' . implode('|', $valid_extensions) . ')$/');
		$file_list = [];
		// iterating
		foreach($filter as $v) {
			$temp = $v->getFilename();
			$extension = pathinfo($temp, PATHINFO_EXTENSION);
			// we need to sort in a way that view files are included second
			if ($controller . '.' . $extension == $temp) {
				$sort = 1000;
			} else {
				$sort = 2000;
			}
			$new = '/numbers/media_generated/application_' . $path_js . $temp;
			if ($extension == 'js') {
				self::add_js($new, $sort);
			} else if ($extension == 'css') {
				self::add_css($new, $sort);
			} else if ($extension == 'scss') {
				$new.= '.css';
				self::add_css($new, $sort);
			}
			// adding media files to application for further reporting
			application::set(['application', 'loaded_classes', $class, 'media'], ['file' => $temp, 'full' => $new], ['append' => true]);
		}
	}
}