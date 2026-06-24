<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Ob;
use Object\ACL\Resources;

#[AllowDynamicProperties]
class Layout extends View
{
    /**
     * Title override
     *
     * @var string
     */
    public static $title_override;

    /**
     * Icon override
     *
     * @var string
     */
    public static $icon_override;

    /**
     * Version to be used when rendering JS/CSS links
     *
     * @var int
     */
    public static $version;

    /**
     * Onload JavaScript
     *
     * @var string
     */
    public static $onload = '';
    public static $onload_first = '';

    /**
     * JavaScript data would be here
     *
     * @var array
     */
    private static $js_data = [];

    /**
     * HTML to be added last to the page
     *
     * @var string
     */
    public static $onhtml = '';

    /**
     * Template settings
     *
     * @var array
     */
    protected static array $template_settings = [];

    /**
     * Get application version
     *
     * @return int
     */
    public static function getVersion()
    {
        if (!empty(getenv('NF_IS_CONTAINER'))) {
            self::$version = getenv('NF_IS_CONTAINER');
        } elseif (empty(self::$version)) {
            $filename = Application::get(['application', 'path_full']) . (Application::isDeployed() ? '../../../deployed' : '../../deployed');
            self::$version = filemtime($filename);
        }
        return self::$version;
    }

    /**
     * Add css file to layout
     *
     * @param string $css
     * @param int $sort
     */
    public static function addCss(string $css, int $sort = 0)
    {
        Application::set(array('layout', 'css', $css), $sort);
    }

    /**
     * Render css files
     *
     * @param array $options
     *	boolean return_list
     * @return string
     */
    public static function renderCss($options = [])
    {
        $result = '';
        $list = [];
        $css = Application::get(array('layout', 'css'));
        if (!empty($css)) {
            asort($css);
            foreach ($css as $k => $v) {
                $script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::getVersion();
                $list[] = $script;
                $result .= '<link href="' . $script . '" rel="stylesheet" type="text/css" />';
            }
        }
        // list is needed for ajax form reloads
        if (!empty($options['return_list'])) {
            return $list;
        }
        return $result;
    }

    /**
     * Add JavaScript file to the layout
     *
     * @param string $js
     * @param int $sort
     */
    public static function addJs(string $js, int $sort = 0, array $options = [])
    {
        $js = str_replace('\\', '/', $js);
        Application::set(['layout', 'js', $js], $sort);
        if (!empty($options)) {
            Application::set(['layout', 'js_options', $js], $options);
        }
    }

    /**
     * Render javascript files
     *
     * @param array $options
     *          boolean return_list
     *          boolean return_extended_list
     * @return string|array
     */
    public static function renderJs(array $options = []): string|array
    {
        $result = '';
        $list = [];
        $extended_list = [];
        $js = Application::get(['layout', 'js']);
        if (!empty($js)) {
            asort($js);
            foreach ($js as $k => $v) {
                $js_options = Application::get(['layout', 'js_options', $k]) ?? [];
                $js_options['type'] ??= 'text/javascript';
                if ($js_options['type'] == 'module') {
                    $script = $k;
                } else {
                    $script = $k . (strpos($k, '?') !== false ? '&' : '?') . self::getVersion();
                }
                $extended_list[] = ['type' => $js_options['type'], 'src' => $script, 'is_entry' => $js_options['is_entry'] ?? false];
                $list[] = $script;
                if (empty($js_options)) {
                    $js_options['crossorigin'] = 'anonymous';
                }
                $js_options['src'] = $script;
                $result .= HTML::script($js_options);
            }
        }
        // extended list
        if (!empty($options['return_extended_list'])) {
            return $extended_list;
        }
        // list is needed for ajax form reloads
        if (!empty($options['return_list'])) {
            return $list;
        }
        return $result;
    }

    /**
     * Onload js
     *
     * @param string $js
     * @param boolean $first
     */
    public static function onLoad(string $js, bool $first = false)
    {
        if (!$first) {
            self::$onload .= $js;
        } else {
            self::$onload_first .= $js;
        }
    }

    /**
     * OnHTML
     *
     * @param string $html
     */
    public static function onHtml($html)
    {
        self::$onhtml .= $html;
    }

    /**
     * Render onload js
     *
     * @param boolean $first
     * @return string
     */
    public static function renderOnLoad()
    {
        if (!empty(self::$onload) || !empty(self::$onload_first)) {
            return HTML::script(['value' => '$(document).ready(function(){ ' . self::$onload_first . self::$onload . ' });']);
        }
    }

    /**
     * Render js head, currently loads local and session storages
     *
     * @return string
     */
    public static function renderJsHead(): string
    {
        $local_storage = WebStorage::renderJavascriptStatic(WebStorage::LOCAL_STORAGE);
        $session_storage = WebStorage::renderJavascriptStatic(WebStorage::SESSION_STORAGE);
        if (!empty($session_storage) || !empty($local_storage)) {
            return HTML::script(['value' => $session_storage . $local_storage]);
        }
        return '';
    }

    /**
     * Add array to JavaScript data
     *
     * @param array $data
     */
    public static function jsData($data)
    {
        self::$js_data = array_merge_hard(self::$js_data, $data);
    }

    /**
     * Render JavaScript data
     */
    public static function renderJsData()
    {
        return HTML::script(['value' => '$(document).ready(function(){ var numbers_js_data = ' . json_encode(self::$js_data) . '; $.extend(true, Numbers, numbers_js_data); numbers_js_data = null; });']);
    }

    /**
     * Render title
     *
     * @return string
     */
    public static function renderTitle()
    {
        $loc = 'NF.System.' . String2::createStatic(Application::$controller->title)->englishOnly(true)->toString();
        $title = self::$title_override ?? loc($loc, Application::$controller->title) ?? Application::$controller->title;
        if (!empty($title)) {
            $icon = self::$icon_override ?? Application::$controller->icon ?? null;
            return (!empty($icon) ? (HTML::icon(['type' => $icon]) . ' ') : '') . $title;
        }
    }

    /**
     * Render title name only (not translated)
     *
     * @return string
     */
    public static function renderTitleNameOnly()
    {
        return self::$title_override ?? Application::$controller->title;
    }

    /**
     * Render document title
     *
     * @return string
     */
    public static function renderDocumentTitle()
    {
        $title = trim(strip_tags(self::renderTitle() ?? ''));
        return '<title>' . $title . '</title>';
    }

    /**
     * Add messages
     *
     * @param string|array $msg
     * @param string $type
     *		type one of:
     *			danger
     *			warning
     *			success
     *			info
     *			other
     * @param boolean $postponed
     */
    public static function addMessage($msg, string $type = 'danger', bool $postponed = false)
    {
        if (!$postponed) {
            if (is_array($msg)) {
                foreach ($msg as $k => $v) {
                    Application::set(['messages', $type], $v, ['append' => true]);
                }
            } else {
                Application::set(['messages', $type], $msg, ['append' => true]);
            }
        } else { // postponed messages go into session
            if (is_array($msg)) {
                foreach ($msg as $k => $v) {
                    Session::set(['numbers', 'messages', $type], $v, ['append' => true]);
                }
            } else {
                Session::set(['numbers', 'messages', $type], $msg, ['append' => true]);
            }
        }
    }

    /**
     * Render messages
     *
     * @return string
     */
    public static function renderMessages(): string
    {
        $result = '';
        // we need to see if we have postponed messages and render them first
        $postponed = Session::get(['numbers', 'messages']);
        if (!empty($postponed)) {
            Session::set(['numbers', 'messages'], []);
            foreach ($postponed as $k => $v) {
                $result .= HTML::message(['options' => $v, 'type' => $k]);
            }
        }
        // regular messages
        $messages = Application::get(array('messages'));
        if (!empty($messages)) {
            foreach ($messages as $k => $v) {
                $result .= HTML::message(['options' => $v, 'type' => $k]);
            }
        }
        return $result;
    }

    /**
     * Add action
     *
     * @param string $code
     * @param array $action
     */
    public static function addAction(string $code, array $action)
    {
        $action['order'] = $action['order'] ?? 0;
        Application::set(array('layout', 'actions', $code), $action);
    }

    /**
     * Render actions
     *
     * @return string
     */
    public static function renderActions(): string
    {
        $result = '';
        $data = Application::get(array('layout', 'actions'));
        if (!empty($data)) {
            // sorting first
            array_key_sort($data, ['order' => SORT_ASC], ['order' => SORT_NUMERIC]);
            // looping through data and building html
            $temp = [];
            foreach ($data as $k => $v) {
                if (empty($v)) {
                    continue;
                }
                $icon = !empty($v['icon']) ? (HTML::icon(['type' => $v['icon']]) . ' ') : '';
                $onclick = !empty($v['onclick']) ? $v['onclick'] : '';
                $value = $v['value'] ?? '';
                $href = $v['href'] ?? 'javascript:void(0);';
                $temp[] = HTML::a(['value' => $icon . $value, 'href' => $href, 'onclick' => $onclick, 'title' => $v['title'] ?? '']);
            }
            $result = implode(' ', $temp);
        }
        return $result;
    }

    /**
     * Render bread crumbs
     *
     * @return string
     */
    public static function renderBreadcrumbs(): string
    {
        if (!empty(Application::$controller->breadcrumbs)) {
            $temp = array_slice(Application::$controller->breadcrumbs, 1, 2);
            $keys = [];
            foreach ($temp as $v) {
                $keys[] = $v;
                $keys[] = 'options';
            }
            $data = Resources::getStatic('menu', 'primary');
            // submenu is available only when we have breadcrumbs and menu is there
            $submenu = '';
            if (!empty($keys) && !empty($data[200])) {
                $submenu_data = array_key_get($data[200], $keys);
                $submenu = [];
                if (is_array($submenu_data)) {
                    array_key_sort($submenu_data, ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
                    foreach ($submenu_data as $k => $v) {
                        $submenu[] = HTML::a([
                            'href' => Request::fixUrl($v['url'], $v['template']),
                            'value' => HTML::icon(['type' => $v['icon']]) . ' ' . $v['name_loc'],
                        ]);
                        if (!empty($v['options'])) {
                            array_key_sort($v['options'], ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
                            foreach ($v['options'] as $k2 => $v2) {
                                $submenu[] = HTML::a([
                                    'href' => Request::fixUrl($v2['url'], $v2['template']),
                                    'value' => '&nbsp;&nbsp;&nbsp;' . HTML::icon(['type' => $v2['icon']]) . ' ' . $v2['name_loc'],
                                ]);
                                if (!empty($v2['options'])) {
                                    array_key_sort($v2['options'], ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
                                    foreach ($v2['options'] as $k3 => $v3) {
                                        $submenu[] = HTML::a([
                                            'href' => Request::fixUrl($v3['url'], $v3['template']),
                                            'value' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . HTML::icon(['type' => $v3['icon']]) . ' ' . $v3['name_loc'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                    $submenu = HTML::popover([
                        'id' => 'breadcrumbs_submenu',
                        'value' => HTML::icon(['type' => 'fa-solid fa-sticky-note']),
                        'content' => implode(HTML::br(), $submenu),
                        'style' => 'overflow-y: scroll;'
                    ]);
                }
            }
            // quick actions
            $quick_actions = [];
            if (!empty($data['quick_actions'])) {
                foreach ($data['quick_actions'] as $k => $v) {
                    $quick_actions[] = HTML::a([
                        'href' => Request::fixUrl($v['url'], $v['template']),
                        'value' => HTML::icon(['type' => $v['icon']]) . ' ' . $v['name_loc'],
                    ]);
                    foreach ($v['options'] as $k2 => $v2) {
                        $quick_actions[] = HTML::a([
                            'href' => Request::fixUrl($v2['url'], $v2['template']),
                            'value' => '&nbsp;&nbsp;&nbsp;' . HTML::icon(['type' => $v2['icon']]) . ' ' . $v2['name_loc'],
                        ]);
                    }
                }
                $quick_actions = HTML::popover([
                    'id' => 'breadcrumbs_quick_actions',
                    'value' => HTML::icon(['type' => 'fa-regular fa-hand-point-right']),
                    'content' => implode(HTML::br(), $quick_actions),
                    'style' => 'overflow-y: scroll;'
                ]);
            }
            $breadcrumbs = [];
            foreach (Application::$controller->breadcrumbs as $k => $v) {
                $loc = 'NF.System.' . String2::createStatic($v)->englishOnly(true)->toString();
                $breadcrumbs[] = loc($loc, $v);
            }
            if (!empty($submenu)) {
                $breadcrumbs[] = $submenu;
            }
            if (!empty($quick_actions)) {
                $breadcrumbs[] = $quick_actions;
            }
            return HTML::breadcrumbs($breadcrumbs);
        } else {
            return '';
        }
    }

    /**
     * Render as content type, non HTML output should go though this function
     *
     * @param mixed $data
     * @param string $content_type
     * @param array $options
     *		output_file_name
     */
    public static function renderAs($data, string $content_type, array $options = [])
    {
        // clean up output buffer
        Ob::cleanAll();
        Application::set('flag.global.__content_type', $content_type);
        Application::set('flag.global.__skip_layout', 1);
        Application::set('flag.global.__already_rendered', 1);
        header("Content-type: " . $content_type);
        if (!empty($options['output_file_name'])) {
            header('Content-Disposition: attachment; filename=' . $options['output_file_name']);
        }
        $options['extension'] = ($options['extension'] ?? '');
        Log::add([
            'type' => 'Layout',
            'only_channel' => 'default',
            'message' => 'Content type generated!',
            'other' => 'Content type: ' . $content_type . "\n" . 'Status: ' . ($options['status'] ?? 'Unknown'),
        ]);
        // response code
        if (isset($options['status'])) {
            http_response_code($options['status']);
        }
        // main switch
        switch ($content_type . $options['extension']) {
            case 'application/json':
                header('Connection: close');
                echo json_encode($data);
                break;
            case 'application/xml':
                if (is_array($data)) {
                    echo array2xml($data);
                } else {
                    echo $data;
                }
                break;
            case 'text/html':
                Ob::start();
                if (!empty($options['layout_path'])) {
                    require($options['layout_path']);
                } else {
                    require(Application::get(['application', 'path_full']) . 'Layout/blank.html');
                }
                echo str_replace([
                    '<!-- [numbers: document title] -->',
                    '<!-- [numbers: document body] -->',
                    '<!-- [numbers: javascript links] -->',
                    '<!-- [numbers: css links] -->',
                ], [
                    Layout::renderDocumentTitle(),
                    $data,
                    Layout::renderJs(),
                    Layout::renderCss(),
                ], Ob::clean());
                break;
            case 'text/htmlemail':
                Ob::start();
                require(Application::get(['application', 'path_full']) . 'Layout/' . Application::get('application.layout.email') . '.html');
                echo str_replace([
                    '<!-- [numbers: document title] -->',
                    '<!-- [numbers: document body] -->'
                ], [
                    '<title>' . ($options['title'] ?? '') . '</title>',
                    $data
                ], Ob::clean());
                break;
            case 'text/htmlplain':
            case 'text/plain':
            default:
                echo $data;
        }
        session_write_close();
        Log::deliver();
        Application::set('flag.global.__ajax_call_processed', 1);
        exit;
    }

    /**
     * Include all media files for controller
     *
     * @param string $path
     * @param string $controller
     * @param string $view
     * @param string $class
     */
    public static function includeMedia($path, $controller, $view, $class)
    {
        // generating a list of extensions
        $valid_extensions = ['js', 'css'];
        if (Application::get('dep.submodule.numbers.frontend.media.scss')) {
            $valid_extensions[] = 'scss';
        }
        // we need to fix path for submodules
        $path_fixed = str_replace('/', '_', $path);
        $path_js = str_replace('_' . $controller, '', $class) . '_';
        if (substr($path, 0, 8) == 'numbers/') {
            $path = '../libraries/vendor/' . $path;
        }
        //$path = Application::get(['application', 'path_full']) . $path;
        // build an iterator
        $iterator = new FilesystemIterator($path);
        $filter = new RegexIterator($iterator, '/' . $controller . '(.' . $view . ')?.(' . implode('|', $valid_extensions) . ')$/');
        $file_list = [];
        // iterating
        foreach ($filter as $v) {
            $temp = $v->getFilename();
            $extension = pathinfo($temp, PATHINFO_EXTENSION);
            // we need to sort in a way that view files are included second
            if ($controller . '.' . $extension == $temp) {
                $sort = 1000;
            } else {
                $sort = 2000;
            }
            $new = '/numbers/media_generated/application_' . $path_js . $temp;
            if ($extension == 'js') {
                self::add_js($new, $sort);
            } elseif ($extension == 'css') {
                self::add_css($new, $sort);
            } elseif ($extension == 'scss') {
                $new .= '.css';
                self::add_css($new, $sort);
            }
            // adding media files to application for further reporting
            Application::set(['application', 'loaded_classes', $class, 'media'], ['file' => $temp, 'full' => $new], ['append' => true]);
        }
    }

    /**
     * Get template settings
     *
     * @return array
     */
    public static function getTemplateSettings(): array
    {
        return self::$template_settings;
    }

    /**
     * Set template settings
     *
     * @param array|string $template_strategies
     * @param array $template_options
     * @return void
     */
    public static function setTemplateSettings(array|string $strategies, array $options = []): void
    {
        if (is_string($strategies)) {
            $strategies = [$strategies];
        }
        $templates = Application::get('application.layouts');
        $existing = null;
        $template_name = null;
        foreach ($strategies as $v) {
            if (!empty($templates[$v])) {
                $existing = $templates[$v];
                $existing['template'] = $v;
                $template_name = $v;
                break;
            }
        }
        // if we did not find a template we set default
        if (empty($template_name)) {
            throw new Exception('Layout: Could not find a template from strategies!');
        }
        self::$template_settings['template'] = $existing;
        self::$template_settings['strategies'] = $strategies;
        self::$template_settings['options'] = $options;
        Application::set('application.template.name', $template_name);
        // title
        if (isset($options['title'])) {
            Application::set("'application.layouts.{$template_name}.title'", $options['title']);
            self::$template_settings['template']['title'] = $options['title'];
        }
        // menu
        if (isset($options['menu'])) {
            Application::set("'application.layouts.{$template_name}.menu'", $options['menu']);
            self::$template_settings['template']['menu'] = $options['menu'];
        }
        // footer
        if (isset($options['footer'])) {
            Application::set("'application.layouts.{$template_name}.footer'", $options['footer']);
            self::$template_settings['template']['footer'] = $options['footer'];
        }
        // site
        if (isset($options['site'])) {
            Application::set("'application.layouts.{$template_name}.site'", $options['site']);
            self::$template_settings['template']['site'] = $options['site'];
        }
        // double check manifest
        if (!empty(self::$template_settings['template']['site'])) {
            $site_name = self::$template_settings['template']['site'];
            $manifest = Application::get("application.sites.{$site_name}.manifest");
            $public_dir = Application::get("application.sites.{$site_name}.public_dir");
            if (empty($manifest)) {
                throw new Exception("Layout: unable to locate manifest file!");
            }
            Application::set("'application.layouts.{$template_name}.manifest'", $manifest);
            self::$template_settings['template']['manifest'] = $manifest;
            self::$template_settings['template']['public_dir'] = $public_dir;
        }
        // no styles
        if (self::$template_settings['template']['title'] == 'no_styles' && !isset($options['skip_title'])) {
            $options['skip_title'] = true;
        }
        if (self::$template_settings['template']['menu'] == 'no_styles' && !isset($options['skip_menu'])) {
            $options['skip_menu'] = true;
        }
        if (self::$template_settings['template']['footer'] == 'no_styles' && !isset($options['skip_footer'])) {
            $options['skip_footer'] = true;
        }
        if (self::$template_settings['template']['extra_forms'] == 'no_styles' && !isset($options['skip_extra_forms'])) {
            $options['skip_extra_forms'] = true;
        }
        // skips from layout
        if (isset($options['skip_menu'])) {
            Application::set('flag.global.__skip_menu', $options['skip_menu']);
        }
        if (isset($options['skip_footer'])) {
            Application::set('flag.global.__skip_footer', $options['skip_footer']);
        }
        if (isset($options['skip_title'])) {
            Application::set('flag.global.__skip_title', $options['skip_title']);
        }
        if (isset($options['skip_extra_forms'])) {
            Application::set('flag.global.__skip_extra_forms', $options['skip_extra_forms']);
        }
    }

    /**
     * Render layout template
     *
     * @param array $options
     * @return string
     */
    public static function renderLayoutTemplate(array $options = []): string
    {
        // if view does not specify a template or have it set in url
        if (empty(self::$template_settings['template']) || Application::get('application.template.__is_set_in_url')) {
            self::setTemplateSettings(Application::get('application.template.name'), $options);
        }
        $options = array_merge_hard(self::$template_settings['options'], $options);
        return Template::renderStatic(self::$template_settings['template']['layout_type'], self::$template_settings['template']['layout_path'], $options);
    }

    /**
     * Add style
     *
     * @param string $style
     * @return void
     */
    public static function style(string $style): void
    {
        self::$onhtml .= HTML::style(['value' => $style]);
    }
}
