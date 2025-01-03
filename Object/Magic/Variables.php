<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Magic;

use Object\Data;

class Variables extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Object Magic Variables';
    public $column_key = 'no_magic_variable_name';
    public $column_prefix = 'no_magic_variable_';
    public $columns = [
        'no_magic_variable_name' => ['name' => 'Name', 'type' => 'varchar', 'length' => 100],
        'no_magic_variable_description' => ['name' => 'Description', 'type' => 'text']
    ];
    public $data = [
        '__module_id' => ['no_magic_variable_description' => 'Module #'],
        '__db_token' => ['no_magic_variable_description' => 'Db Token'],
        '__content_type' => ['no_magic_variable_description' => 'Content Type'],
        //'__in_language_code' => ['no_magic_variable_description' => 'I/N Language Code'],
        '__in_group_id' => ['no_magic_variable_description' => 'I/N Group #'],
        '__skip_layout' => ['no_magic_variable_description' => 'Skip Layout'],
        '__skip_session' => ['no_magic_variable_description' => 'Skip Session'],
        '__ajax' => ['no_magic_variable_description' => 'Ajax Call'],
        '__session_id' => ['no_magic_variable_description' => 'Session #'],
        '__menu_id' => ['no_magic_variable_description' => 'Menu #'],
        '__history_id' => ['no_magic_variable_description' => 'History #'],
        '__form_filter_id' => ['no_magic_variable_description' => 'Form Filter #'],
        '__form_filter_id_delete' => ['no_magic_variable_description' => 'Form Filter # (Delete)'],
    ];
}
