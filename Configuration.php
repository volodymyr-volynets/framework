<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Configuration
{
    public const OPENING_CLOSING_ENV_VARIABLE_REGEX = '/\$\(([^\)].*)\)/';

    /**
     * Configuration link
     *
     * @var string
     */
    protected $configuration_link;

    /**
     * Configuration object
     *
     * @var object
     */
    protected $object;

    /**
     * Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructing configuration object
     *
     * @param string $db_link
     * @param string $class
     * @param array $options
     */
    public function __construct($configuration_link = null, $class = null, $options = [])
    {
        // if we need to use default link from application
        if (empty($configuration_link)) {
            $configuration_link = Application::get('flag.global.default_configuration_link');
            if (empty($configuration_link)) {
                throw new Exception('You must specify database link and/or class!');
            }
        }
        $this->configuration_link = $configuration_link;
        // get object from factory
        $temp = Factory::get(['configuration', $configuration_link]);
        // if we have class
        if (!empty($class) && !empty($configuration_link)) {
            // check if backend has been enabled
            if (!Application::get($class, ['submodule_exists' => true])) {
                throw new Exception('You must enable ' . $class . ' first!');
            }
            // if we are replacing database connection with the same link we
            // need to manually close database connection
            if (!empty($temp['object'])) {
                $object = $temp['object'];
                unset($this->object);
            }
            // creating new class
            $this->object = new $class($configuration_link, $options);
            // putting every thing into factory
            Factory::set(['configuration', $configuration_link], [
                'object' => $this->object,
                'class' => $class,
                'options' => $options,
            ]);
            // set options without credentials
            $this->options = $options;
        } elseif (!empty($temp['object'])) {
            $this->object = & $temp['object'];
        } else {
            throw new Exception('You must specify configuration link and/or class!');
        }
    }

    /**
     * Environment
     *
     * @param array|string|null $environments
     * @return string|bool
     */
    public static function environment(array|string|null $environments = null): string|bool
    {
        if ($environments === null) {
            return Application::get('environment');
        } else {
            if (!is_array($environments)) {
                $environments = [$environments];
            }
            return in_array(Application::get('environment'), $environments);
        }
    }

    /**
     * Get
     *
     * @param string|array $keys
     * @param mixed $default
     * @return mixed
     */
    public function get(string|array $keys, mixed $default = null): mixed
    {
        if ($this->configuration_link == 'default') {
            return Application::get($keys) ?? $default;
        } else {
            if (is_string($keys)) {
                $keys = [$keys];
            }
            if (!empty($this->options['prepend_keys'])) {
                array_unshift($keys, $this->options['prepend_keys']);
            }
            return Application::get($keys) ?? $default;
        }
    }

    /**
     * Get (static)
     *
     * @param string|array $keys
     * @param mixed $default
     * @return mixed
     */
    public function getStatic(string $configuration_link, string|array $keys, mixed $default = null): mixed
    {
        $object = new static($configuration_link);
        return $object->get($keys, $default);
    }

    /**
     * Get configuration paths
     *
     * This will load path, then production, then environment
     *
     * @param string $path
     * @param string|null $environment
     * @return string[]
     */
    public static function getConfigurationPaths(string $path, string|null $environment = null): array
    {
        if ($environment === null) {
            $environment = Application::get('environment');
        }
        $result = [];
        $result[] = $path;
        if (strpos($path, '.template.') !== false) {
            $temp = explode('.template.', $path, 2);
            $result[] = $temp[0] . '.production.template.' . $temp[1];
            if ($environment !== 'production') {
                $result[] = $temp[0] . '.' . $environment . '.template.' . $temp[1];
            }
        } else {
            $temp = [];
            $temp[0] = pathinfo($path, PATHINFO_FILENAME);
            $temp[1] = pathinfo($path, PATHINFO_EXTENSION);
            $dir = pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
            // fix for .env files
            if (empty($temp[0]) && $temp[1] == 'env') {
                $result[] = rtrim($dir . '.' . $temp[1] . '.production', '.');
                if ($environment !== 'production') {
                    $result[] = rtrim($dir . '.'. $temp[1] . '.' . $environment, '.');
                }
            } else {
                $result[] = rtrim($dir . $temp[0] . '.production.' . $temp[1], '.');
                if ($environment !== 'production') {
                    $result[] = rtrim($dir . $temp[0] . '.' . $environment . '.' . $temp[1], '.');
                }
            }
        }
        return $result;
    }

    /**
     * Read file
     *
     * @param string $path
     * @param string|null $environment
     * @param array $options
     * @return array
     */
    public function readFile(string $path, string|null $environment = null, array $options = []): array
    {
        $options2 = array_merge_hard($this->options, $options);
        return $this->object->readFile($path, $environment, $options2);
    }

    /**
     * Read string
     *
     * @param string $str
     * @param string|null $environment
     * @param array $options
     * @return array
     */
    public function readString(string $str, string|null $environment = null, array $options = []): array
    {
        $options2 = array_merge_hard($this->options, $options);
        return $this->object->readString($str, $environment, $options2);
    }

    /**
     * Replace environment variables
     *
     * @param array $result
     * @return array
     */
    public static function replaceEnvironmentVariables(array $result): array
    {
        array_walk_recursive2($result, ['\Configuration', 'matchEnvironmentVariables']);
        return $result;
    }

    /**
     * Match environment variables
     *
     * @param mixed $value
     * @param mixed $key
     * @return mixed
     */
    public static function matchEnvironmentVariables($value, $key): mixed
    {
        // if we have environment variables
        if (is_string($value) && strpos($value, '$(') !== false) {
            preg_match_all(self::OPENING_CLOSING_ENV_VARIABLE_REGEX, $value, $matches, PREG_PATTERN_ORDER);
            if (!empty($matches[0])) {
                foreach ($matches[0] as $k => $v) {
                    $value = str_replace($v, $_ENV[$matches[1][$k]] ?? '', $value);
                }
            }
        }
        return $value;
    }

    /**
     * Init default links
     *
     * @return void
     */
    public static function initDefaultLinks(): void
    {
        $configuration = Application::get('configuration') ?? [];
        $configuration['default']['submodule'] = '\Numbers\Backend\Configuration\Ini\Base';
        $configuration['default']['prepend_keys'] = '';
        if (Can::submoduleExists('Numbers.Backend.Configuration.Ini')) {
            $configuration['ini']['submodule'] = '\Numbers\Backend\Configuration\Ini\Base';
            $configuration['ini']['prepend_keys'] = 'configs';
        }
        if (Can::submoduleExists('Numbers.Backend.Configuration.JSON')) {
            $configuration['json']['submodule'] = '\Numbers\Backend\Configuration\JSON\Base';
            $configuration['json']['prepend_keys'] = 'configs';
        }
        if (Can::submoduleExists('Numbers.Backend.Configuration.Environment')) {
            $configuration['env']['submodule'] = '\Numbers\Backend\Configuration\Environment\Base';
            $configuration['env']['prepend_keys'] = 'env';
        }
        // add to ini storage
        Application::set('configuration', $configuration);
        // initialize classes
        $configuration_links = [];
        foreach ($configuration as $configuration_link => $configuration_settings) {
            $configuration_model = new self($configuration_link, $configuration_settings['submodule'], $configuration_settings);
            $configuration_links[] = $configuration_link;
        }
        Log::add([
            'type' => 'System',
            'only_channel' => 'default',
            'message' => 'Initialized configuration!',
            'other' => 'Configuration links: ' . implode(', ', $configuration_links),
        ]);
    }
}
