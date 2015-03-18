<?php

class icon {
	
	public static $path_html = null;
	public static $path_server = null;
	
	/**
	 * Render icon
	 * 
	 * @param string $name
	 * @param string $width_and_height
	 * @param string $alt
	 * @return string
	 */
	public static function render($name, $alt = '') {
		if (self::$path_html===null) {
			$data = application::get('icon');
			if (empty($data)) Throw new Exception('Icon settings?');
			self::$path_html = $data['path_html'];
			self::$path_server = $data['path_server'];
		}
		$width_and_height = 16;
		if (strpos($name, '24.')!==false) $width_and_height = 24;
		if (strpos($name, '32.')!==false) $width_and_height = 32;
		return h::img(array('src'=>self::$path_html . $name, 'width'=>$width_and_height, 'height'=>$width_and_height, 'alt'=>$alt, 'style'=>'vertical-align: top;'));
	}
}