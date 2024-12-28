<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Table;

use Object\Form\Base;
use Object\Table;

class Complementary
{
    /**
     * Preload json data
     *
     * @param Table $model
     * @param array $where
     * @param array $columns
     * @param array $values
     */
    public static function jsonPreloadData(Table $model, array $where, array $columns, array & $values)
    {
        $result = $model->get([
            'where' => $where,
            'pk' => null
        ]);
        foreach ($columns as $a => $c) {
            if (is_array($c)) {
                $only_columns = $c;
                $c = $a;
            } else {
                $only_columns = false;
            }
            if (!empty($result[0][$c])) {
                if (is_json($result[0][$c])) { // json columns
                    if ($result[0][$c] != 'null') {
                        $temp = json_decode($result[0][$c], true);
                        foreach ($temp as $k => $v) {
                            if (!empty($only_columns) && !in_array($k, $only_columns)) {
                                continue;
                            }
                            if (is_array($v) && !empty($v) && empty($values[$k])) {
                                $values[$k] = $v;
                            } elseif (($values[$k] ?? '') == '') {
                                $values[$k] = $v ?? null;
                            }
                        }
                    }
                } else { // scalar columns
                    $values[$c] = $result[0][$c];
                }
            }
        }
    }

    /**
     * Save json data
     *
     * @param Table $model
     * @param array $values
     * @param Base $form
     * @param string $id_column
     * @return bool
     */
    public static function jsonSaveData(Table $model, array $values, Base & $form, string $id_column): bool
    {
        if (!empty($form->values[$id_column])) {
            $values[$id_column] = $form->values[$id_column];
        }
        $result = $model->collection()->merge($values);
        if (!$result['success']) {
            $form->error(DANGER, $result['error']);
            return false;
        }
        $form->values[$id_column] = $form->values[$id_column] ?? $result['new_serials'][$id_column];
        return true;
    }
}
