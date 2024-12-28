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

enum EventTypes: string
{
    use EnumTrait;

    #[Object_Enum_LocAttribute('NF.Form.Realtime', 'Realtime', 'Calls subscribers in realtime.')]
    case Realtime = 'SM::REALTIME';

    #[Object_Enum_LocAttribute('NF.Form.RequestEnd', 'Request End', 'Calls subscribers at the end of a request.')]
    case RequestEnd = 'SM::REQUEST_END';

    #[Object_Enum_LocAttribute('NF.Form.Daemon', 'Daemon', 'Calls subscribers every 5 seconds.')]
    case Daemon = 'SM::DAEMON';

    #[Object_Enum_LocAttribute('NF.Form.Task', 'Task', 'Calls subscribers every minute or specified inteeerval.')]
    case Task = 'SM::TASK';

    #[Object_Enum_LocAttribute('NF.Form.Cron', 'Cron', 'Calls subscribers as per cron expression.')]
    case Cron = 'SM::CRON';

    #[Object_Enum_LocAttribute('NF.Form.AtDatetime', 'At Datetime', 'Calls subscribers at datetime.')]
    case AtDatetime = 'SM::AT_DATETIME';
}
