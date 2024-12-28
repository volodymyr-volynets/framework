<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace NF;

use Object\Content\LocalizationConstants;

class Error extends LocalizationConstants
{
    public static $prefix = 'NF.ER.';
    public const EMPTY_VALUES = ['NF.Error.EmptyValues' => 'Empty value(s)!','errno' => 'NF.ER.0008'];
    public const EMPTY_WHAT_VALUES = ['NF.Error.EmptyWhatValues' => 'Empty {what} value(s)!','errno' => 'NF.ER.0009'];
    public const INVALID_VALUES = ['NF.Error.InvalidValues' => 'Invalid value(s)!','errno' => 'NF.ER.0006'];
    public const INVALID_WHAT_VALUES = ['NF.Error.InvalidWhatValues' => 'Invalid {what} value(s)!','errno' => 'NF.ER.0010'];
    public const LENGTH_MUST_BE_LONG = ['NF.Error.LengthMustBeLong' => 'The length must be {length} characters!','errno' => 'NF.ER.0011'];
    public const NO_ROWS_FOUND = ['NF.Error.NoRowsFound' => 'No rows found!','errno' => 'NF.ER.0003'];
    public const REQUIRED_FIELD = ['NF.Error.RequiredField' => 'Required field!','errno' => 'NF.ER.0004'];
    public const REQUIRED_JSON_KEY = ['NF.Error.RequiredJsonKey' => 'Required JSON key "{field}"!','errno' => 'NF.ER.0005'];
    public const ROUTE_BEARER_TOKEN_EXPIRED = ['NF.Error.RouteBearerTokenExpired' => '{errno}: Bearer token is missing or expired!','errno' => 'NF.ER.0002'];
    public const ROUTE_MIDDLEWARE_DENIED = ['NF.Error.RouteMiddlewareDenied' => '{errno}: Middleware denied for this route!','errno' => 'NF.ER.0001'];
    public const STRING_IS_TOO_LONG = ['NF.Error.StringIsTooLong' => 'String is too long, should be no longer than {length}!','errno' => 'NF.ER.0012'];
    public const STRING_PASCAL_CASE = ['NF.Error.StringPascalCase' => 'The string must be PascalCase!','errno' => 'NF.ER.0007'];
}
