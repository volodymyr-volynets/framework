<?php

class system_media {

	/**
	 * Serve js and/or css files, mostly used in development
	 *
	 * @param string $filename
	 */
	public static function serve_media_if_exists($filename, $application_path) {
		// we need to remove question mark and all after it
		if (strpos($filename, '?') !== false) {
			$temp = explode('?' , $filename);
			$filename = $temp[0];
		}
		// generated files first
		if (strpos($filename, '/numbers/media_generated/') === 0) {
			$filename = str_replace('/numbers/media_generated/application_', '', $filename);
			$filename = $application_path . str_replace('_', '/', $filename);
		} else if (strpos($filename, '/numbers/media_submodules/') === 0) {
			$temp2 = str_replace('/numbers/media_submodules/', '', $filename);
			$temp = explode('.', $temp2);
			$extension = $temp[1];
			$temp = explode('_', $temp[0]);
			// we unset last key, possible can of worms in the future
			end($temp);
			$key = key($temp);
			unset($temp[$key]);
			$filename = './../libraries/vendor/' . implode('/', $temp) . '/media/' . $extension . '/' . $temp2;
		} else {
			// we must return, do not exit !!!
			return;
		}
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext == 'css' || $ext == 'js') {
			if (file_exists($filename)) {
				if ($ext == 'js') header('Content-Type: application/javascript');
				if ($ext == 'css') header('Content-type: text/css');
				echo file_get_contents($filename);
				exit;
			}
		}
	}
}