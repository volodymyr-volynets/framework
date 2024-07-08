<?php

#[\AllowDynamicProperties]
class Layout extends View {

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
	 * Version to be used when rendering JS/CSS links
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
	public static $onload_first = '';

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
	public static function getVersion() {
		if (empty(self::$version)) {
			$filename = Application::get(['application', 'path_full']) . (Application::isDeployed() ? '../../../deployed' : '../../deployed');
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
	public static function addCss(string $css, int $sort = 0) {
		Application::set(array('layout', 'css', $css), $sort);
	}

	/**
	 * Render css files
	 *
	 * @param array $options
	 *	boolean return_list
	 * @return string
	 */
	public static function renderCss($options = []) {
		$result = '';
		$list = [];
		$css = Application::get(array('layout', 'css'));
		if (!empty($css)) {
			asort($css);
			foreach ($css as $k=>$v) {
				$script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::getVersion();
				$list[] = $script;
				$result.= '<link href="' . $script . '" rel="stylesheet" type="text/css" />';
			}
		}
		// list is needed for ajax form reloads
		if (!empty($options['return_list'])) {
			return $list;
		}
		return $result;
	}

	/**
	 * Add JavaScript file to the layout
	 *
	 * @param string $js
	 * @param int $sort
	 */
	public static function addJs(string $js, int $sort = 0, array $options = []) {
		$js = str_replace('\\', '/', $js);
		Application::set(['layout', 'js', $js], $sort);
		if (!empty($options)) {
			Application::set(['layout', 'js_options', $js], $options);
		}
	}

	/**
	 * Render javascript files 
	 *
	 * @param array $options
	 *	boolean return_list
	 * @return string
	 */
	public static function renderJs($options = []) {
		$result = '';
		$list = [];
		$js = Application::get(['layout', 'js']);
		if (!empty($js)) {
			asort($js);
			foreach ($js as $k => $v) {
				$script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::getVersion();
				$list[] = $script;
				$js_options = Application::get(['layout', 'js_options', $k]) ?? [];
				if (empty($js_options)) {
					$js_options['crossorigin'] = 'anonymous';
				}
				$js_options['type'] = 'text/javascript';
				$js_options['src'] = $script;
				$result.= \HTML::script($js_options);
			}
		}
		// list is needed for ajax form reloads
		if (!empty($options['return_list'])) {
			return $list;
		}
		return $result;
	}

	/**
	 * Onload js
	 * 
	 * @param string $js
	 * @param boolean $first
	 */
	public static function onLoad(string $js, bool $first = false) {
		if (!$first) {
			self::$onload.= $js;
		} else {
			self::$onload_first.= $js;
		}
	}

	/**
	 * OnHTML
	 *
	 * @param string $html
	 */
	public static function onHtml($html) {
		self::$onhtml.= $html;
	}

	/**
	 * Render onload js
	 * 
	 * @param boolean $first
	 * @return string
	 */
	public static function renderOnLoad() {
		if (!empty(self::$onload) || !empty(self::$onload_first)) {
			return \HTML::script(['value'=>'$(document).ready(function(){ ' . self::$onload_first . self::$onload . ' });']);
		}
	}

	/**
	 * Add array to JavaScript data
	 *
	 * @param array $data
	 */
	public static function jsData($data) {
		self::$js_data = array_merge_hard(self::$js_data, $data);
	}

	/**
	 * Render JavaScript data
	 */
	public static function renderJsData() {
		return \HTML::script(['value' => '$(document).ready(function(){ var numbers_js_data = ' . json_encode(self::$js_data) . '; $.extend(true, Numbers, numbers_js_data); numbers_js_data = null; });']);
	}

	/**
	 * Render title
	 *
	 * @return string
	 */
	public static function renderTitle() {
		$title = self::$title_override ?? i18n(null, \Application::$controller->title) ?? null;
		if (!empty($title)) {
			$icon = self::$icon_override ?? \Application::$controller->icon ?? null;
			return (!empty($icon) ? (\HTML::icon(['type' => $icon]) . ' ') : '') . $title;
		}
	}

	/**
	 * Render document title
	 * 
	 * @return string
	 */
	public static function renderDocumentTitle() {
		$title = trim(strip_tags(self::renderTitle() ?? ''));
		return '<title>' . $title . '</title>';
	}

	/**
	 * Add messages
	 * 
	 * @param string|array $msg
	 * @param string $type
	 *		type one of:
	 *			danger
	 *			warning
	 *			success
	 *			info
	 *			other
	 * @param boolean $postponed
	 */
	public static function addMessage($msg, string $type = 'danger', bool $postponed = false) {
		if (!$postponed) {
			if (is_array($msg)) {
				foreach ($msg as $k=>$v) {
					Application::set(['messages', $type], $v, ['append'=>true]);
				}
			} else {
				Application::set(['messages', $type], $msg, ['append'=>true]);
			}
		} else { // postponed messages go into session
			if (is_array($msg)) {
				foreach ($msg as $k=>$v) {
					Session::set(['numbers', 'messages', $type], $v, ['append'=>true]);
				}
			} else {
				Session::set(['numbers', 'messages', $type], $msg, ['append'=>true]);
			}
		}
	}

	/**
	 * Render messages
	 * 
	 * @return string
	 */
	public static function renderMessages() : string {
		$result = '';
		// we need to see if we have postponed messages and render them first
		$postponed = Session::get(['numbers', 'messages']);
		if (!empty($postponed)) {
			Session::set(['numbers', 'messages'], []);
			foreach ($postponed as $k => $v) {
				$result.= \HTML::message(['options' => $v, 'type' => $k]);
			}
		}
		// regular messages
		$messages = Application::get(array('messages'));
		if (!empty($messages)) {
			foreach ($messages as $k => $v) {
				$result.= \HTML::message(['options' => $v, 'type' => $k]);
			}
		}
		return $result;
	}

	/**
	 * Add action
	 *
	 * @param string $code
	 * @param array $action
	 */
	public static function addAction(string $code, array $action) {
		$action['order'] = $action['order'] ?? 0;
		\Application::set(array('layout', 'actions', $code), $action);
	}

	/**
	 * Render actions
	 * 
	 * @return string
	 */
	public static function renderActions() : string {
		$result = '';
		$data = \Application::get(array('layout', 'actions'));
		if (!empty($data)) {
			// sorting first
			array_key_sort($data, ['order' => SORT_ASC], ['order' => SORT_NUMERIC]);
			// looping through data and building html
			$temp = [];
			foreach ($data as $k => $v) {
				if (empty($v)) continue;
				$icon = !empty($v['icon']) ? (\HTML::icon(['type' => $v['icon']]) . ' ') : '';
				$onclick = !empty($v['onclick']) ? $v['onclick'] : '';
				$value = !empty($v['value']) ? i18n(null, $v['value']) : '';
				$href = $v['href'] ?? 'javascript:void(0);';
				$temp[] = \HTML::a(['value' => $icon . $value, 'href' => $href, 'onclick' => $onclick, 'title' => $v['title'] ?? '']);
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
	public static function renderBreadcrumbs() : string {
		if (!empty(Application::$controller->breadcrumbs)) {
			$temp = array_slice(Application::$controller->breadcrumbs, 1, 2);
			$keys = [];
			foreach ($temp as $v) {
				$keys[] = $v;
				$keys[] = 'options';
			}
			$data = \Object\ACL\Resources::getStatic('menu', 'primary');
			// submenu is available only when we have breadcrumbs and menu is there
			$submenu = '';
			if (!empty($keys) && !empty($data[200])) {
				$data = array_key_get($data[200], $keys);
				$submenu = [];
				if (is_array($data)) {
					array_key_sort($data, ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
					foreach ($data as $k => $v) {
						$submenu[] = \HTML::a([
							'href' => \Request::fixUrl($v['url'], $v['template']),
							'value' => \HTML::icon(['type' => $v['icon']]) . ' ' . $v['name'],
						]);
						if (!empty($v['options'])) {
							array_key_sort($v['options'], ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
							foreach ($v['options'] as $k2 => $v2) {
								$submenu[] = \HTML::a([
									'href' => \Request::fixUrl($v2['url'], $v2['template']),
									'value' => '&nbsp;&nbsp;&nbsp;' . \HTML::icon(['type' => $v2['icon']]) . ' ' . $v2['name']
								]);
								if (!empty($v2['options'])) {
									array_key_sort($v2['options'], ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
									foreach ($v2['options'] as $k3 => $v3) {
										$submenu[] = \HTML::a([
											'href' => \Request::fixUrl($v3['url'], $v3['template']),
											'value' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . \HTML::icon(['type' => $v3['icon']]) . ' ' . $v3['name']
										]);
									}
								}
							}
						}
					}
					$submenu = \HTML::popover([
						'id' => 'breadcrumbs_submenu',
						'value' => \HTML::icon(['type' => 'fas fa-sticky-note']),
						'content' => implode(\HTML::br(), $submenu),
						'style' => 'overflow-y: scroll;'
					]);
				}
			}
			if (!empty($submenu)) {
				Application::$controller->breadcrumbs[] = $submenu;
			}
			return \HTML::breadcrumbs(Application::$controller->breadcrumbs);
		} else {
			return '';
		}
	}

	/**
	 * Render as content type, non HTML output should go though this function
	 *
	 * @param mixed $data
	 * @param string $content_type
	 * @param array $options
	 *		output_file_name
	 */
	public static function renderAs($data, string $content_type, array $options = []) {
		// clena up output buffer
		\Helper\Ob::cleanAll();
		Application::set('flag.global.__content_type', $content_type);
		Application::set('flag.global.__skip_layout', 1);
		header("Content-type: " . $content_type);
		if (!empty($options['output_file_name'])) {
			header('Content-Disposition: attachment; filename=' . $options['output_file_name']);
		}
		$options['extension'] = ($options['extension'] ?? '');
		\Log::add([
			'type' => 'Layout',
			'only_chanel' => 'default',
			'message' => 'Content type generated: ' . $content_type,
		]);
		switch ($content_type . $options['extension']) {
			case 'application/json':
				header('Connection: close');
				echo json_encode($data);
				break;
			case 'application/xml':
				if (is_array($data)) {
					echo array2xml($data);
				} else {
					echo $data;
				}
				break;
			case 'text/html':
				\Helper\Ob::start();
				require(Application::get(['application', 'path_full']) . 'Layout/blank.html');
				echo str_replace([
					'<!-- [numbers: document title] -->',
					'<!-- [numbers: document body] -->'
				], [
					\Layout::renderDocumentTitle(),
					$data
				], \Helper\Ob::clean());
				break;
			case 'text/htmlemail':
				\Helper\Ob::start();
				require(Application::get(['application', 'path_full']) . 'Layout/' . \Application::get('application.layout.email') . '.html');
				echo str_replace([
					'<!-- [numbers: document title] -->',
					'<!-- [numbers: document body] -->'
				], [
					'<title>' . ($options['title'] ?? '') . '</title>',
					$data
				], \Helper\Ob::clean());
				break;
			case 'text/htmlplain':
			default:
				echo $data;
		}
		\Log::deliver();
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
	public static function includeMedia($path, $controller, $view, $class) {
		// generating a list of extensions
		$valid_extensions = ['js', 'css'];
		if (Application::get('dep.submodule.numbers.frontend.media.scss')) {
			$valid_extensions[] = 'scss';
		}
		// we need to fix path for submodules
		$path_fixed = str_replace('/', '_', $path);
		$path_js = str_replace('_' . $controller, '', $class) . '_';
		if (substr($path, 0, 8) == 'numbers/') {
			$path = '../libraries/vendor/' . $path;
		}
		//$path = Application::get(['application', 'path_full']) . $path;
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
			Application::set(['application', 'loaded_classes', $class, 'media'], ['file' => $temp, 'full' => $new], ['append' => true]);
		}
	}
}