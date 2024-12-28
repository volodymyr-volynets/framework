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

use Helper\Constant\HTTPConstants;

class HTTPRequest extends HTTPConstants
{
    /**
     * @var array
     */
    private array $headers = [];

    /**
     * @var array
     */
    private array $parameters = [
        'GET' => [],
        'POST' => [],
        'BODY' => null,
    ];

    /**
     * @var array
     */
    private array $acceptable = [];

    /**
     * @var array
     */
    private array $options = [
        'method' => null,
        'url' => null,
        'accept' => [],
        'path' => null,
        // retry
        'retry_times' => 1,
        'retry_seconds' => 0,
    ];

    /**
     * @var array
     */
    private array $result = [
        'success' => false,
        'error' => [],
        'status' => null,
        'data' => null,
        'info' => null,
    ];

    /**
     * URL
     *
     * @param string $url
     * @return HTTPRequest
     */
    public function url(string $url): HTTPRequest
    {
        $this->options['url'] = $url;
        return $this;
    }

    /**
     * Param
     *
     * @param string $field
     * @param mixed $value
     * @param string $type
     *      GET - get param in URL
     *      POST - post param in post fields
     *      BODY - raw in a body
     * @return HTTPRequest
     */
    public function param(string $field, $value, string $type = 'POST'): HTTPRequest
    {
        $this->parameters[$type][$field] = $value;
        return $this;
    }

    /**
     * Params
     *
     * @param array $params
     * @param string $type
     * @return HTTPRequest
     */
    public function params(array $params, string $type = 'POST'): HTTPRequest
    {
        foreach ($params as $k => $v) {
            $this->param($k, $v, $type);
        }
        return $this;
    }

    /**
     * Body
     *
     * @param mixed $value
     * @param string $type
     * 		JSON
     * 		XML
     * 		else serialize
     * @return HTTPRequest
     */
    public function body($value, string $type = 'JSON'): HTTPRequest
    {
        if ($type == 'JSON') {
            if (!is_json($value)) {
                $value = json_encode($value);
            }
        } elseif ($type == 'XML') {
            if (!is_xml($value)) {
                $value = array2xml($value);
            }
        } else {
            if (!is_string($value)) {
                $value = serialize($value);
            }
        }
        $this->parameters['BODY'] = $value;
        return $this;
    }

    /**
     * Path
     *
     * @param string $path
     * @param array $values
     * @return HTTPRequest
     */
    public function path(string $path, array $values = []): HTTPRequest
    {
        $this->options['path'] = $path;
        // if we have parameter in url
        if (strpos($this->options['path'], '{') !== false) {
            $matches = [];
            if (preg_match_all('/{(.*?)}/', $this->options['path'], $matches, PREG_PATTERN_ORDER)) {
                foreach ($matches[1] as $v) {
                    if (!array_key_exists($v, $values)) {
                        throw new \Exception('YOu must supply all path parameters!');
                    }
                    $this->options['path'] = str_replace('{' . $v . '}', $values[$v], $this->options['path']);
                }
            }
        }
        return $this;
    }

    /**
     * Header
     *
     * @param string $name
     * @param mixed $value
     * @return HTTPRequest
     */
    public function header(string $name, $value): HTTPRequest
    {
        $header = $name . ': ' . $value;
        $this->headers[$name] = $header;
        return $this;
    }

    /**
     * Headers
     *
     * @param array $headers
     * @return HTTPRequest
     */
    public function headers(array $headers): HTTPRequest
    {
        foreach ($headers as $k => $v) {
            $this->header($k, $v);
        }
        return $this;
    }

    /**
     * Accept
     *
     * @param array|string $type
     *		application/json
     *		application/xml
     *		text/html
     * @return HTTPRequest
     */
    public function accept(array|string $type): HTTPRequest
    {
        if (!is_array($type)) {
            $type = [$type];
        }
        $this->options['accept'] = array_unique(array_merge($this->options['accept'], $type));
        return $this;
    }

    /**
     * Acceptable HTTP response statuses
     *
     * @param array|int|string $statuses
     * @return HTTPRequest
     */
    public function acceptable(array|int|string $statuses): HTTPRequest
    {
        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }
        $this->acceptable = array_unique(array_merge($this->acceptable, $statuses));
        return $this;
    }

    /**
     * Retry
     *
     * @param int $times
     * @param int $seconds
     * @return HTTPRequest
     */
    public function retry(int $times = 3, int $seconds = 3): HTTPRequest
    {
        $times = abs($times);
        // minimum run is 1
        if ($times == 0) {
            $times = 1;
        }
        $this->options['retry_times'] = $times;
        $this->options['retry_seconds'] = abs($seconds);
        return $this;
    }

    /**
     * Validate request
     */
    private function validateRequest(): bool
    {
        if (empty($this->options['url'])) {
            throw new \Exception('You must provide URL!');
        }
        return true;
    }

    /**
     * Do requests
     *
     * @return array
     */
    private function doRequest(): array
    {
        // process headers
        $headers = $this->headers;
        if (empty($headers['Accept'])) {
            if ($this->options['accept']) {
                $headers['Accept'] = 'Accept: ' . implode(', ', $this->options['accept']);
            } else {
                $headers['Accept'] = 'Accept: */*';
            }
        }
        // process url
        $url = $this->options['url'];
        if ($this->options['path']) {
            $url = rtrim($url, '/') . '/' . $this->options['path'];
        }
        for ($i = 1; $i <= $this->options['retry_times']; $i++) {
            switch ($this->options['method']) {
                case 'GET':
                    $result = cURL::get($url, [
                        'params' => array_merge_hard($this->parameters['GET'], $this->parameters['POST']),
                        'headers' => array_values($headers),
                    ]);
                    break;
                case 'POST':
                    $result = cURL::post($url, [
                        'params' => array_merge_hard($this->parameters['GET'], $this->parameters['POST']),
                        'headers' => array_values($headers),
                        'raw' => $this->parameters['BODY'],
                    ]);
                    break;
                case 'PUT':
                    $result = cURL::put($url, [
                        'params' => array_merge_hard($this->parameters['GET'], $this->parameters['POST']),
                        'headers' => array_values($headers),
                        'raw' => $this->parameters['BODY'],
                    ]);
                    break;
                case 'DELETE':
                    $result = cURL::delete($url, [
                        'params' => array_merge_hard($this->parameters['GET'], $this->parameters['POST']),
                        'headers' => array_values($headers),
                        'raw' => $this->parameters['BODY'],
                    ]);
                    break;
            }
            // if we got acceptable statuses
            if ($this->validateAcceptableStatuses($result['status'])) {
                if ($this->options['accept']) {
                    if (in_array('application/json', $this->options['accept']) && is_json($result['data'])) {
                        $result['data'] = json_decode($result['data'], true);
                    }
                    if (in_array('application/xml', $this->options['accept']) && is_xml($result['data'])) {
                        $result['data'] = xml2array($result['data']);
                    }
                }
                return $result;
            }
            // we need to wait specified number of seconds
            if ($this->options['retry_seconds']) {
                sleep($this->options['retry_seconds']);
            }
        }
        if (empty($result['error'])) {
            $result['error'][] = 'Failed to get specified URL after specied number of tries!';
        }
        return $result;
    }

    /**
     * Validate acceptable status
     *
     * @param int|string|null status
     * @return bool
     */
    private function validateAcceptableStatuses(int|string|null $status): bool
    {
        if (in_array($status, $this->acceptable)) {
            return true;
        }
        if (in_array('ST100', $this->acceptable) && $status >= 100 && $status < 200) {
            return true;
        }
        if (in_array('ST200', $this->acceptable) && $status >= 200 && $status < 300) {
            return true;
        }
        if (in_array('ST300', $this->acceptable) && $status >= 300 && $status < 400) {
            return true;
        }
        if (in_array('ST400', $this->acceptable) && $status >= 400 && $status < 500) {
            return true;
        }
        if (in_array('ST500', $this->acceptable) && $status >= 500 && $status < 600) {
            return true;
        }
        return false;
    }

    /**
     * Get
     *
     * @return HTTPRequest
     */
    public function get(): HTTPRequest
    {
        $this->options['method'] = 'GET';
        $this->validateRequest();
        $this->result = $this->doRequest();
        return $this;
    }

    /**
     * Post
     *
     * @return HTTPRequest
     */
    public function post(): HTTPRequest
    {
        $this->options['method'] = 'POST';
        $this->validateRequest();
        $this->result = $this->doRequest();
        return $this;
    }

    /**
     * Put
     *
     * @return HTTPRequest
     */
    public function put(): HTTPRequest
    {
        $this->options['method'] = 'PUT';
        $this->validateRequest();
        $this->result = $this->doRequest();
        return $this;
    }

    /**
     * Delete
     *
     * @return HTTPRequest
     */
    public function delete(): HTTPRequest
    {
        $this->options['method'] = 'DELETE';
        $this->validateRequest();
        $this->result = $this->doRequest();
        return $this;
    }

    /**
     * Status
     *
     * @return int|null
     */
    public function status(): int|null
    {
        return $this->result['status'];
    }

    /**
     * Data
     *
     * @return mixed
     */
    public function data(): mixed
    {
        return $this->result['data'];
    }

    /**
     * Info
     *
     * @return mixed
     */
    public function info(): mixed
    {
        return $this->result['info'];
    }

    /**
     * Error
     *
     * @return array
     */
    public function error(): array
    {
        return $this->result['error'];
    }

    /**
     * JSON Decode
     */
    public function jsonDecode(bool $assoc = true): HTTPRequest
    {
        $this->result['data'] = json_decode($this->result['data'], $assoc);
        return $this;
    }

    /**
     * Result
     *
     * @return array
     */
    public function result(): array
    {
        $status = $this->status();
        return [
            'success' => $status && $status >= 200 && $status < 300,
            'error' => $this->error(),
            'status' => $status,
            'data' => $this->data(),
            'info' => $this->info(),
        ];
    }

    /**
     * Create (static)
     *
     * @return HTTPRequest
     */
    public static function createStatic(): HTTPRequest
    {
        $object = new self();
        return $object;
    }
}
