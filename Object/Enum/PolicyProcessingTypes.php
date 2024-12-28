<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Enum;

use Object\Traits\EnumTrait;
use Object_Enum_LocAttribute;

enum PolicyProcessingTypes: string
{
    use EnumTrait;

    #[Object_Enum_LocAttribute('NF.Form.AnyAllow', 'Any Allow', 'Action any allow.')]
    case AnyAllow = 'AnyAllow';

    #[Object_Enum_LocAttribute('NF.Form.AllAllow', 'All Allow', 'Action all allow.')]
    case AllAllow = 'AllAllow';
}
