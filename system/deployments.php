<?php

class system_deployments {

	/**
	 * Deploy application
	 *
	 * @param array $options
	 * @return array
	 */
	public static function deploy($options = array()) {
		$result = array(
			'success' => false,
			'error' => array()
		);
		do {
			$temp = rtrim(getcwd(), '/');
			$deployed_dir = $temp . '/../../deployed';
			$code_dir = $temp . '/../../code';
			$all_deps_dir = $temp . '/../../deployments';
			$time = time();
			$dep_id = 'build.' . $time . '.' . rand(100, 999);
			$dep_dir = $all_deps_dir . '/' . $dep_id;
			$media_dir = $dep_dir . '/public_html/numbers';
			if (mkdir($dep_dir, 0777) === false) {
				$result['error'][] = ' - unable to create new deployment directory ' . $dep_dir;
				break;
			}

			// copying code repository
			shell_exec("cp -r $code_dir/. $dep_dir");

			// removing what we do not want to have
			$dels = array('.git', 'Makefile');
			foreach ($dels as $v) {
				shell_exec("rm -r $dep_dir/$v");
			}

			// javascript, css, scss, files here
			$files_to_copy = array();
			$process_extensions = array('js', 'css');
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($dep_dir)
			);
			foreach ($iterator as $filename => $cur) {
				$extension = $cur->getExtension();
				if (in_array($extension, $process_extensions)) {
					$parent_dir_name = basename(dirname($filename));
					if ($parent_dir_name == 'controller') {
						$key = str_replace($dep_dir, '', $filename);
						$files_to_copy[$extension][$key] = $filename;
					}
				}
			}

			// coping javescript files
			if (!empty($files_to_copy['js'])) {
				mkdir($media_dir . '/js_generated', 0777);
				foreach ($files_to_copy['js'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					shell_exec("cp -r $v $media_dir/js_generated/$newname");
				}
			}

			// coping css files
			if (!empty($files_to_copy['css'])) {
				mkdir($media_dir . '/css_generated', 0777);
				foreach ($files_to_copy['css'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					shell_exec("cp -r $v $media_dir/css_generated/$newname");
				}
			}

			// setting permissions
			shell_exec("chmod -R 0777 $dep_dir");

			// now we need to create a symlink
			if (file_exists($deployed_dir)) {
				shell_exec("rm -r $deployed_dir");
			}
			symlink($dep_dir, $deployed_dir);

			// cleanup older deployments,older than 5 days
			$iterator = new DirectoryIterator($all_deps_dir);
			foreach ($iterator as $filedir => $fileinfo) {
				if ($fileinfo->isDir()) {
					$filename = $fileinfo->getFilename();
					// sanity check
					if ($filename == $dep_id) continue;
					if (strpos($filename, 'build.') === 0) {
						if ($time - $fileinfo->getMTime() > 259200) {
							$temp = $fileinfo->getPathname();
							//shell_exec("rm -r $temp");
						}
					}
				}
			}

			$result['success'] = true;
		} while(0);
		return $result;
	}
}