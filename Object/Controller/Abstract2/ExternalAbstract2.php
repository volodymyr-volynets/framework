<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Controller\Abstract2;

use Helper\HTTPRequest;

class ExternalAbstract2
{
    /**
     * @var array
     */
    public array $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function preset(string $endpoint, string $method = 'GET', array $input = [], $options = []): void
    {
        $this->data['endpoint'] = $endpoint;
        $this->data['method'] = strtolower($method);
        $this->data['input'] = $input;
        $this->data['options'] = $options;
    }

    /**
     * Execute (remote)
     *
     * @return array
     */
    public function remoteExecute(): array
    {
        $model = HTTPRequest::createStatic()
            ->url($this->data['endpoint'])
            ->acceptable(HTTPRequest::Status200OK)
            ->retry($options['retry'] ?? 1, 3)
            ->header('Content-Type', 'application/json');
        if (!empty($this->data['options']['input_as_raw'])) {
            $model = $model->body($this->data['input'], 'JSON');
        } else {
            $model = $model->params($this->data['input']);
        }
        $result = $model
            ->method($this->data['method'])
            ->jsonDecode(true)
            ->result();
        // if call is successful we need to return data key as is
        if ($result['success']) {
            $result = $result['data'];
        }
        return $result;
    }
}
