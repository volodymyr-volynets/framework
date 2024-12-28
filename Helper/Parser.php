<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper;

class Parser
{
    /**
     * Regular expressions
     */
    public const REGEXP_EMAIL = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,})+/i';

    /**
     * Match single occurrence in a string
     *
     * @param string $str
     * @param string $start
     * @param string $end
     * @param array $options
     *	boolean all
     *	boolean raw
     * @return boolean|string
     */
    public static function match(string $str, string $start, string $end, array $options = [])
    {
        $start = preg_quote($start);
        $end = preg_quote($end);
        if (empty($options['all'])) {
            if (preg_match('/' . $start . '(.*)' . $end . '/U', $str, $matches)) {
                if (empty($options['raw'])) {
                    return $matches[1] . '';
                } else {
                    return $matches[0] . '';
                }
            }
        } else {
            if (preg_match_all('/' . $start . '(.*)' . $end . '/U', $str, $matches)) {
                if (empty($options['raw'])) {
                    return $matches[1];
                } else {
                    return $matches[0];
                }
            }
        }
        return false;
    }

    /**
     * Match all email addresses
     *
     * @param string $str
     * @return boolean|array
     */
    public static function emails(string $str)
    {
        preg_match_all(self::REGEXP_EMAIL, $str, $matches);
        return $matches[0] ?? false;
    }

    /**
     * Extract sentances
     *
     * @param string $keywords
     * @param string $str
     * @param int $number
     * @return array
     */
    public static function extractSentances(string $keywords, string $str, int $number = 3, int $length = 120): array
    {
        $result = [];
        if (empty($str)) {
            return $result;
        }
        $i = 1;
        do {
            $temp = self::firstWord($keywords, $str);
            if ($temp == null) {
                if (empty($result)) {
                    $result[] = mb_substr($str, 0, $length) . '...';
                }
                return $result;
            }
            if ($temp < 40) {
                $result[] = mb_substr($str, 0, $length) . '...';
                $str = mb_substr($str, $length);
            } else {
                $result[] = '...' . mb_substr($str, $temp - 40, $length) . '...';
                $str = '...' . mb_substr($str, $temp - 40 + $length);
            }
            $i++;
        } while ($i <= $number);
        return $result;
    }

    /**
     * First word
     *
     * @param string $keywords
     * @param type $str
     * @return int|null
     */
    public static function firstWord(string $keywords, $str)
    {
        $keywords = preg_replace('/\s\s+/', ' ', $keywords);
        $keywords = explode(' ', $keywords);
        $start = null;
        foreach ($keywords as $v) {
            $temp = stripos($str, $v);
            if ($temp !== false) {
                if ($start == null) {
                    $start = $temp;
                } elseif ($start > $temp) {
                    $start = $temp;
                }
            }
        }
        return $start;
    }

    /**
     * Filter UTF-8
     *
     * @param string $str
     * @return string
     */
    public static function filterUTF8(string $str): string
    {
        return iconv(mb_detect_encoding($str, mb_detect_order(), true), "UTF-8", $str);
    }
}
