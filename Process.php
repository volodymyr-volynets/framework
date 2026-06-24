<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Constant\ProcessConstants;

class Process extends ProcessConstants
{
    /**
     * Run (static)
     *
     * @param array|string $cmd
     * @return array{code: int|null, error: array, output: array, success: bool}
     */
    public static function runStatic(array|string $cmd): array
    {
        if (is_array($cmd)) {
            $cmd = implode(' ', $cmd);
        }
        $output = [];
        $return_var = 0;
        $result = exec($cmd, $output, $return_var);
        $error = [];
        if ($return_var != 0) {
            $error[] = (self::CODES[$return_var]['name'] ?? 'Process: Unknown error occured') . ': ' . $cmd;
        }
        return [
            'success' => $return_var == 0,
            'error' => $error,
            'code' => $return_var,
            'output' => array_values($output),
        ];
    }

    /**
     * Pipe (static)
     *
     * @param array $cmd
     * @return array{code: int, error: array, output: array, success: bool}
     */
    public static function pipeStatic(array $cmd): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'code' => 0,
            'output' => [],
        ];
        $index = 1;
        foreach ($cmd as $v) {
            $temp = self::runStatic($v);
            if (is_array($v)) {
                $v = implode(' ', $v);
            }
            if (!$temp['success']) {
                $result['error'] = array_merge($result['error'], $temp['error']);
                $result['code'] = $temp['code'];
                return $result;
            }
            $result['output'][$index . '. ' . $v] = $temp['output'];
            $index++;
        }
        $result['success'] = true;
        return $result;
    }
}
