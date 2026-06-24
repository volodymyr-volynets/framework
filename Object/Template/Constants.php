<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Template;

use Helper\File;

class Constants
{
    public const AUTO = 'auto';
    public const PHP = 'php';
    public const HTML = 'html';
    public const INI = 'ini';
    public const JSON = 'json';
    public const SQL = 'sql';
    public const CSS = 'css';
    public const SCSS = 'scss';
    public const REACT_JS = 'react_js';
    public const REACT_JSX = 'react_jsx';
    public const REACT_TS = 'react_ts';
    public const REACT_TSX = 'react_tsx';
    public const VUE = 'vue';

    public const TEMPLATE_TYPES = [
        // php/html processors
        self::PHP => ['name' => 'PHP Templates', 'extensions' => ['template.html', 'template.php'], 'processor' => '\Numbers\Templates\HTML\Base'],
        self::HTML => ['name' => 'HTML Templates', 'extensions' => ['template.html', 'template.php'], 'processor' => '\Numbers\Templates\HTML\Base'],
        // react processors
        self::REACT_JS => ['name' => 'JavaScript Templates', 'extensions' => ['template.react.js'], 'processor' => '\Numbers\Templates\ViteReact\Base'],
        self::REACT_JSX => ['name' => 'JSX Templates', 'extensions' => ['template.react.jsx'], 'processor' => '\Numbers\Templates\ViteReact\Base'],
        self::REACT_TS => ['name' => 'TypeScript Templates', 'extensions' => ['template.react.ts'], 'processor' => '\Numbers\Templates\ViteReact\Base'],
        self::REACT_TSX => ['name' => 'TSX Templates', 'extensions' => ['template.react.tsx'], 'processor' => '\Numbers\Templates\ViteReact\Base'],
        // vue processors
        self::VUE => ['name' => 'Vue Templates', 'extensions' => ['template.vue'], 'processor' => null],
        // css processors
        self::CSS => ['name' => 'CSS Templates', 'extensions' => ['template.css'], 'processor' => null],
        self::SCSS => ['name' => 'SCSS Templates', 'extensions' => ['template.scss'], 'processor' => null],
        // sql processors
        self::SQL => ['name' => 'SQL Templates', 'extensions' => ['template.sql', 'object.sql'], 'processor' => '\Numbers\Templates\SQL\Base'],
    ];

    public const TEMPLATE_EXTENSIONS = [
        'template.php' => 'php/html',
        'template.html' => 'php/html',
        // react
        'template.react.js' => 'react',
        'template.react.jsx' => 'react',
        'template.react.ts' => 'react',
        'template.react.tsx' => 'react',
        // vue
        'template.vue' => 'vue',
        // css
        'template.css' => 'css',
        'template.scss' => 'css',
        // sql
        'template.sql' => 'sql',
        'object.sql' => 'sql',
    ];

    /**
     * Check path extension
     *
     * @param string $type
     * @param string $path
     * @return bool
     */
    public static function templateConstantsCheckExtension(string $type, string $path): bool
    {
        $temp = explode('.template.', pathinfo($path)['basename'], 2);
        if (count($temp) == 2) {
            $extension = 'template.' . $temp[1];
        } else {
            $temp = explode('.object.', pathinfo($path)['basename'], 2);
            if (count($temp) == 2) {
                $extension = 'object.' . $temp[1];
            } else {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
            }
        }
        return in_array($extension, self::TEMPLATE_TYPES[$type]['extensions'] ?? []);
    }

    /**
     * Detect type
     *
     * @param string $path
     * @throws \Exception
     * @return string
     */
    public static function templateAutoDetectType(string $path): string
    {
        $temp = explode('.template.', pathinfo($path)['basename'], 2);
        if (count($temp) == 2) {
            $extension = 'template.' . $temp[1];
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
        }
        foreach (self::TEMPLATE_TYPES as $k => $v) {
            if (in_array($extension, $v['extensions'])) {
                return $k;
            }
        }
        throw new \Exception('Template: could not detect type from path!');
    }

    /**
     * Path to name
     *
     * @param string $path
     * @return string
     */
    public static function pathToName(string $path): string
    {
        $result = str_replace('.' . File::getTemplateExtension($path), '', $path);
        return trim(str_replace('/', '_', $result), '_');
    }
}
