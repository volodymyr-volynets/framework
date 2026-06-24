<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Data;

use Object\Traits\MagicGetAndSetOnData;

class Optional
{
    use MagicGetAndSetOnData;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * From (static)
     *
     * @param mixed $arr
     * @return static
     */
    public static function fromStatic(mixed $arr): static
    {
        $result = new static();
        if (is_array($arr) || is_object($arr)) {
            foreach ($arr as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    $result->{$k} = self::fromStatic($v);
                } else {
                    $result->{$k} = $v;
                }
            }
        }
        return $result;
    }
}
