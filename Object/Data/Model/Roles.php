<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Data\Model;

use Object\Data;

class Roles extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data Roles';
    public $column_key = 'no_data_model_role_code';
    public $column_prefix = 'no_data_model_role_';
    public $orderby = [
        'no_data_model_role_order' => SORT_ASC
    ];
    public $columns = [
        'no_data_model_role_code' => ['name' => 'Code', 'domain' => 'lgroup_code'],
        'no_data_model_role_name' => ['name' => 'Name', 'type' => 'text'],
        'no_data_model_role_order' => ['name' => 'Order', 'domain' => 'order'],
    ];
    public $options_map = [
        'no_data_model_role_name' => 'name',
    ];
    public $data = [
        'primary' => ['no_data_model_role_name' => 'Primary', 'no_data_model_role_order' => 100],
        'secondary' => ['no_data_model_role_name' => 'Secondary', 'no_data_model_role_order' => 200],
        // AI roles
        'user' => ['no_data_model_role_name' => 'User', 'no_data_model_role_order' => 300],
        'system' => ['no_data_model_role_name' => 'System', 'no_data_model_role_order' => 400],
        'assistant' => ['no_data_model_role_name' => 'Assistant', 'no_data_model_role_order' => 500],
        'tool' => ['no_data_model_role_name' => 'Tool', 'no_data_model_role_order' => 600],
        'agent' => ['no_data_model_role_name' => 'Agent', 'no_data_model_role_order' => 700],
        'conversation' => ['no_data_model_role_name' => 'Conversation', 'no_data_model_role_order' => 800],
        // groups
        'group' => ['no_data_model_role_name' => 'Group', 'no_data_model_role_order' => 1000],
        'group_user' => ['no_data_model_role_name' => 'Group-User', 'no_data_model_role_order' => 1100],
        'user_mention' => ['no_data_model_role_name' => 'User-Mention', 'no_data_model_role_order' => 1200],
        'channel' => ['no_data_model_role_name' => 'Channel', 'no_data_model_role_order' => 1300],
        // chat
        'chat' => ['no_data_model_role_name' => 'Chat', 'no_data_model_role_order' => 1400],
        'message' => ['no_data_model_role_name' => 'Message', 'no_data_model_role_order' => 1500],
        'thread' => ['no_data_model_role_name' => 'Thread', 'no_data_model_role_order' => 1600],
        'acknowledgement' => ['no_data_model_role_name' => 'Acknowledgement', 'no_data_model_role_order' => 1700],
        // context
        'context_user' => ['no_data_model_role_name' => 'Context-User', 'no_data_model_role_order' => 2000],
        'owner_type' => ['no_data_model_role_name' => 'Owner Type', 'no_data_model_role_order' => 2100],
        'executing_user' => ['no_data_model_role_name' => 'Executing User', 'no_data_model_role_order' => 2200],
        'context_record' => ['no_data_model_role_name' => 'Context Record', 'no_data_model_role_order' => 2200],
        // ai
        'embedding' => ['no_data_model_role_name' => 'Embedding', 'no_data_model_role_order' => 3000],
    ];
}
