<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Keywords;

class Extractor
{
    /**
     * Extract
     *
     * @param string $string
     * @return array
     *		keyword => count
     */
    public static function extract(string $string): array
    {
        $stopwords = ['i', 'a', 'about', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en', 'for', 'from', 'how', 'in', 'is', 'it', 'la', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'und', 'the', 'www'];
        // strip tags
        $string = strip_tags2($string);
        $string = preg_replace('/[^a-zA-Z0-9\s-]/', ' ', $string);
        // remove urls
        $string = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $string);
        // extract emails and remove them from the string
        $pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
        preg_match_all($pattern, $string, $matches);
        if (!empty($matches[0])) {
            preg_replace($pattern, '', $string);
        }
        // lowecase, remove punctuation, remove extra spaces
        $string = preg_replace('/[\pP]/', ' ', trim(strtolower($string)));
        $string = preg_replace('/\s\s+/i', ' ', $string);
        // match items
        $match = array_filter(explode(' ', $string), function ($item) use ($stopwords) { return !($item == '' || in_array($item, $stopwords) || mb_strlen($item) < 2 || is_numeric($item)); });
        $count = array_count_values($match);
        arsort($count);
        // inject emails back
        if (!empty($matches[0])) {
            foreach ($matches[0] as $v) {
                $count[$v] = $count[$v] ?? 0;
                $count[$v]++;
            }
        }
        return $count;
    }

    /**
     * TSVector
     *
     * @param string $string
     * @return array
     *		keyword => count
     */
    public static function tsvecror(string $string): string
    {
        $stopwords = ['i', 'a', 'about', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en', 'for', 'from', 'how', 'in', 'is', 'it', 'la', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'und', 'the', 'www'];
        // strip tags
        $string = strip_tags2($string);
        $string = preg_replace('/[^a-zA-Z0-9\s-]/', ' ', $string);
        // remove urls
        $string = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $string);
        // extract emails and remove them from the string
        $pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
        preg_match_all($pattern, $string, $matches);
        if (!empty($matches[0])) {
            preg_replace($pattern, '', $string);
        }
        // lowecase, remove punctuation, remove extra spaces
        $string = preg_replace('/[\pP]/', ' ', trim(strtolower($string)));
        $string = preg_replace('/\s\s+/i', ' ', $string);
        // match items
        $match = array_filter(explode(' ', $string), function ($item) use ($stopwords) { return !($item == '' || in_array($item, $stopwords) || mb_strlen($item) < 2 || is_numeric($item)); });
        $result = [];
        // inject emails back
        if (!empty($match)) {
            foreach ($match as $v) {
                $result[] = $v;
            }
        }
        return implode(' ', $result);
    }
}
