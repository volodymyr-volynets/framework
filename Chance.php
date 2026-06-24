<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class Chance
{
    /**
     * Calc chance (static)
     *
     * @param float|int $percent
     * @param callable|null $winner
     * @param callable|null $looser
     * @return bool
     */
    public static function calcChanceStatic(float|int $percent, callable|null $winner = null, callable|null $looser = null): bool
    {
        $result = mt_rand(0, 99) < $percent;
        if ($result && isset($winner)) {
            $winner();
        } elseif (!$result && isset($looser)) {
            $looser();
        }
        return $result;
    }

    /**
     * Calc odds (static)
     *
     * @param float|int $odd
     * @param float|int $total
     * @param callable|null $winner
     * @param callable|null $looser
     * @return bool
     */
    public static function calcOddsStatic(float|int $odd, float|int $total, callable|null $winner = null, callable|null $looser = null): bool
    {
        $result = mt_rand(0, $total - 1) < $odd;
        if ($result && isset($winner)) {
            $winner();
        } elseif (!$result && isset($looser)) {
            $looser();
        }
        return $result;
    }
}
