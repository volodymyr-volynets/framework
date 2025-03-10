<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Data;

use Object\Data;

class Aliases extends Data
{
    public $module_code = 'NO';
    public $title = 'N/O Data Aliases';
    public $column_key = 'no_data_alias_code';
    public $column_prefix = 'no_data_alias_';
    public $orderby = ['no_data_alias_name' => SORT_ASC];
    public $columns = [
        'no_data_alias_code' => ['name' => 'Alias Code', 'type' => 'varchar', 'length' => 50],
        'no_data_alias_name' => ['name' => 'Name', 'type' => 'text'],
        'no_data_alias_model' => ['name' => 'Model', 'type' => 'text'],
        'no_data_alias_column' => ['name' => 'Code Column', 'type' => 'text']
    ];
    public $data = [
        // data would come from overrides
    ];

    /**
     * Get id by code/alias
     *
     * @param string $alias
     * @param string $code
     * @param boolean $id_only
     * @param array $options
     *		boolean skip_acl
     * @return mixed
     */
    public function getIdByCode($alias, $code, $id_only = true, array $options = [])
    {
        $class = $this->data[$alias]['no_data_alias_model'];
        $model = new $class();
        $columns = [];
        if ($id_only) {
            $columns[] = $model->column_prefix . 'id';
        }
        $data = $model->get([
            'columns' => $columns,
            'where' => [
                $this->data[$alias]['no_data_alias_column'] => $code . ''
            ],
            'pk' => null,
            'skip_acl' => $options['skip_acl'] ?? false,
            'no_cache' => true,
        ]);
        // if we have results we need to make it single row
        if (isset($data[0])) {
            $data = $data[0];
        }
        if (!$id_only) {
            return $data;
        } else {
            return $data[$model->column_prefix . 'id'] ?? null;
        }
    }
}
