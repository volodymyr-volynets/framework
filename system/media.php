<?php

class system_media {

	/**
	 * Serve js and/or css files, mostly used in development
	 *
	 * @param string $filename
	 */
	public static function serve_media_if_exists($filename, $application_path) {
		if (strpos($filename, '/numbers/media_generated/') === 0) {
			$filename = str_replace('/numbers/media_generated/app_', '', $filename);
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if ($ext == 'css' || $ext == 'js') {
				$filename = $application_path . str_replace('_', '/', $filename);
				if (file_exists($filename)) {
					if ($ext == 'js') header('Content-Type: application/javascript');
					if ($ext == 'css') header('Content-type: text/css');
					echo file_get_contents($filename);
					exit;
				}
			}
		}
	}
}