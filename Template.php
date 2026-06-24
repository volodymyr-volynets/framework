<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\Template\Constants;
use Helper\File;

class Template extends Constants
{
    /**
     * @var string|null
     */
    protected ?string $type;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * Cached data
     *
     * @var array
     */
    protected static array $cached_data = [];

    /**
     * Constructor
     *
     * @param string $type
     */
    public function __construct(?string $type = null)
    {
        $this->type = $type;
    }

    /**
     * Assign
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function assign(mixed $key, mixed $value)
    {
        array_key_set($this->options, $key, $value);
    }

    /**
     * Determine type
     *
     * @param mixed $path
     * @throws Exception
     * @return string
     */
    public function determineType($path): string
    {
        $extension = 'template.' . explode('.template.', $path)[1];
        foreach (self::TEMPLATE_TYPES as $k => $v) {
            if (in_array($extension, $v['extensions'])) {
                return $k;
            }
        }
        throw new Exception('Template: not valid type and extension!');
    }

    /**
     * Render
     *
     * @param string $path
     * @param array $options
     * @return string
     */
    public function render(string $path, array $options = []): string
    {
        $type = $this->type ?? $this->determineType($path);
        $options = array_merge($this->options, $options);
        return self::renderStatic($type, $path, $options);
    }

    /**
     * Render (static)
     *
     * @param string|null $type
     *      self::AUTO or null means we need to detect
     * @param string $path
     * @param array $options
     * @return string
     */
    public static function renderStatic(string|null $type, string $path, array $options = []): string
    {
        // auto detect type
        if ($type == self::AUTO || !isset($type)) {
            $type = self::templateAutoDetectType($path);
        }
        // check for valid extension
        if (!self::templateConstantsCheckExtension($type, $path)) {
            throw new Exception('Template: not valid extension!');
        }
        // check if backend has been enabled
        $class = self::TEMPLATE_TYPES[$type]['processor'];
        if (empty($class) || !Application::get($class, ['submodule_exists' => true])) {
            throw new Exception('Template: you must enable ' . ($class ?? $type) . ' first!');
        }
        // find support template class
        $template_class_name = explode('.template.', $path)[0];
        $template_class_filename = explode('.template.', $path)[0] . '.template.class.php';
        $template_class_name .= 'TemplateClass';
        $template_class_name = str_replace('/', '\\', $template_class_name);
        $template_path = File::path($template_class_filename);
        if ($template_path !== false) {
            require_once($template_path);
            $template_class_object = new $template_class_name();
            $options = $template_class_object->process($options);
        }
        // load
        if (!empty($options['__load'])) {
            $options['__loaded'] = [];
            $all_loads = [];
            require_if_exists('./Miscellaneous/Loads/AllLoads.php', false, $all_loads);
            foreach ($options['__load'] as $v) {
                foreach ($all_loads[$v] as $k2 => $v2) {
                    foreach ($v2 as $k3 => $v3) {
                        $model = Factory::model($k3, true);
                        $options['__loaded'][$v][$k2] = call_user_func_array([$model, $v3], []);
                    }
                }
            }
            unset($options['__load']);
        }
        // local storage
        if (!empty($options['__localStorage'])) {
            foreach ($options['__localStorage'] as $k => $v) {
                $values = array_key_get($options['__loaded'], $k);
                foreach ($values as $k2 => $v2) {
                    $key = '';
                    if ($v) {
                        $key .= $v . '_';
                    }
                    $key .= $k2;
                    WebStorage::setStatic($key, $v2);
                }
            }
            unset($options['__localStorage']);
        }
        // ts
        $options['__ts'] = time();
        // main switch per type
        switch ($type) {
            case self::PHP:
            case self::HTML:
            case self::SQL:
                // create new class
                $template = Factory::model($class, true);
                return $template->render($type, $path, $options);
            case self::REACT_JS:
            case self::REACT_JSX:
            case self::REACT_TS:
            case self::REACT_TSX:
                // create new class
                $template = Factory::model($class, true);
                return $template->render($type, $path, $options);
            default:
                throw new Exception('Template: unknown template type!');
        }
    }

    /**
     * Set layout yield
     *
     * @param string $section
     * @param string|null $content
     * @param bool $append
     * @return void
     */
    public static function setLayoutYield(string $section, string|null $content, bool $append = true): void
    {
        if (!isset(self::$cached_data['yield'][$section])) {
            self::$cached_data['yield'][$section] = '';
        }
        if ($append) {
            self::$cached_data['yield'][$section] .= $content;
        } else {
            self::$cached_data['yield'][$section] = $content . self::$cached_data['yield'][$section];
        }
    }

    /**
     * Get layout yield
     *
     * @param string $section
     * @return string|null
     */
    public static function getLayoutYield(string $section): string|null
    {
        return self::$cached_data['yield'][$section] ?? null;
    }
}
