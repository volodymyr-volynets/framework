<?php

/**
 * 
 * @author Volodymyr Volynets
 *
 */
class file {
	
	/**
	 * Extensions we allow to upload
	 * 
	 * @var array
	 */
	public static $extensions = array('gif','jpg','jpeg','tiff','png','doc','docx','xls','xlsx','pdf');
	
	/**
	 * Extension for thumbnail
	 * 
	 * @var unknown_type
	 */
	public static $extensions_for_thumbnails = array('jpg','jpeg','png', 'gif');
	
	/**
	 * Write content to file and sets permissions
	 * 
	 * @param string $filename
	 * @param mixed $data
	 * @param int $permission
	 * @param int $flags
	 */
	public static function write($filename, $data, $permission = 0777, $flags = LOCK_EX) {
		if (file_put_contents($filename, $data, $flags) !== false) {
			@chmod($filename, $permission);
			return true;
		}
		return false;
	}
	
	/**
	 * Read file
	 * 
	 * @param string $filename
	 * @return string
	 */
	public static function read($filename) {
		return file_get_contents($filename);
	}
	
	/**
	 * Upload file to the server
	 * 
	 * @param string $fid
	 * @param string $file_name
	 * @param string $path
	 * @param array $extensions
	 * @return array
	 */
	public static function upload($fid, $file_name, $path, $extensions = array()) {
		global $_FILES;
		$result = array(
			'error'=>array(),
			'success'=>false,
			'file_name_safe' => '',
			'file_name_full' => '',
			'size' => '',
			'type' => '',
			'name' => '',
		);
		$file_upload_valid_extensions = !empty($extensions) ? $extensions : self::$extensions;
		$file_error_types = array(
			1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			2=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
			3=>'The uploaded file was only partially uploaded.',
			4=>'No file was uploaded.',
			6=>'Missing a temporary folder.',
			7=>'Failed to write file to disk.',
			8=>'A PHP extension stopped the file upload.',
		);
		do {
			if (!is_uploaded_file(@$_FILES[$fid]['tmp_name']) || @$_FILES[$fid]['size'] == 0) {
				$result['error'][] = 'Error occured when uploading file!';
				break;
			}
			if ($_FILES[$fid]['error']) {
				$result['error'][] = $file_error_types[$_FILES[$fid]['error']];
				break;
			}
			$file_extension = pathinfo($_FILES[$fid]['name'], PATHINFO_EXTENSION);
			if (!in_array(strtolower($file_extension), $file_upload_valid_extensions) && !in_array('*.*', $file_upload_valid_extensions)) {
				$result['error'][] = 'You can not upload files with extension';
				break;
			}
			//$result['file_name'] = !empty($file_name) ? $file_name : trim($_FILES[$fid]['name']);
			$result['file_name_safe'] = strtolower(!empty($file_name) ? $file_name : preg_replace(array('/\s+/', '/[^-\.\w]+/'), array('_', ''), trim($_FILES[$fid]['name'])));
			$result['file_name_full'] = $path ? ($path . '/' . $result['file_name_safe']) : $result['file_name_safe'];
			// create directory recursively
			if (!file_exists($path)) mkdir($path, 0777, true);
			// uploading
			if (!move_uploaded_file($_FILES[$fid]['tmp_name'], $result['file_name_full'])) {
				$result['error'][] = 'Could not upload file (Move error)!';
				break;
			} else {
				// we set permission that everyone can change uploaded file
				@chmod($result['file_name_full'], 0777);
			}
			$result['success'] = true;
			// other variables
			$result['size'] = $_FILES[$fid]['size'];
			$result['type'] = $_FILES[$fid]['type'];
			$result['name'] = $_FILES[$fid]['name'];
		} while(0);
		return $result;
	}
	
	/**
	 * Remove directory with its content
	 * 
	 * @param unknown_type $dir
	 */
	public static function rmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") file::rmdir($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
}