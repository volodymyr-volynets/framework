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
    public const INVALID_HASH_TAGGED_GIVEN = ['NF.Error.InvalidHashTaggedGiven' => 'Hashtag should start with # and no spaces','errno' => 'NF.ER.0014'];
    public const INVALID_MENTION_GIVEN = ['NF.Error.InvalidMentionGiven' => 'Mention should start with @ and no spaces','errno' => 'NF.ER.0013'];
    public const INVALID_STRING_LETTERS_NUMBERS_DASHES_UNDERSCORES = ['NF.Error.InvalidStringLettersNumbersDashesUnderscores' => 'String must contain letters, numbers, dashes and underscores!','errno' => 'NF.ER.0015'];
    public const INVALID_STRING_LETTERS_NUMBERS_UNDERSCORES = ['NF.Error.InvalidStringLettersNumbersUnderscores' => 'String must contain uppercase letters, numbers and underscores!','errno' => 'NF.ER.0018'];
    public const INVALID_VALUES = ['NF.Error.InvalidValues' => 'Invalid value(s)!','errno' => 'NF.ER.0006'];
    public const INVALID_WHAT_VALUES = ['NF.Error.InvalidWhatValues' => 'Invalid {what} value(s)!','errno' => 'NF.ER.0010'];
    public const LENGTH_MUST_BE_LONG = ['NF.Error.LengthMustBeLong' => 'The length must be {length} characters!','errno' => 'NF.ER.0011'];
    public const MAXIMUM_NUMBER_PER_LIST = ['NF.Error.MaximumNumberPerList' => 'Maximum {number} per list!','errno' => 'NF.ER.0022'];
    public const NO_ROWS_FOUND = ['NF.Error.NoRowsFound' => 'No rows found!','errno' => 'NF.ER.0003'];
    public const ONE_MODEL_PER_AGENT_DEFAULT = ['NF.Error.OneModelPerAgentDefault' => 'Only one default agent can be default!','errno' => 'NF.ER.0021'];
    public const ONE_MODEL_PER_PROVIDER_DEFAULT = ['NF.Error.OneModelPerProviderDefault' => 'Only one model per provider can be default!','errno' => 'NF.ER.0020'];
    public const ONE_PROVIDER_DEFAULT = ['NF.Error.OneProviderDefault' => 'Only one provider can be default!','errno' => 'NF.ER.0019'];
    public const REQUIRED_FIELD = ['NF.Error.RequiredField' => 'Required field!','errno' => 'NF.ER.0004'];
    public const REQUIRED_JSON_KEY = ['NF.Error.RequiredJsonKey' => 'Required JSON key "{field}"!','errno' => 'NF.ER.0005'];
    public const ROUTE_BEARER_TOKEN_EXPIRED = ['NF.Error.RouteBearerTokenExpired' => '{errno}: Bearer token is missing or expired!','errno' => 'NF.ER.0002'];
    public const ROUTE_MIDDLEWARE_DENIED = ['NF.Error.RouteMiddlewareDenied' => '{errno}: Middleware denied for this route!','errno' => 'NF.ER.0001'];
    public const SLUG_MUST_START_WITH_EXTERNAL = ['NF.Error.SlugMustStartWithExternal' => 'Slug must start with "external-" and must continue to have value!','errno' => 'NF.ER.0016'];
    public const SLUG_MUST_START_WITH_PARENT_SLUG = ['NF.Error.SlugMustStartWithParentSlug' => 'Slug must start with parent slug!','errno' => 'NF.ER.0017'];
    public const STRING_IS_TOO_LONG = ['NF.Error.StringIsTooLong' => 'String is too long, should be no longer than {length}!','errno' => 'NF.ER.0012'];
    public const STRING_PASCAL_CASE = ['NF.Error.StringPascalCase' => 'The string must be PascalCase!','errno' => 'NF.ER.0007'];
}
