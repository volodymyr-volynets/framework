<?php

/**
 * Layout class
 * 
 * @author Volodymyr Volynets
 *
 */
class layout extends view {
	
	public static $title_override = null;
	public static $icon_override = null;
	
	/**
	 * Onload javascript
	 * 
	 * @var string
	 */
	private static $onload = '';
	
	/**
	 * Html to be prepended can be stored here
	 * 
	 * @var string 
	 */
	public static $last_html = '';
	
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
		$version = application::get(array('content', 'version', 'css'));
		if (!empty($css)) {
			asort($css);
			foreach ($css as $k=>$v) {
				$script = $k . (strpos($k, '?')!=false ? '&' : '?') . $version;
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
		$version = application::get(array('content', 'version', 'js'));
		if (!empty($js)) {
			asort($js);
			foreach ($js as $k=>$v) {
				$script = $k . (strpos($k, '?')!=false ? '&' : '?') . $version;
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
			return h::script(array('value'=>'$(document).ready(function(){ ' . self::$onload . ' });'));
		}
	}
	
	/**
	 * Render title
	 *
	 * @return string
	 */
	public static function render_title() {
		$result = '';
		$data = application::get(array('controller'));
		//$controllers = application::get(array('mvc', 'controllers'));
		if (!empty(self::$title_override)) $data['title'] = self::$title_override;
		if (!empty(self::$icon_override)) $data['icon'] = self::$icon_override;
		if (!empty($data['title'])) {
			$data['icon'] = !empty($data['icon']) ? icon::render($data['icon']) : '';
			$result.= '<br/><h3 class="content_title" style="margin-top: 0;">'. ($data['icon'] ? ($data['icon'] . ' ') : '') . $data['title'] . '</h3>';
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
		if (is_array($messages)) {
			foreach ($messages as $k=>$v) {
				$result.= h::message($v, $k);
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
				$icon = @$v['icon'] ? $v['icon'] : '';
				$temp[] = h::a(array('value'=>$icon . @$v['value'], 'href'=>$v['href'], 'onclick'=>@$v['onclick']));
			}
			$result = implode(' ', $temp);
		}
		return $result;
	}
}