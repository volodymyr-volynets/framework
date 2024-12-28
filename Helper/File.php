<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper;

class File
{
    /**
     * Write content to file and sets permissions
     *
     * @param string $filename
     * @param mixed $data
     * @param int $permission
     * @param int $flags
     * @param boolean $relative
     */
    public static function write($filename, $data, $permission = 0777, $flags = LOCK_EX, $relative = true)
    {
        // if we have relative path we convert it to full path
        if ($relative && $filename[0] == '.' && $filename[1] != '.') {
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
     * Write JSON
     *
     * @param string $filename
     * @param mixed $data
     * @param int $permission
     * @param int $flags
     * @param boolean $relative
     */
    public static function writeJSON($filename, $data, $permission = 0777, $flags = LOCK_EX, $relative = true)
    {
        return File::write($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $permission, $flags, $relative);
    }

    /**
     * Read file
     *
     * @param string $filename
     * @return string|boolean false
     */
    public static function read($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * Read json file
     *
     * @param string $filename
     * @param bool $assoc
     * @return array|string|boolean false
     */
    public static function readJSON($filename, $assoc = true)
    {
        $result = File::read($filename);
        if ($result !== false) {
            return json_decode($result, $assoc);
        }
        return $result;
    }

    /**
     * Create directory
     *
     * @param string $dir
     * @param octal $permission
     * @param array $options
     *		boolean skip_realpath
     * @return boolean
     */
    public static function mkdir($dir, $permission = 0777, $options = [])
    {
        if (empty($options['skip_realpath'])) {
            $dir = self::realpath($dir);
        }
        if (is_dir($dir)) {
            return true;
        }
        return mkdir($dir, (int) $permission, true);
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
    public static function delete(string $dir, array $options = []): bool
    {
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
                    if (!self::delete($dir . DIRECTORY_SEPARATOR . $v, $options)) {
                        return false;
                    }
                }
            }
            if (empty($options['only_contents'])) {
                return rmdir($dir);
            } else {
                return true;
            }
        } elseif (file_exists($dir)) {
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
     *		boolean only_file_names
     *		boolean strip_extension
     *		string files_start_with
     *		boolean sort_files
     * @return array
     */
    public static function iterate(string $dir, array $options = []): array
    {
        if (isset($options['only_extensions']) && !is_array($options['only_extensions'])) {
            $options['only_extensions'] = [$options['only_extensions']];
        }
        $result = [];
        $relative_path = realpath($dir);
        if (empty($options['recursive'])) {
            $iterator = new \DirectoryIterator($dir);
        } else {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        }
        foreach ($iterator as $v) {
            $filename = $v->getFilename();
            if (method_exists($v, 'isDot')) {
                if ($v->isDot()) {
                    continue;
                }
            } else {
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
            if (!empty($options['files_start_with']) && !str_starts_with($filename, $options['files_start_with'])) {
                continue;
            }
            if (!empty($options['strip_extension'])) {
                $filename = $v->getBasename('.' . $v->getExtension());
            }
            if (empty($options['extended'])) {
                if (!empty($options['only_file_names'])) {
                    $result[] = $filename;
                } else {
                    $result[] = $v->getPathname();
                }
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
                    'basename_no_extension' => $v->getBasename('.' . $v->getExtension()),
                    'filename' => $v->getFilename(),
                    'relative_directory' => self::iterateProcessPathInnerHelper($v->getPath(), $relative_path),
                    'relative_simple' => str_replace(rtrim($dir, '/') . DIRECTORY_SEPARATOR, '', $pathname),
                ];
            }
        }
        // sort
        if (empty($options['extended'])) {
            sort($result);
        } else {
            array_key_sort($result, ['pathname' => SORT_ASC]);
        }
        return $result;
    }

    /**
     * Function to remove absolute path
     *
     * @param string $dir
     * @param string $relative_path
     * @return string
     */
    private static function iterateProcessPathInnerHelper(string $dir, string $relative_path)
    {
        if ($relative_path == '') {
            return $dir;
        } else {
            $dir = trim2($dir, '^' . $relative_path, '');
            $dir = ltrim($dir, DIRECTORY_SEPARATOR);
            return $dir;
        }
    }

    /**
     * Copy file/directory
     *
     * @param string $source
     * @param string $destination
     * @param array $options
     *		array skip_files
     *		array skip_directories
     *      array skip_directories_level1
     * @return boolean
     */
    public static function copy(string $source, string $destination, array $options = []): bool
    {
        if (is_dir($source)) {
            // we need to skip directories
            if (!empty($options['skip_directories']) && in_array(basename($source), $options['skip_directories'])) {
                return true;
            }
            // level 1 directories
            if (!empty($options['skip_directories_level1']) && in_array(basename($source), $options['skip_directories_level1'])) {
                return true;
            }
            // open directory for reading
            $dir = opendir($source);
            if (!file_exists($destination)) {
                if (!self::mkdir($destination)) {
                    return false;
                }
            }
            while (($file = readdir($dir)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (empty($options['skip_files']) || (!empty($options['skip_files']) && !in_array($file, $options['skip_files']))) {
                    unset($options['skip_directories_level1']);
                    if (!self::copy($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file, $options)) {
                        return false;
                    }
                }
            }
            closedir($dir);
            return true;
        } else {
            // we might need to create a directory
            $dir = dirname($destination);
            if (!file_exists($dir)) {
                if (!self::mkdir($dir)) {
                    return false;
                }
            }
            // we need to remove a file if it exists
            if (file_exists($destination) || is_link($destination)) {
                unlink($destination);
            }
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
    public static function chmod(string $dir_or_file, int $permission = 0777): bool
    {
        if (is_dir($dir_or_file)) {
            $dir = opendir($dir_or_file);
            while (($file = readdir($dir)) !== false) {
                if ($file != '.' && $file != '..') {
                    if (!self::chmod($dir_or_file . DIRECTORY_SEPARATOR . $file, $permission)) {
                        return false;
                    }
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
    public static function replace(string $filename, string $find, string $replace): bool
    {
        if (!file_exists($filename)) {
            return false;
        }
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $k => $v) {
            if (stripos($v, $find) !== false) {
                $lines[$k] = $replace;
            }
        }
        return self::write($filename, implode("\n", $lines));
    }

    /**
     * Mime of a file
     *
     * @param string $filename
     * @return string
     */
    public static function mime(string $filename): string
    {
        $finfo = finfo_open(FILEINFO_MIME); // return mime type ala mimetype extension
        $mime = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mime;
    }

    /**
     * Temporary directory
     *
     * @param string $filename
     * @return string
     */
    public static function tempDirectory(string $filename = ''): string
    {
        $path = \Application::get('documents.default.temp_dir');
        $host_parts = \Request::hostParts(\Application::get('phpunit.tenant_default_url'));
        $dir = $path . implode('.', $host_parts);
        if (!file_exists($dir)) {
            self::mkdir($dir, 0777);
            self::chmod($dir, 0777);
        }
        return $dir . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Generate temporary file name
     *
     * @param string $extension
     * @param boolean $in_temp_directory
     * @return string
     */
    public static function generateTempFileName(string $extension = '', bool $in_temp_directory = false): string
    {
        $result = uniqid('Numbers_Temp_') . '_' . \Format::now('unix');
        if (!empty($extension)) {
            $result .= '.' . $extension;
        }
        if ($in_temp_directory) {
            return self::tempDirectory($result);
        } else {
            return $result;
        }
    }

    /**
     * Real path
     *
     * @param string $path
     * @return string
     */
    public static function realpath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $first = ($path[0] == DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $result = [];
        foreach ($parts as $v) {
            if ($v == '.') {
                continue;
            }
            if ($v == '..') {
                array_pop($result);
            } else {
                $result[] = $v;
            }
        }
        return $first . implode(DIRECTORY_SEPARATOR, $result);
    }
}
