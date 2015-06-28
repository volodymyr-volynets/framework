<?php

class ftp {

	/**
	 * This would upload file to the ftp site, it will also change and create
	 * folder automatically
	 *
	 * @param string $in_file
	 * @param string $out_file
	 * @param array $options [host,username,password,upload_mode]
	 * @return array
	 */
	public static function upload($in_file, $out_file, $options) {
		$result = array(
			'error' => array(),
			'success' => false
		);
		$flag_ftp_openned = false;
		do {
			// if host exists and filanames populated
			if (empty($options['host']) || empty($in_file) || empty($out_file)) {
				$result['error'][] = 'Host, in/out file names?';
				break;
			}

			$conn_id = ftp_connect($options['host']);
			$flag_ftp_openned = true;
			
			// login with username and password
			$login_result = @ftp_login($conn_id, @$options['username'], @$options['password']);
			
			// check connection
			if ((!$conn_id) || (!$login_result)) {
				$result['error'][] = 'FTP connection has failed!';
				break;
			}
			
			// turning on passive mode
			ftp_pasv($conn_id, true);
			
			// get directory + file name
			$dir = pathinfo($out_file, PATHINFO_DIRNAME);
			$file = pathinfo($out_file, PATHINFO_BASENAME);
			
			// changing directory if it is not root
			if ($dir && $dir!='/') {
				if ($dir[0]=='/') $dir = substr($dir, 1);
				$dir_status = self::mkdir($conn_id, $dir);
				if (!$dir_status) {
					$result['error'][] = 'Unable to create or change directory!';
					break;
				}
				if (!ftp_chdir($conn_id, $dir)) {
					$result['error'][] = 'Unable to create or change directory!';
					break;
				}
			}
			
			// uploading a file
			$options['upload_mode'] = !empty($options['upload_mode']) ? $options['upload_mode'] : FTP_BINARY;
			if (!ftp_put($conn_id, $file, $in_file, $options['upload_mode'])) {
				$result['error'][] = "There was a problem while uploading $file";
				break;
			}

			$result['success'] = true;
		} while(0);
		
		// closing connection
		if ($flag_ftp_openned) {
			@ftp_close($conn_id);
		}
		return $result;
	}


	/**
	 * This would upload file to the ftp site, it will also change and create
	 * folder automatically
	 *
	 * @param string $in_file
	 * @param string $out_file
	 * @param array $options [host,username,password]
	 * @return array
	 */
	public static function delete($in_file, $options) {
		$result = array(
			'error' => array(),
			'success' => false
		);
		$flag_ftp_openned = false;
		do {
			// checking if host name and file is not empty
			if (empty($options['host']) || empty($in_file)) {
				$result['error'][] = 'Host, in/out file names?';
				break;
			}

			$conn_id = ftp_connect($options['host']);
			$flag_ftp_openned = true;
			
			// login with username and password
			$ftp_user_name = "media";
			$ftp_password = "media123";
			$login_result = @ftp_login($conn_id, @$options['username'], @$options['password']);
			
			// check connection
			if ((!$conn_id) || (!$login_result)) {
				$result['error'][] = 'FTP connection has failed!';
				break;
			}
			
			// turning on passive mode
			ftp_pasv($conn_id, true);

			$dir = pathinfo($in_file, PATHINFO_DIRNAME);
			$file = pathinfo($in_file, PATHINFO_BASENAME);
			// changing directory
			if ($dir) {
				if ($dir[0]=='/') $dir = substr($dir, 1);
				if (!ftp_chdir($conn_id, $dir)) {
					$result['error'][] = 'Unable to create or change directory!';
					break;
				}
			}

			// delete file name
			if (!ftp_delete($conn_id, $file)) {
				$result['error'][] = 'FTP delete failed!';
				break;
			}

			$result['success'] = true;
		} while(0);
		
		// closing connection
		if ($flag_ftp_openned) {
			ftp_close($conn_id);
		}
		return $result;
	}

	/**
	 * Recursive make directory function for ftp
	 * 
	 * @param resource $ftp_stream
	 * @param string $dir
	 * @return boolean
	 */
	public static function mkdir($ftp_stream, $dir) {
		// if directory already exists or can be immediately created return true
		if (self::isDir($ftp_stream, $dir) || @ftp_mkdir($ftp_stream, $dir)) return true;
		// otherwise recursively try to make the directory
		if (!self::mkdir($ftp_stream, dirname($dir))) return false;
		// final step to create the directory
		return ftp_mkdir($ftp_stream, $dir);
	}

	/**
	 * Check if FTP directory exists
	 * 
	 * @param resource $ftp_stream
	 * @param string $dir
	 * @return boolean
	 */
	private static function is_dir($ftp_stream, $dir) {
		// get current directory
		$original_directory = ftp_pwd($ftp_stream);
		// test if you can change directory to $dir
		// suppress errors in case $dir is not a file or not a directory
		if (@ftp_chdir($ftp_stream, $dir)) {
			// If it is a directory, then change the directory back to the original directory
			ftp_chdir( $ftp_stream, $original_directory );
			return true;
		} else {
			return false;
		}
	}
}