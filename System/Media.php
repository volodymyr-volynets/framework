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

class Media
{
    /**
     * Serve js and/or css files, mostly used in development
     *
     * @param string $filename
     */
    public static function serveMediaIfExists($filename, $application_path, $as_string = false)
    {
        // we need to remove question mark and all after it
        if (strpos($filename, '?') !== false) {
            $temp = explode('?', $filename);
            $filename = $temp[0];
        }
        // generated files first
        if (strpos($filename, '/numbers/media_generated/') === 0) {
            $filename = str_replace('/numbers/media_generated/application_', '', $filename);
            $filename = $application_path . str_replace('_', '/', $filename);
        } elseif (strpos($filename, '/numbers/media_submodules/') === 0) {
            $temp = str_replace('/numbers/media_submodules/', '', $filename);
            $temp = str_replace('_', '/', $temp);
            if (strpos($temp, 'Numbers') === 0) {
                $filename = './../libraries/private/' . $temp;
                if (!file_exists($filename)) {
                    $filename = './../libraries/vendor/' . $temp;
                }
            } else {
                $filename = './../libraries/private/' . $temp;
            }
        } else {
            // we must return, do not exit !!!
            return;
        }
        // check if file exists on file system
        if (!file_exists($filename)) {
            return;
        }
        // as string
        if ($as_string) {
            return file_get_contents($filename);
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
                    } elseif (\Application::get('dep.submodule.numbers.frontend.media.scss')) {
                        /*
                        $temp = numbers_frontend_media_scss_base::serve($new);
                        if ($temp['success']) {
                            echo $temp['data'];
                        }
                        */
                    }
                }
                exit;
            }
        } else { // other files that exist on file system
            $mime = mime_content_type($filename);
            header('Content-type: ' . $mime);
            echo file_get_contents($filename);
            exit;
        }
    }
}
