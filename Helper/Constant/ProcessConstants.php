<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper\Constant;

class ProcessConstants
{
    public const CODES = [
        0 => ['name' => 'OK'],
        1 => ['name' => 'General error'],
        2 => ['name' => 'Misuse of shell builtins'],

        126 => ['name' => 'Invoked command cannot execute'],
        127 => ['name' => 'Command not found'],
        128 => ['name' => 'Invalid exit argument'],

        129 => ['name' => 'Hangup'],
        130 => ['name' => 'Interrupt'],
        131 => ['name' => 'Quit and dump core'],
        132 => ['name' => 'Illegal instruction'],
        133 => ['name' => 'Trace/breakpoint trap'],
        134 => ['name' => 'Process aborted'],
        135 => ['name' => 'Bus error: "access to undefined portion of memory object"'],
        136 => ['name' => 'Floating point exception: "erroneous arithmetic operation"'],
        137 => ['name' => 'Kill (terminate immediately)'],
        138 => ['name' => 'User-defined 1'],
        139 => ['name' => 'Segmentation violation'],
        140 => ['name' => 'User-defined 2'],
        141 => ['name' => 'Write to pipe with no one reading'],
        142 => ['name' => 'Signal raised by alarm'],
        143 => ['name' => 'Termination (request to terminate)'],
        145 => ['name' => 'Child process terminated, stopped (or continued*)'],
        146 => ['name' => 'Continue if stopped'],
        147 => ['name' => 'Stop executing temporarily'],
        148 => ['name' => 'Terminal stop signal'],
        149 => ['name' => 'Background process attempting to read from tty ("in")'],
        150 => ['name' => 'Background process attempting to write to tty ("out")'],
        151 => ['name' => 'Urgent data available on socket'],
        152 => ['name' => 'CPU time limit exceeded'],
        153 => ['name' => 'File size limit exceeded'],
        154 => ['name' => 'Signal raised by timer counting virtual time: "virtual timer expired"'],
        155 => ['name' => 'Profiling timer expired'],
        157 => ['name' => 'Pollable event'],
        159 => ['name' => 'Bad syscall'],
    ];

    /**
     * @var array
     */
    public $loc = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        foreach (self::CODES as $k => $v) {
            $key = 'NF.Status.' . (new \String2($v['name']))->englishOnly()->toString();
            $this->loc[$key] = $v['name'];
        }
    }
}
