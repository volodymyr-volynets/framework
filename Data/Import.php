<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Data;

class Import extends \Object\Import
{
    public $data = [
        'modules' => [
            'options' => [
                'pk' => ['sm_module_code'],
                'model' => '\Numbers\Backend\System\Modules\Model\Collection\Modules',
                'method' => 'save'
            ],
            'data' => [
                [
                    'sm_module_code' => 'NF',
                    'sm_module_type' => 20,
                    'sm_module_name' => 'N/F Numbers Framework',
                    'sm_module_abbreviation' => 'N/F',
                    'sm_module_icon' => 'fab fa-amilia',
                    'sm_module_transactions' => 0,
                    'sm_module_multiple' => 0,
                    'sm_module_activation_model' => null,
                    'sm_module_custom_activation' => false,
                    'sm_module_inactive' => 0,
                    '\Numbers\Backend\System\Modules\Model\Module\Dependencies' => []
                ]
            ]
        ],
    ];
}
