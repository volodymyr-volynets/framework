<?php

namespace System;
class Media {

	/**
	 * Serve js and/or css files, mostly used in development
	 *
	 * @param string $filename
	 */
	public static function serveMediaIfExists($filename, $application_path) {
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
			$temp = str_replace('/numbers/media_submodules/', '', $filename);
			$temp = str_replace('_', '/', $temp);
			$filename = './../libraries/vendor/' . $temp;
		} else {
			// we must return, do not exit !!!
			return;
		}
		// check if file exists on file system
		if (!file_exists($filename)) {
			return;
		}
		// we need to know extension of a file
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext == 'css' || $ext == 'js') {
			$new = $filename;
			$flag_scss = false;
			if (strpos($filename, '.scss.css') !== false) {
				$new = str_replace('.scss.css', '.scss', $new);
				$flag_scss = true;
			}
			if (file_exists($new)) {
				if ($ext == 'js') {
					header('Content-Type: application/javascript');
					echo file_get_contents($new);
				}
				if ($ext == 'css') {
					header('Content-type: text/css');
					if (!$flag_scss) {
						echo file_get_contents($new);
					} else if (Application::get('dep.submodule.numbers.frontend.media.scss')) {
						$temp = numbers_frontend_media_scss_base::serve($new);
						if ($temp['success']) {
							echo $temp['data'];
						}
					}
				}
				exit;
			}
		} else { // other files that exist on file system
			$mime = mime_content_type($filename);
			header('Content-type: text/css');
			echo file_get_contents($filename);
		}
	}
}