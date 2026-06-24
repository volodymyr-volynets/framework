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

class QRCode
{
    /**
     * Render QR-Code
     *
     * @param string $url
     * @return string
     */
    public static function renderQRCode(string $url): string
    {
        return "<img src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url) . "'>";
    }
}
