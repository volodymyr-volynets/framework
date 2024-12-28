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

enum PolicyReturnTypes: string
{
    use EnumTrait;

    #[Object_Enum_LocAttribute('NF.Form.Allow', 'Allow', 'Action allow.')]
    case Allow = 'Allow';

    #[Object_Enum_LocAttribute('NF.Form.Deny', 'Deny', 'Action deny.')]
    case Deny = 'Deny';

    #[Object_Enum_LocAttribute('NF.Form.NotMached', 'Not Mached', 'Action not mached.')]
    case NotMached = 'NotMached';
}
