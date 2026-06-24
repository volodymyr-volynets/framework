<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Traits;

trait BatchesURLHelper
{
    /**
     * Batches get edit endpoint
     *
     * @param int|string $id
     * @param string $link
     * @throws \Exception
     * @return string
     */
    public function batchesGetEditEndpoint(int|string $id, string $link = ''): string
    {
        // sanity check
        if (empty($this->batches['edit'])) {
            throw new \Exception('Batches edit endpoint is not defined!');
        }
        $url = $this->batches['edit']['edit_endpoint'] . '?' . http_build_query2([
            $this->batches['edit']['edit_key'] => $id,
        ]);
        if (empty($link)) {
            return $url;
        }
        return \HTML::a([
            'href' => $url,
            'value' => $link,
            //'target' => '_blank'
        ]);
    }

    /**
     * Batches get edit endpoint
     *
     * @param int|string $id
     * @param string $link
     * @throws \Exception
     * @return string
     */
    public function batchesGetListEndpoint(int|string $id, string $link = ''): string
    {
        // sanity check
        if (empty($this->batches['edit'])) {
            throw new \Exception('Batches list endpoint is not defined!');
        }
        $params = [];
        foreach ($this->batches['edit']['list_key'] as $v) {
            $params[$v] = $id;
        }
        $url = $this->batches['edit']['list_endpoint'] . '?' . http_build_query2($params);
        if (empty($link)) {
            return $url;
        }
        return \HTML::a([
            'href' => $url,
            'value' => $link,
            //'target' => '_blank'
        ]);
    }
}
