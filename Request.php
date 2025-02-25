<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Constant\HTTPConstants;
use Object\ACL\Resources;
use Object\Controller;
use Object\Error\Base;
use Object\Traits\MagicGetAndSetOnData;
use Object\Traits\ObjectableAndStaticable;

/**
 * @method string ip() IP Adderess
 * @method mixed cookie(mixed $key, mixed $default) Cookie
 * @method string|null bearerToken() Bearer token
 * @method mixed header(mixed $key, mixed $default) Header
 * @method array headers() Headers
 * @method string host(array $params) Host
 * @method string method() Get request method
 * @method static mixed getStatic(mixed $key, mixed $default) Get (static)
 * @method static array allStatic() All (static)
 * @method static Array2 array2Static() Array2 (static)
 */
class Request
{
    use ObjectableAndStaticable;
    use MagicGetAndSetOnData;

    /**
     * @var array|null
     */
    protected array $data = [];

    /**
     * @var array|null
     */
    protected static ?array $headers = null;

    /**
     * @var array
     */
    public $columns = [];

    /**
     * Constructor
     *
     * @param array $options
     *      bool include_cookie
     */
    public function __construct(array $options = [])
    {
        $this->data = self::input(null, true, !empty($options['include_cookie']));
    }

    /**
     * Get
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }
        return array_key_get($this->data, $key) ?? $default;
    }

    /**
     * Validate
     *
     * @param array|null $columns
     * @return Validator
     */
    public function validate(?array $columns = null): Validator
    {
        return Validator::validateInputStatic($this->data, $columns ?? $this->columns);
    }

    /**
     * All data
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * To Array2
     *
     * @return Array2
     */
    public function array2(): Array2
    {
        return new Array2($this->data);
    }

    /**
     * To String2
     *
     * @param mixed $key
     * @param mixed $default
     * @return String2
     */
    public function string2(mixed $key, mixed $default = null): String2
    {
        return new String2($this->get($key, $default));
    }

    /**
     * To string
     *
     * @param mixed $key
     * @param mixed $default
     * @return string
     */
    public function string(mixed $key, mixed $default = null): string
    {
        return (string) $this->get($key, $default);
    }

    /**
     * To integer
     *
     * @param mixed $key
     * @param mixed $default
     * @return int
     */
    public function integer(mixed $key, mixed $default = 0): int
    {
        return intval($this->get($key, $default));
    }

    /**
     * To float
     *
     * @param mixed $key
     * @param mixed $default
     * @return float
     */
    public function float(mixed $key, mixed $default = 0.00): float
    {
        return floatval($this->get($key, $default));
    }

    /**
     * To boolean
     *
     * @param mixed $key
     * @param mixed $default
     * @return bool
     */
    public function boolean(mixed $key, mixed $default = null): bool
    {
        return !empty($this->get($key, $default));
    }

    /**
     * Enum
     *
     * @param mixed $class
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function enum(mixed $class, mixed $key, mixed $default = null): mixed
    {
        $result = $this->get($key, $default);
        return call_user_func_array([$class, 'from'], [$result]);
    }

    /**
     * Only these values
     *
     * @param array|string $keys
     * @return array
     */
    public function only(array|string $keys): array
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $result = [];
        foreach ($keys as $v) {
            $temp = array_key_get($this->data, $v);
            array_key_set($result, $v, $temp);
        }
        return $result;
    }

    /**
     * Except these values
     *
     * @param array|string $keys
     * @return array
     */
    public function except(array|string $keys): array
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $result = $this->data;
        foreach ($keys as $v) {
            array_key_get($result, $v, ['unset' => true]);
        }
        return $result;
    }

    /**
     * Present all values
     *
     * @param array|string $keys
     * @return bool
     */
    public function presentAll(array|string $keys): bool
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        foreach ($keys as $v) {
            $temp = array_key_get($this->data, $v, ['present' => true]);
            if (!$temp) {
                return false;
            }
        }
        return true;
    }

    /**
     * Present any of the values
     *
     * @param array|string $keys
     * @return bool
     */
    public function presentAny(array|string $keys): bool
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        foreach ($keys as $v) {
            $temp = array_key_get($this->data, $v, ['present' => true]);
            if ($temp) {
                return true;
            }
        }
        return false;
    }

    /**
     * Merge values
     *
     * @param array $data
     * @return array
     */
    public function merge(array $data): array
    {
        $this->data = array_merge_hard($this->data, $data);
        return $this->data;
    }

    /**
     * Cookie
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function cookie(mixed $key = null, mixed $default = null): mixed
    {
        return array_key_get($_COOKIE, $key) ?? $default;
    }

    /**
     * Bearer token
     *
     * @return string|null
     */
    public static function bearerToken(): ?string
    {
        return Application::get('flag.global.__bearer_token');
    }

    /**
     * Header
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function header(mixed $key, mixed $default = null): mixed
    {
        if (!isset(self::$headers)) {
            self::$headers = getallheaders();
        }
        return self::$headers[$key] ?? $default;
    }

    /**
     * Headers
     *
     * @return array
     */
    public static function headers(): array
    {
        if (!isset(self::$headers)) {
            self::$headers = getallheaders();
        }
        return self::$headers;
    }

    /**
     * IP Adderess
     *
     * @return string
     */
    public static function ip(): string
    {
        // for development purposes we might need to have specific IP address
        $request_ip = Application::get('flag.numbers.framework.request.ip');
        if (!empty($request_ip)) {
            return $request_ip;
        }
        // get users IP
        $result = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        // if request goes through the proxy
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $result = $_SERVER['HTTP_X_REAL_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // sometimes we have few IP addresses we take last one
        if (strpos($result, ',') !== false) {
            $temp = explode(',', $result);
            $result = trim($temp[sizeof($temp) - 1]);
        }
        // unknown
        if ($result == 'unknown' || is_null($result)) {
            $result = '127.0.0.1';
        }
        return $result;
    }

    /**
     * Get merged cookie, get and post
     *
     * @param mixed $key
     * @param boolean $xss
     * @param boolean $cookie
     * @param array $options
     *		array skip_xss_on_keys
     *		boolean trim_empty_html_input
     *		boolean remove_script_tag
     * @return mixed
     */
    public static function input($key = '', bool $xss = true, bool $cookie = false, array $options = [])
    {
        // cookie first, get and post after
        $_GET = $_GET ?? $_REQUEST ?? [];
        // fix files
        $files = [];
        foreach (($_FILES ?? []) as $k => $v) {
            // we need to convert
            if (is_array($v['name'])) {
                $level = array_nested_levels_count($v['name']);
                // details
                if ($level == 2) {
                    foreach ($v['name'] as $k2 => $v2) {
                        foreach ($v2 as $k3 => $v3) {
                            if (empty($v['tmp_name'][$k2][$k3])) {
                                continue;
                            }
                            $files[$k][$k2][$k3] = [
                                'name' => $v3,
                                'type' => $v['type'][$k2][$k3],
                                'tmp_name' => $v['tmp_name'][$k2][$k3],
                                'error' => $v['error'][$k2][$k3],
                                'size' => $v['size'][$k2][$k3],
                            ];
                        }
                    }
                } elseif ($level == 3) {
                    foreach ($v['name'] as $k2 => $v2) {
                        foreach ($v2 as $k3 => $v3) {
                            foreach ($v3 as $k4 => $v4) {
                                if (empty($v['tmp_name'][$k2][$k3][$k4])) {
                                    continue;
                                }
                                $files[$k][$k2][$k3] = [
                                    'name' => $v4,
                                    'type' => $v['type'][$k2][$k3][$k4],
                                    'tmp_name' => $v['tmp_name'][$k2][$k3][$k4],
                                    'error' => $v['error'][$k2][$k3][$k4],
                                    'size' => $v['size'][$k2][$k3][$k4],
                                ];
                            }
                        }
                    }
                } else {
                    foreach ($v['name'] as $k2 => $v2) {
                        if (empty($v['tmp_name'][$k2])) {
                            continue;
                        }
                        $files[$k][$k2] = [
                            'name' => $v2,
                            'type' => $v['type'][$k2],
                            'tmp_name' => $v['tmp_name'][$k2],
                            'error' => $v['error'][$k2],
                            'size' => $v['size'][$k2],
                        ];
                    }
                }
            } else {
                if (empty($v['name'])) {
                    continue;
                }
                $files[$k] = $v;
            }
        }
        if ($cookie) {
            $result = array_merge_hard($_COOKIE, $_GET, $_POST, $files);
        } else {
            $result = array_merge_hard($_GET, $_POST, $files);
        }
        // raw data from the request
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            // json
            if (is_json($raw)) {
                $result = array_merge_hard($result, json_decode($raw, true));
            } elseif (is_xml($raw)) { // xml
                $xml = simplexml_load_string($raw);
                $result = array_merge_hard($result, xml2array($xml));
            }
        }
        // protection against XSS attacks is on by default
        if ($xss) {
            $result = strip_tags2($result, $options);
        }
        // we need to get rid of session id from the result
        if (!$cookie) {
            unset($result[session_name()]);
        }
        // if we are debugging
        if (Debug::$debug) {
            Debug::$data['input'][] = $result;
        }
        // returning result
        if ($key) {
            return array_key_get($result, $key);
        } else {
            return $result;
        }
    }

    /**
     * Host, input parameters: ip, request, protocol
     *
     * @param array $params
     * @return string
     */
    public static function host(array $params = []): string
    {
        $protocol = !empty($params['protocol']) ? $params['protocol'] : '';
        $port = !empty($params['port']) ? (':' . $params['port']) : '';
        if (!$protocol) {
            $protocol = self::isSSL() ? 'https' : 'http';
        }
        if (!empty($params['host_parts'])) {
            $host = implode('.', $params['host_parts']);
        } else {
            $host = !empty($params['ip']) ? (getenv('SERVER_ADDR') . ':' . getenv('SERVER_PORT')) : getenv('HTTP_HOST');
        }
        if (!empty($params['name_only'])) {
            return $host;
        }
        if (!empty($params['level3'])) {
            $host = str_replace('www.', '', $host);
            $host_parts = self::hostParts($host);
            $host_parts[3] = $params['level3'];
            krsort($host_parts);
            $host = implode('.', $host_parts);
        }
        // if we are from cli we need to use predefined host
        if (empty($host)) {
            $result = Application::get('application.structure.app_domain_url') ?? '';
        } else {
            $result = $protocol . '://' . $host . $port . (!empty($params['request']) ? $_SERVER['REQUEST_URI'] : '/');
        }
        // append mvc
        if (!empty($params['mvc'])) {
            $result = rtrim($result, '/') . $params['mvc'];
        }
        // append parameters
        if (!empty($params['params'])) {
            $result .= '?' . http_build_query2($params['params']);
        }
        return $result;
    }

    /**
     * Get host parts
     *
     * @param string $host
     * @return array
     */
    public static function hostParts($host = null)
    {
        if (empty($host)) {
            $host = self::host();
        }
        $host = str_replace(['http://', 'https://', '/'], '', $host);
        $temp = explode('.', $host);
        krsort($temp);
        $result = [];
        $counter = 1;
        foreach ($temp as $k => $v) {
            $result[$counter] = $v;
            $counter++;
        }
        return $result;
    }

    /**
     * Is tenant
     *
     * @param int $level
     * @param string|null $host
     * @return bool
     */
    public static function isTenant(int $level = 3, ?string $host = null): bool
    {
        $parts = self::hostParts($host);
        return count($parts) == $level && $parts[$level] !== 'www';
    }

    /**
     * Generate urt for particular tenant
     *
     * @param string $tenant_part
     * @return string
     */
    public static function tenantHost(string $tenant_part): string
    {
        $url = Application::get('application.structure.app_domain_host');
        if (!empty($url)) {
            return Request::host(['host_parts' => explode('.', $url)]);
        } else {
            // generate link to system tenant
            $domain_level = (int) Application::get('application.structure.tenant_domain_level');
            $host_parts = Request::hostParts();
            $host_parts[$domain_level] = $tenant_part;
            krsort($host_parts);
            return Request::host(['host_parts' => $host_parts]);
        }
    }

    /**
     * Is ssl
     *
     * @return boolean
     */
    public static function isSSL(): bool
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Redirect
     *
     * @param string $url
     */
    public static function redirect($url, $host = null, $params = [])
    {
        if ($host) {
            $url = rtrim($host, '/') . '/' . ltrim($url, '/');
        }
        if ($params) {
            $url = $url . '?' . http_build_query($params);
        }
        Event::processEvents('SM::REQUEST_END');
        session_write_close(); // a must
        header('Location: ' . $url);
        exit;
    }

    /**
     * Build URL
     *
     * @param type $controller
     * @param array $params
     * @param string $host
     * @return string
     */
    public static function buildURL($controller, array $params = [], $host = null, string $anchor = ''): string
    {
        if (!isset($host)) {
            $host = Request::host();
        }
        $controller = ltrim($controller, '/');
        return $host . $controller . '?' . http_build_query($params) . ($anchor ? ('#' . $anchor) : '');
    }

    /**
     * Build URL from name
     *
     * @param string $name
     * @param string $action
     * @param array $params
     * @param string|null $host
     * @param string|null $anchor
     * @param bool $as_json
     * @return string
     */
    public static function buildFromName(string $name, string $action = 'Edit', array $params = [], ?string $host = null, ?string $anchor = null, bool $as_json = false): string
    {
        if (is_null(Controller::$cached_controllers) && !Base::$flag_database_tenant_not_found) {
            Controller::$cached_controllers = Resources::getStatic('controllers', 'primary');
        }
        if (is_null(Controller::$cached_controllers_by_names)) {
            foreach (Controller::$cached_controllers as $k => $v) {
                $v['key'] = $k;
                Controller::$cached_controllers_by_names[$v['name']] = $v;
            }
        }
        $url = '/';
        $template = '';
        if (!empty(Controller::$cached_controllers_by_names[$name])) {
            $v = Controller::$cached_controllers_by_names[$name];
            $temp = Application::get('application.template.url_path_name');
            if ($temp) {
                $temp .= '-' . ucfirst($v['template']);
            } else {
                $temp = ucfirst($v['template']);
            }
            $url = rtrim($host ?? '', '/') . '/' . $temp . '/'. ltrim(str_replace('\\', '/', $v['key']), '/') . '/_' . $action . '?' . http_build_query($params) . ($anchor ? ('#' . $anchor) : '');
            $url = rtrim($url, '?');
            $template = $v['template'];
        }
        if ($as_json) {
            return json_encode([
                'name' => $name,
                'action' => $action,
                'params' => $params,
                'host' => $host,
                'anchor' => $anchor,
                'template' => $template
            ]);
        } else {
            return $url;
        }
    }

    /**
     * Build URL from JSON
     *
     * @param string $json
     * @param string|null $host
     * @return string
     */
    public static function buildFromJson(string $json, ?string $host = null): string
    {
        $json = json_decode($json, true);
        return self::buildFromName($json['name'], $json['action'] ?? 'Edit', $json['params'] ?? [], $host ?? $json['host'] ?? null, $json['anchor']);
    }


    /**
     * Build URL for current controller
     *
     * @param string|null $action
     * @return string
     */
    public static function buildFromCurrentController(?string $action = null): string
    {

        $mvc = Application::get('mvc');
        // if we are in API or console mode
        if (empty($mvc['controller_class'])) {
            return Application::get('application.request_uri');
        }
        // if we do not have title
        if (empty(Application::$controller->title)) {
            return '/'. ltrim(str_replace('\\', '/', $mvc['controller_class']), '/') . '/_' . ($action ?? $mvc['controller_action_raw']);
        }
        $result = rtrim(self::buildFromName(Application::$controller->title, $action ?? $mvc['controller_action_raw']), '?');
        if (empty($result) || $result == '/') {
            return Application::get('application.request_uri');
        }
        return $result;
    }

    /**
     * Fix URL
     *
     * @param string|null $url
     * @param string $template
     * @param string $default
     * @return string
     */
    public static function fixUrl(?string $url, string $template, string $default = ''): string
    {
        if (!empty($url)) {
            if ($url[0] === '/') {
                if (Application::get('application.template.url_path_name')) {
                    if (strpos($url, '/' . Application::get('application.template.url_path_name') ?? 'X-Template') === false) {
                        $url = '/' .  Application::get('application.template.url_path_name') . '-' . ucfirst($template) . $url;
                    }
                } else {
                    $first_fragment = explode('/', $url);
                    if ($first_fragment[1] !== ucfirst($template)) {
                        $url = '/' .  ucfirst($template) . $url;
                    }
                }
            }
            return $url;
        } else {
            return $default;
        }
    }

    /**
     * Get request method
     *
     * @return string
     *		GET,HEAD,POST,PUT,DELETE,CONNECT,OPTIONS,TRACE,PATCH
     *		CONSOLE is returned if not set
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'CONSOLE';
    }

    /**
     * Hash
     *
     * @param array $options
     * @return string
     */
    public static function hash(array $options): string
    {
        return 'hash::' . implode('::', $options);
    }

    /**
     * Replace HTML tags
     *
     * In HTML we can add: href="[[Url;U/M Sign In;Index]]"
     *
     * @param string $html
     * @return string
     */
    public static function htmlReplaceTags(string $html): string
    {
        $matches = [];
        preg_match_all('/\[\[(.*?)\]\]/is', $html, $matches, PREG_PATTERN_ORDER);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $k => $v) {
                $v = explode(';', $v);
                if (strtolower($v[0]) === 'url') {
                    $html = str_replace($matches[0][$k], Request::buildFromName($v[1], $v[2]), $html);
                }
            }
        }
        return $html;
    }

    /**
     * Check if given URL or domain is white listed.
     *
     * @param string $url
     * @param array $whitelist
     * @return bool
     */
    public static function urlWhitelisted(string $url, array $whitelist): bool
    {
        $domain = parse_url($url, PHP_URL_HOST);
        // exact match
        if (in_array($domain, $whitelist)) {
            return true;
        }
        foreach ($whitelist as $v) {
            $v = '.' . $v;
            if (strpos($domain, $v) === (strlen($domain) - strlen($v))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Error
     *
     * @param int $status
     * @param string|null $text
     */
    public static function error(int $status, ?string $text = null): void
    {
        if ($text === null) {
            $text = HTTPConstants::STATUSES[$status]['name'] ?? 'Error Occured!';
        }
        header('HTTP/1.1 ' . $status);
        echo i18n(null, $text);
        exit;
    }
}
