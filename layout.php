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