<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace System;

use Helper\File;
use Numbers\Frontend\Media\SCSS\Base;

class Deployments
{
    /**
     * Deploy application
     *
     * @param array $options
     * @return array
     */
    public static function deploy($options = array())
    {
        $result = array(
            'success' => false,
            'error' => array()
        );
        $options['mode'] = $options['mode'] ?? 'production';
        do {
            $base_dir = $temp = rtrim(getcwd(), '/');
            $deployed_dir = $temp . '/../../deployed';
            $code_dir = $temp . '/../../application';
            // for development we handle deployment differently, just symlink to the code
            $data = Dependencies::processDepsAll(['mode' => 'test']);
            if ($options['mode'] == 'development') {
                File::delete($deployed_dir);
                symlink($code_dir, $deployed_dir);
                // process components
                if (!empty($data['data']['components'])) {
                    // create directories
                    File::mkdir($code_dir . '/application/Components');
                    File::mkdir($code_dir . '/application/../public_html/components');
                    foreach ($data['data']['components'] as $k => $v) {
                        File::delete($code_dir . '/application/Components/' . $k . '/');
                        File::delete($code_dir . '/application/../public_html/components/' . $k . '/');
                        File::mkdir($code_dir . '/application/Components/' . $k . '/');
                        File::mkdir($code_dir . '/application/../public_html/components/' . $k . '/');
                        foreach (['application', 'public_html'] as $v2) {
                            $files = File::iterate($v . $v2, ['recursive' => true]);
                            foreach ($files as $v3) {
                                $temp1 = explode('/' . $v2 . '/', $v3);
                                if ($v2 == 'application') {
                                    File::copy($code_dir . '/' . $v2 . '/' . $v3, $code_dir . '/application/Components/' . $k . '/' . $temp1[1], ['chmod' => 0777]);
                                } else {
                                    File::copy($code_dir . '/' . $v2 . '/' . $v3, $code_dir . '/application/../public_html/components/' . $k . '/' . $temp1[1], ['chmod' => 0777]);
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
            $media_base = $dep_dir . '/public_html';
            if (File::mkdir($dep_dir, 0777) === false) {
                $result['error'][] = ' - unable to create new deployment directory ' . $dep_dir;
                break;
            }
            // copy components before copying code
            File::mkdir($dep_dir . '/application/Components');
            File::mkdir($dep_dir . '/application/../public_html/components');
            foreach ($data['data']['components'] as $k => $v) {
                File::mkdir($code_dir . '/application/Components/' . $k . '/');
                File::mkdir($code_dir . '/application/../public_html/components/' . $k . '/');
                foreach (['application', 'public_html'] as $v2) {
                    $files = File::iterate($v . $v2, ['recursive' => true]);
                    foreach ($files as $v3) {
                        $temp1 = explode('/' . $v2 . '/', $v3);
                        if ($v2 == 'application') {
                            File::copy($code_dir . '/' . $v2 . '/' . $v3, $code_dir . '/application/Components/' . $k . '/' . $temp1[1], ['chmod' => 0777]);
                        } else {
                            File::copy($code_dir . '/' . $v2 . '/' . $v3, $code_dir . '/application/../public_html/components/' . $k . '/' . $temp1[1], ['chmod' => 0777]);
                        }
                    }
                }
            }
            // copying code repository
            if (!File::copy($code_dir, $dep_dir, ['skip_directories' => ['.numbers', '.git', '.docs', 'public_sites'], 'skip_files' => ['Makefile', 'Make.cmd', '.gitignore']])) {
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
                File::mkdir($media_dir_full, 0777, ['skip_realpath' => true]);
            }
            // coping javescript files
            if (!empty($files_to_copy['js'])) {
                foreach ($files_to_copy['js'] as $k => $v) {
                    $newname = ltrim(str_replace('/', '_', $k), '_');
                    File::copy($v, "$media_dir_full/$newname");
                }
            }
            // coping css files
            if (!empty($files_to_copy['css'])) {
                foreach ($files_to_copy['css'] as $k => $v) {
                    $newname = ltrim(str_replace('/', '_', $k), '_');
                    File::copy($v, "$media_dir_full/$newname");
                }
            }
            // coping scss files
            if (!empty($files_to_copy['scss'])) {
                foreach ($files_to_copy['scss'] as $k => $v) {
                    $newname = ltrim(str_replace('/', '_', $k), '_');
                    // processing scss files
                    $temp = Base::serve($v);
                    if ($temp['success']) {
                        file_put_contents("{$media_dir_full}/{$newname}.css", $temp['data']);
                    }
                }
            }
            // we need to load media from dependencies
            $result = Dependencies::processDepsAll(['mode' => 'test']);
            // copying js, css & scss files
            $media_dir_submodule = $dep_dir . '/public_html';
            if (!empty($result['data']['media'])) {
                File::mkdir($media_dir_submodule . '/numbers/media_submodules', 0777, ['skip_realpath' => true]);
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
                            File::copy($copy_from, $copy_to);
                        } elseif ($k == 'scss' && \Application::get('dep.submodule.numbers.frontend.media.scss')) {
                            // todo: we need to process scss
                            /*
                            $temp = numbers_frontend_media_scss_base::serve($copy_from);
                            if ($temp['success']) {
                                file_put_contents($copy_to, $temp['data']);
                            }
                            */
                        }
                    }
                }
            }
            // public html assets
            if (!empty($result['data']['public_html'])) {
                foreach ($result['data']['public_html'] as $v) {
                    $files = File::iterate($base_dir . DIRECTORY_SEPARATOR . $v, ['recursive' => true, 'extended' => true]);
                    foreach ($files as $k2 => $v2) {
                        File::copy($k2, $media_base . DIRECTORY_SEPARATOR . $v2['relative_simple']);
                    }
                }
            }
            // sites
            if (file_exists($code_dir . '/public_sites')) {
                $sites_ini_files = File::iterate($code_dir . '/public_sites', ['only_extensions' => 'ini']);
                if ($sites_ini_files) {
                    foreach ($sites_ini_files as $v) {
                        $sites_ini_data = Config::ini($v, \Application::get('environment') ?? 'production');
                        // create directory
                        $site_dir = $dep_dir . DIRECTORY_SEPARATOR . $sites_ini_data['site']['public_dir'];
                        File::mkdir($site_dir, 0777, ['skip_realpath' => true]);
                        // copy build files
                        $files = File::iterate($code_dir . DIRECTORY_SEPARATOR. $sites_ini_data['site']['build_dir'], ['recursive' => true]);
                        foreach ($files as $v3) {
                            $relative = explode($sites_ini_data['site']['build_dir'], $v3);
                            File::copy($v3, $site_dir . DIRECTORY_SEPARATOR . $relative[1]);
                        }
                        // htaccess
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
                        file_put_contents($site_dir . DIRECTORY_SEPARATOR . '.htaccess', $htaccess);
                    }
                }
            }
            // setting permissions
            File::chmod($dep_dir, 0777);
            // now we need to create a symlink
            if (file_exists($deployed_dir)) {
                File::delete($deployed_dir);
            }
            symlink($dep_dir, $deployed_dir);
            // cleanup older deployments,older than 5 days
            if (!empty($options['remove_old_files'])) {
                $iterator = new \DirectoryIterator($all_deps_dir);
                foreach ($iterator as $filedir => $fileinfo) {
                    if ($fileinfo->isDir()) {
                        $filename = $fileinfo->getFilename();
                        // sanity check
                        if ($filename == $dep_id) {
                            continue;
                        }
                        if (strpos($filename, 'build.') === 0) {
                            if ($time - $fileinfo->getMTime() > 259200) {
                                $temp = $fileinfo->getPathname();
                                File::delete($temp);
                            }
                        }
                    }
                }
            }
            $result['success'] = true;
        } while (0);
        return $result;
    }
}
