<?php

namespace System;
class Deployments {

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
		$options['mode'] = $options['mode'] ?? 'production';
		do {
			$temp = rtrim(getcwd(), '/');
			$deployed_dir = $temp . '/../../deployed';
			$code_dir = $temp . '/../../application';
			// for development we handle deployment differently, just symlink to the code
			if ($options['mode'] == 'development') {
				\Helper\File::delete($deployed_dir);
				symlink($code_dir, $deployed_dir);
				$result['success'] = true;
				break;
			}
			// determine and create directories
			$all_deps_dir = $temp . '/../../deployments';
			$time = time();
			$dep_id = 'build.' . $time . '.' . rand(100, 999);
			$dep_dir = $all_deps_dir . '/' . $dep_id;
			$media_dir = $dep_dir . '/public_html/numbers';
			if (\Helper\File::mkdir($dep_dir, 0777) === false) {
				$result['error'][] = ' - unable to create new deployment directory ' . $dep_dir;
				break;
			}
			// copying code repository
			if (!\Helper\File::copy($code_dir, $dep_dir, ['skip_files' => ['.git', 'Makefile', '.gitignore']])) {
				$result['error'][] = ' - unable to copy code!';
				break;
			}
			// js, css, scss, files here
			$files_to_copy = [];
			$process_extensions = ['js', 'css'];
			if (\Application::get('dep.submodule.numbers.frontend.media.scss')) {
				$process_extensions[] = 'scss';
			}
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dep_dir)
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
				\Helper\File::mkdir($media_dir_full, 0777);
			}
			// coping javescript files
			if (!empty($files_to_copy['js'])) {
				foreach ($files_to_copy['js'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					\Helper\File::copy($v, "$media_dir_full/$newname");
				}
			}
			// coping css files
			if (!empty($files_to_copy['css'])) {
				foreach ($files_to_copy['css'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					\Helper\File::copy($v, "$media_dir_full/$newname");
				}
			}
			// coping scss files
			if (!empty($files_to_copy['scss'])) {
				foreach ($files_to_copy['scss'] as $k => $v) {
					$newname = ltrim(str_replace('/', '_', $k), '_');
					// processing scss files
					$temp = \Numbers\Frontend\Media\SCSS\Base::serve($v);
					if ($temp['success']) {
						file_put_contents("{$media_dir_full}/{$newname}.css", $temp['data']);
					}
				}
			}
			// we need to load media from dependencies
			$result = \System\Dependencies::processDepsAll(['mode' => 'test']);
			// copying js, css & scss files
			$media_dir_submodule = $dep_dir . '/public_html';
			if (!empty($result['data']['media'])) {
				\Helper\File::mkdir($media_dir_submodule . '/numbers/media_submodules', 0777);
				foreach ($result['data']['media'] as $k => $v) {
					if (!in_array($k, ['js', 'css', 'scss', 'other'])) {
						continue;
					}
					foreach ($v as $k2 => $v2) {
						if (!isset($v2['origin']) || !isset($v2['destination'])) {
							continue;
						}
						// js and css we just copy
						$copy_from = $dep_dir . '/libraries/vendor' . $v2['origin'];
						$copy_to = $media_dir_submodule . $v2['destination'];
						if ($k == 'js' || $k == 'css' || $k == 'other') {
							\Helper\File::copy($copy_from, $copy_to);
						} else if ($k == 'scss' && Application::get('dep.submodule.numbers.frontend.media.scss')) {
							// todo
							// we need to process scss
							$temp = numbers_frontend_media_scss_base::serve($copy_from);
							if ($temp['success']) {
								file_put_contents($copy_to, $temp['data']);
							}
						}
					}
				}
			}
			// setting permissions
			\Helper\File::chmod($dep_dir, 0777);
			// now we need to create a symlink
			if (file_exists($deployed_dir)) {
				\Helper\File::delete($deployed_dir);
			}
			symlink($dep_dir, $deployed_dir);
			// cleanup older deployments,older than 5 days
			$iterator = new \DirectoryIterator($all_deps_dir);
			foreach ($iterator as $filedir => $fileinfo) {
				if ($fileinfo->isDir()) {
					$filename = $fileinfo->getFilename();
					// sanity check
					if ($filename == $dep_id) continue;
					if (strpos($filename, 'build.') === 0) {
						if ($time - $fileinfo->getMTime() > 259200) {
							$temp = $fileinfo->getPathname();
							\Helper\File::delete($temp);
						}
					}
				}
			}
			$result['success'] = true;
		} while(0);
		return $result;
	}
}