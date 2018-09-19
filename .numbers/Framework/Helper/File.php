<?php

/**
 * File/directory helper
 */
namespace Helper;
class File {

	/**
	 * Write content to file and sets permissions
	 *
	 * @param string $filename
	 * @param mixed $data
	 * @param int $permission
	 * @param int $flags
	 * @param boolean $relative
	 */
	public static function write($filename, $data, $permission = 0777, $flags = LOCK_EX, $relative = true) {
		// if we have relative path we convert it to full path
		if ($relative && $filename[0] == '.') {
			$path = \Application::get('application.path_full');
			$info = pathinfo($filename);
			$filename = realpath($path . $info['dirname']) . DIRECTORY_SEPARATOR . $info['basename'];
		}
		// write file
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
	 * @return string|boolean false
	 */
	public static function read($filename) {
		return file_get_contents($filename);
	}

	/**
	 * Create directory
	 *
	 * @param string $dir
	 * @param octal $permission
	 * @return boolean
	 */
	public static function mkdir($dir, $permission = 0777) {
		return mkdir($dir, $permission, true);
	}

	/**
	 * Delete file/directory
	 *
	 * @param string $dir
	 * @param arary $options
	 *		only_contents - whether to remove directory contents only
	 *		skip_files - array of files to skip
	 * @return boolean
	 */
	public static function delete(string $dir, array $options = []) : bool {
		if (is_dir($dir) && !is_link($dir)) {
			$skip_files = [];
			if (!empty($options['skip_files'])) {
				$skip_files = $options['skip_files'];
				$options['only_contents'] = true;
			}
			$skip_files[] = '.';
			$skip_files[] = '..';
			$objects = scandir($dir);
			foreach ($objects as $v) {
				if (!in_array($v, $skip_files)) {
					if (!self::delete($dir . DIRECTORY_SEPARATOR . $v, $options)) return false;
				}
			}
			if (empty($options['only_contents'])) {
				return rmdir($dir);
			} else {
				return true;
			}
		} else if (file_exists($dir)) {
			return unlink($dir);
		} else {
			return false;
		}
	}

	/**
	 * Iterate over directory
	 *
	 * @param string $dir
	 * @param array $options
	 *		boolean recursive
	 *		array only_extensions
	 *		array only_files
	 *		boolean extended
	 * @return array
	 */
	public static function iterate(string $dir, array $options = []) : array {
		$result = [];
		$relative_path = realpath($dir);
		// inner helper function to remove absolute path
		function iterate_process_path_inner_helper(string $dir, string $relative_path) {
			if ($relative_path == '') {
				return $dir;
			} else {
				$dir = trim2($dir, '^' . $relative_path, '');
				$dir = ltrim($dir, DIRECTORY_SEPARATOR);
				return $dir;
			}
		}
		if (empty($options['recursive'])) {
			$iterator = new \DirectoryIterator($dir);
		} else {
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
		}
		foreach ($iterator as $v) {
			if (method_exists($v, 'isDot')) {
				if ($v->isDot()) {
					continue;
				}
			} else {
				$filename = $v->getFilename();
				if ($filename === '.' || $filename === '..') {
					continue;
				}
			}
			if (!empty($options['only_extensions']) && !in_array($v->getExtension(), $options['only_extensions'])) {
				continue;
			}
			if (!empty($options['only_files']) && !in_array($filename, $options['only_files'])) {
				continue;
			}
			if (empty($options['extended'])) {
				$result[] = $v->getPathname();
			} else {
				$pathname = $v->getPathname();
				$result[$pathname] = [
					'pathname' => $pathname,
					'access' => $v->getATime(),
					'modified' => $v->getMTime(),
					'permissions' => $v->getPerms(),
					'size' => $v->getSize(),
					'type' => $v->getType(),
					'directory' => $v->getPath(),
					'basename' => $v->getBasename(),
					'filename' => $v->getFilename(),
					'relative_directory' => iterate_process_path_inner_helper($v->getPath(), $relative_path),
				];
			}
		}
		return $result;
	}

	/**
	 * Copy file/directory
	 *
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	public static function copy(string $source, string $destination, array $options = []) : bool {
		if (is_dir($source)) {
			$dir = opendir($source);
			if (!file_exists($destination)) {
				if (!self::mkdir($destination)) return false;
			}
			while (($file = readdir($dir)) !== false) {
				if ($file != '.' && $file != '..' && (empty($options['skip_files']) || (!empty($options['skip_files']) && !in_array($file, $options['skip_files'])))) {
					if (!self::copy($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file, $options)) return false;
				}
			}
			closedir($dir);
			return true;
		} else {
			return copy($source, $destination);
		}
	}

	/**
	 * Chmod
	 *
	 * @param string $dir_or_file
	 * @param int $permission
	 * @return bool
	 */
	public static function chmod(string $dir_or_file, int $permission = 0777) : bool {
		if (is_dir($dir_or_file)) {
			$dir = opendir($dir_or_file);
			while (($file = readdir($dir)) !== false) {
				if ($file != '.' && $file != '..') {
					if (!self::chmod($dir_or_file . DIRECTORY_SEPARATOR . $file, $permission)) return false;
				}
			}
			closedir($dir);
			return chmod($dir_or_file, $permission);
		} else {
			return chmod($dir_or_file, $permission);
		}
	}

	/**
	 * Replace string in a file
	 *
	 * @param string $filename
	 * @param string $find
	 * @param string $replace
	 * @return bool
	 */
	public static function replace(string $filename, string $find, string $replace) : bool {
		if (!file_exists($filename)) return false;
		$lines = file($filename, FILE_IGNORE_NEW_LINES);
		foreach ($lines as $k => $v) {
			if (stripos($v, $find) !== false) {
				$lines[$k] = $replace;
			}
		}
		return self::write($filename, implode("\n", $lines));
	}
}