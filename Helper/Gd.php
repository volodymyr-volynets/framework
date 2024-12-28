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

class Gd
{
    /**
     * Start output buffering
     */
    public static function scaleImage(string $filename, float $width, float $height): array
    {
        $size = getimagesize($filename);
        $result = [
            'width' => $size[0],
            'height' => $size[1],
        ];
        // if we need to scale
        if ($result['width'] > $width) {
            $ratio = $result['width'] / $width;
            $result['width'] = $width;
            $result['height'] = intval($result['height'] / $ratio);
        }
        if ($result['height'] > $height) {
            $ratio = $result['height'] / $height;
            $result['height'] = $height;
            $result['width'] = intval($result['width'] / $ratio);
        }
        return $result;
    }
}
