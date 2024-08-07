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
			$data = \System\Dependencies::processDepsAll(['mode' => 'test']);
			if ($options['mode'] == 'development') {
				\Helper\File::delete($deployed_dir);
				symlink($code_dir, $deployed_dir);
				// process components
				if (!empty($data['data']['components'])) {
					// create directories
					\Helper\File::mkdir($code_dir . '/application/Components');
					\Helper\File::mkdir($code_dir . '/application/../public_html/components');
					foreach ($data['data']['components'] as $k => $v) {
						\Helper\File::delete($code_dir . '/application/Components/' . $k . '/');
						\Helper\File::delete($code_dir . '/application/../public_html/components/' . $k . '/');
						\Helper\File::mkdir($code_dir . '/application/Components/' . $k . '/');
						\Helper\File::mkdir($code_dir . '/application/../public_html/components/' . $k . '/');
						foreach (['application', 'public_html'] as $v2) {
							$files = \Helper\File::iterate($v . $v2, ['recursive' => true]);
							foreach ($files as $v3) {
								$temp1 = explode('/' . $v2 . '/', $v3);
								if ($v2 == 'application') {
									\Helper\File::copy($code_dir . '/' . $v2 . '/' . $v3, $code_dir . '/application/Components/' . $k . '/' . $temp1[1]);
								} else {
									\Helper\File::copy($code_dir . '/' . $v2 . '/' . $v3, $code_dir . '/application/../public_html/components/' . $k . '/' . $temp1[1]);
								}
							}
						}
					}
				}
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
			if (!\Helper\File::copy($code_dir, $dep_dir, ['skip_directories' => ['.numbers', '.git', '.docs', '.vue'], 'skip_files' => ['Makefile', 'Make.cmd', '.gitignore']])) {
				$result['error'][] = ' - unable to copy code!';
				break;
			}
			// copy components
			\Helper\File::mkdir($dep_dir . '/application/Components');
			\Helper\File::mkdir($dep_dir . '/application/../public_html/components');
			foreach ($data['data']['components'] as $k => $v) {
				\Helper\File::mkdir($code_dir . '/application/Components/' . $k . '/');
				\Helper\File::mkdir($code_dir . '/application/../public_html/components/' . $k . '/');
				foreach (['application', 'public_html'] as $v2) {
					$files = \Helper\File::iterate($v . $v2, ['recursive' => true]);
					foreach ($files as $v3) {
						$temp1 = explode('/' . $v2 . '/', $v3);
						if ($v2 == 'application') {
							\Helper\File::copy($code_dir . '/' . $v2 . '/' . $v3, $dep_dir . '/application/Components/' . $k . '/' . $temp1[1]);
						} else {
							\Helper\File::copy($code_dir . '/' . $v2 . '/' . $v3, $dep_dir . '/application/../public_html/components/' . $k . '/' . $temp1[1]);
						}
					}
				}
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
						if (file_exists($dep_dir . '/libraries/private' . $v2['origin'])) {
							$copy_from = $dep_dir . '/libraries/private' . $v2['origin'];
						} else {
							if (strpos($v2['origin'], '/Numbers/') === 0) {
								$updated_origin = explode('/', $v2['origin']);
								$updated_origin[1] = strtolower($updated_origin[1]);
								$updated_origin[2] = strtolower($updated_origin[2]);
								$updated_origin = implode('/', $updated_origin);
							} else {
								$updated_origin = $v2['origin'];
							}
							$copy_from = $dep_dir . '/libraries/vendor' . $updated_origin;
						}
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
			// vue
			if (file_exists($code_dir . '/.vue/dist')) {
				$vue_dir = $dep_dir . '/public_html/vue';
				\Helper\File::mkdir($vue_dir, 0777);
				$files = \Helper\File::iterate($code_dir . '/.vue/dist', ['recursive' => true]);
				foreach ($files as $v3) {
					$relative = explode('/application/.vue/dist/', $v3);
					\Helper\File::copy($v3, $vue_dir . DIRECTORY_SEPARATOR . $relative[1]);
				}
				$htaccess = <<<TTT
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{REQUEST_FILENAME} -s [OR]
	RewriteCond %{REQUEST_FILENAME} -l [OR]
	RewriteCond %{REQUEST_FILENAME} -d
	RewriteRule ^.*$ - [NC,L]
	RewriteRule ^.*$ index.html [NC,L]
</IfModule>
DirectoryIndex index.html
TTT;
				file_put_contents($vue_dir . DIRECTORY_SEPARATOR . '.htaccess', $htaccess);
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