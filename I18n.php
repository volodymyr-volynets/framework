<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class I18n
{
    /**
     * Initialized
     *
     * @var boolean
     */
    public static $initialized = false;

    /**
     * Options
     *
     * @var array
     */
    public static $options = [];

    /**
     * Initializing i18n
     *
     * @param array $options
     */
    public static function init($options = [])
    {
        $result = [
            'success' => false,
            'error' => [],
            'group_id' => null,
        ];
        // initialize the module
        $i18n = Application::get('flag.global.i18n') ?? [];
        // settings from user account
        $user_settings = User::get('internalization');
        if (!empty($user_settings)) {
            foreach ($user_settings as $k => $v) {
                if (empty($v)) {
                    unset($user_settings[$k]);
                }
            }
        }
        if (!empty($options['skip_user_settings'])) {
            $user_settings = [];
        }
        $i18n = array_merge_hard($i18n, $user_settings, $options ?? []);
        if (!empty($i18n['submodule'])) {
            // check if backend has been enabled
            if (!Application::get($i18n['submodule'], ['submodule_exists' => true])) {
                throw new Exception('You must enable ' . $i18n['submodule'] . ' first!');
            }
            self::$options = Factory::model($i18n['submodule'], true)->init($i18n);
            //\Application::set('flag.global.i18n', self::$options);
            //\Session::set('numbers.user.i18n.language_code', $i18n['language_code']);
            $result['success'] = self::$initialized = true;
            // set cookie
            $group_id = Application::get('flag.global.__in_group_id') ?? $i18n['group_id'] ?? 1;
            Application::set('flag.global.__in_group_id', $group_id);
            setcookie("__in_group_id", $group_id, 0, '/');
            $result['group_id'] = $group_id;
        }
        return $result;
    }

    /**
     * Destroy
     */
    public static function destroy()
    {
        if (!empty(self::$options['submodule'])) {
            Factory::model(self::$options['submodule'], true)->destroy();
        }
    }

    /**
     * Get translation
     *
     * @param string $i18n
     * @param string $text
     * @param array $options
     * @return string
     */
    public static function get($i18n, $text, $options = [])
    {
        if (is_array($text)) {
            $result = [];
            foreach ($text as $v) {
                $result[] = self::getOne($i18n, $v, $options);
            }
            return implode('', $result);
        } else {
            return self::getOne($i18n, $text, $options);
        }
        return $text;
    }

    /**
     * Get one
     *
     * @param int $i18n
     * @param string $text
     * @param array $options
     * @return type
     */
    public static function getOne($i18n, $text, $options = [])
    {
        // get text from submodule
        if (!empty(self::$options['submodule'])) {
            $text = Factory::model(self::$options['submodule'], true)->get($i18n, $text, $options);
        }
        // if we need to handle replaces, for example:
        //		"Error occured on line [line_number]"
        // important: replaces must be translated/formatted separatly
        if (!empty($options['replace'])) {
            foreach ($options['replace'] as $k => $v) {
                $text = str_replace($k, $v ?? '', $text . '');
            }
        }
        return $text;
    }

    /**
     * Localize
     *
     * @param string|array $key
     * @param mixed $text
     * @param array $options
     * @return string
     */
    public static function loc(string|array $key, mixed $text = '', array $options = [])
    {
        // get text from submodule
        if (!empty(self::$options['submodule'])) {
            $text = Factory::model(self::$options['submodule'], true)->loc($key, $text, $options);
        }
        return $text;
    }

    /**
     * Check if language is RTL or return direction
     *
     * @param boolean $flag
     * @return mixed
     */
    public static function rtl($flag = true)
    {
        if ($flag) {
            return !empty(self::$options['rtl']);
        } else {
            return !empty(self::$options['rtl']) ? ' dir="rtl" ' : ' dir="ltr" ';
        }
    }

    /**
     * Change I/N group
     *
     * @param int $group_id
     */
    public static function changeGroup(int $group_id)
    {
        Application::set('flag.global.__in_group_id', $group_id);
        I18n::init();
        setcookie("__in_group_id", $group_id, 0, '/');
    }

    /**
     * Process message
     *
     * @param string $message
     * @param array|json $replace
     * @return string
     */
    public static function processMessage(string $message, $replace): string
    {
        if (is_json($replace)) {
            $replace = json_decode($replace, true);
        }
        foreach ($replace as $k => $v) {
            if (is_string($v)) {
                if (substr($v, 0, 2) == '~~') {
                    $v = substr($v, 2);
                } else {
                    $v = i18n(null, $v);
                }
            } else {
                $v = Format::id($v);
            }
            $replace[$k] = $v;
        }
        return i18n(null, $message, ['replace' => $replace]);
    }

    /**
     * Translate and sort an array
     *
     * @param array $data
     * @param bool $sort
     */
    public static function translateArray(array & $data, bool $sort = false)
    {
        foreach ($data as $k => $v) {
            $data[$k]['name'] = i18n(null, $v['name']);
        }
        if ($sort) {
            array_key_sort($data, ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
        }
    }

    /**
     * Replace HTML tags
     *
     * @param string $html
     * @return string
     */
    public static function htmlReplaceTags(string $html): string
    {
        $matches = [];
        preg_match_all('@<(i18n)>(.+?)</\1>@is', $html, $matches, PREG_PATTERN_ORDER);
        //print_r2($matches);
        if (!empty($matches[2])) {
            foreach ($matches[2] as $k => $v) {
                $html = str_replace($matches[0][$k], i18n(null, $v), $html);
            }
        }
        return $html;
    }

    /**
     * Options groups
     *
     * @param array $options
     * @return array
     */
    public static function optionsGroups(array $options = []): array
    {
        return Factory::model(self::$options['submodule'], true)->optionsGroups($options);
    }
}
