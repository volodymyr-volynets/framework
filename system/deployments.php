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
			if (empty($options['mode'])) {
				$options['mode'] = 'code';
			}

			$temp = rtrim(getcwd(), '/');
			$deployed_dir = $temp . '/../../deployed';
			$code_dir = $temp . '/../../code';

			// for development we handle deployment differently, just symlink to the code
			if ($options['mode'] == 'code_dev') {
				if (file_exists($deployed_dir)) {
					shell_exec("rm -r $deployed_dir");
				}
				symlink($code_dir, $deployed_dir);
				$result['success'] = true;
				break;
			}

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

			// js, css, scss, files here
			$files_to_copy = [];
			$process_extensions = ['js', 'css'];
			if (application::get('dep.submodule.numbers.frontend.media.scss')) {
				$process_extensions[] = 'scss';
			}
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($dep_dir)
			);
			foreach ($iterator as $filename => $cur) {
				$extension = $cur->getExtension();
				if (in_array($extension, $process_extensions)) {
					$parent_dir_name = basename(dirname($filename));
					if (strpos($filename, '/controller/') !== false) {
						$key = str_replace($dep_dir, '', $filename);
						$files_to_copy[$extension][$key] = $filename;
					}
				}
			}

			// create media directory
			$media_dir_full = $media_dir . '/media_generated';
			if (!empty($files_to_copy['js']) || !empty($files_to_copy['css']) || !empty($files_to_copy['scss'])) {
				mkdir($media_dir_full, 0777);
			}

			// coping javescript files
			if (!empty($files_to_copy['js'])) {
				foreach ($files_to_copy['js'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					shell_exec("cp -r $v $media_dir_full/$newname");
				}
			}

			// coping css files
			if (!empty($files_to_copy['css'])) {
				foreach ($files_to_copy['css'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					shell_exec("cp -r $v $media_dir_full/$newname");
				}
			}

			// coping scss files
			if (!empty($files_to_copy['scss'])) {
				foreach ($files_to_copy['scss'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					// processing scss files
					$temp = numbers_frontend_media_scss_base::serve($v);
					if ($temp['success']) {
						file_put_contents("{$media_dir_full}/{$newname}.css", $temp['data']);
					}
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
							shell_exec("rm -r $temp");
						}
					}
				}
			}

			$result['success'] = true;
		} while(0);
		return $result;
	}
}