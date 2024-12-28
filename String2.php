<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

class String2
{
    /**
     * @var string
     */
    protected ?string $data = null;

    /**
     * Constructor
     *
     * @param mixed $data
     */
    public function __construct(mixed $data)
    {
        $this->data = $this->dataToString2($data);
    }

    /**
     * Create (static)
     */
    public static function createStatic(mixed $data): String2
    {
        return new self($data);
    }

    /**
     * Data to \String2
     *
     * @param mixed $data
     * @return string|null
     */
    protected function dataToString2(mixed $data): ?string
    {
        if (is_null($data)) {
            return null;
        } elseif (is_string($data)) {
            return $data;
        } elseif (is_object($data)) {
            return $data->toString();
        } else {
            return (string) $data;
        }
    }

    /**
     * To string
     *
     * @return string|null
     */
    public function toString(): ?string
    {
        return $this->data;
    }

    /**
     * Trim
     *
     * @param string $character_mask
     * @return String2
     */
    public function trim(string $character_mask = ''): String2
    {
        if ($this->data !== null) {
            $this->data = trim($this->data, $character_mask);
        }
        return $this;
    }

    /**
     * Left trim
     *
     * @param string $character_mask
     * @return String2
     */
    public function ltrim(string $character_mask = ''): String2
    {
        if ($this->data !== null) {
            $this->data = ltrim($this->data, $character_mask);
        }
        return $this;
    }

    /**
     * Right trim
     *
     * @param string $character_mask
     * @return String2
     */
    public function rtrim(string $character_mask = ''): String2
    {
        if ($this->data !== null) {
            $this->data = rtrim($this->data, $character_mask);
        }
        return $this;
    }

    /**
     * Uppercase
     *
     * @return String2
     */
    public function uppercase(): String2
    {
        if ($this->data !== null) {
            $this->data = strtoupper($this->data);
        }
        return $this;
    }

    /**
     * Lowercase
     *
     * @return String2
     */
    public function lowercase(): String2
    {
        if ($this->data !== null) {
            $this->data = strtolower($this->data);
        }
        return $this;
    }

    /**
     * Substr
     *
     * @param int $start
     * @param int $length
     * @return String2
     */
    public function substring(int $start, int $length): String2
    {
        if ($this->data !== null) {
            $this->data = substr($this->data, $start, $length);
        }
        return $this;
    }

    /**
     * Replace
     *
     * @param mixed $needle_from
     * @param mixed $needle_to
     * @return String2
     */
    public function replace(mixed $needle_from, mixed $needle_to): String2
    {
        if ($this->data !== null) {
            $this->data = str_replace($needle_from, $needle_to, $this->data);
        }
        return $this;
    }

    /**
     * Repeat
     *
     * @param int $times
     * @param string|null $string
     * @return String2
     */
    public function repeat(int $times, ?string $string = null): String2
    {
        if ($string !== null) {
            $this->data = str_repeat($string, $times);
        } elseif ($this->data !== null) {
            $this->data = str_repeat($this->data, $times);
        }
        return $this;
    }

    /**
     * First character uppercase
     *
     * @return String2
     */
    public function ucfirst(): String2
    {
        if ($this->data !== null) {
            $this->data = ucfirst($this->data);
        }
        return $this;
    }

    /**
     * First character lowercase
     *
     * @return String2
     */
    public function lcfirst(): String2
    {
        if ($this->data !== null) {
            $this->data = lcfirst($this->data);
        }
        return $this;
    }

    /**
     * Capitalize all words
     *
     * @return String2
     */
    public function ucwords(): String2
    {
        if ($this->data !== null) {
            $this->data = ucwords($this->data);
        }
        return $this;
    }

    /**
     * Word count
     *
     * @return int
     */
    public function wordCount(): int
    {
        return str_word_count($this->data ?? '');
    }

    /**
     * URL encode
     *
     * @return String2
     */
    public function urlencode(): String2
    {
        if ($this->data !== null) {
            $this->data = urlencode($this->data);
        }
        return $this;
    }

    /**
     * URL decode
     *
     * @return String2
     */
    public function urldecode(): String2
    {
        if ($this->data !== null) {
            $this->data = urldecode($this->data);
        }
        return $this;
    }

    /**
     * Parse URL
     *
     * @return Array2
     */
    public function parseUrl(): Array2
    {
        return new Array2(parse_url($this->data ?? ''));
    }

    /**
     * Parse string
     *
     * @return Array2
     */
    public function parseStr(): Array2
    {
        $result = [];
        parse_str($this->data ?? '', $result);
        return new Array2($result);
    }

    /**
     * Pad string
     *
     * @param int $length
     * @param string $string
     * @param int $type
     * 		STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
     * @return String2
     */
    public function pad(int $length, string $string = ' ', int $type = STR_PAD_RIGHT): String2
    {
        $this->data = str_pad($this->data ?? '', $length, $string, $type);
        return $this;
    }

    /**
     * Similar
     *
     * @param string $string
     * @param string $type - either 'chars' or 'percent'
     * @return int|float
     */
    public function similar(string $string, string $type = 'chars'): int|float
    {
        $percent = 0;
        $chars = similar_text($this->data ?? '', $string, $percent);
        if ($type == 'chars') {
            return $chars;
        } else {
            return $percent;
        }
    }

    /**
     * Levenshtein
     *
     * @param string $strin
     * @return int
     */
    public function levenshtein(string $string): int
    {
        return levenshtein($this->data ?? '', $string);
    }

    /**
     * MD5
     *
     * @return String2
     */
    public function md5(): String2
    {
        $this->data = md5($this->data ?? '');
        return $this;
    }

    /**
     * SHA1
     *
     * @return String2
     */
    public function sha1(): String2
    {
        $this->data = sha1($this->data ?? '');
        return $this;
    }

    /**
     * Position (case-sensitive)
     *
     * @param int $needle
     * @return int|bool
     */
    public function position(string $needle): int|bool
    {
        return strpos($this->data ?? '', $needle);
    }

    /**
     * Position (case-insensitive)
     *
     * @param int $needle
     * @return int|bool
     */
    public function iposition(string $needle): int|bool
    {
        return stripos($this->data ?? '', $needle);
    }

    /**
     * Length
     *
     * @return int
     */
    public function length(): int
    {
        return strlen($this->data ?? '');
    }

    /**
     * Starts with
     *
     * @param string $needle
     * @return bool
     */
    public function starts(string $needle): bool
    {
        if ($this->data !== null) {
            return str_starts_with($this->data, $needle);
        }
        return false;
    }

    /**
     * Ends with
     *
     * @param string $needle
     * @return bool
     */
    public function ends(string $needle): bool
    {
        if ($this->data !== null) {
            return str_ends_with($this->data, $needle);
        }
        return false;
    }

    /**
     * Contains
     *
     * @param string $needle
     * @return bool
     */
    public function contains(string $needle): bool
    {
        if ($this->data !== null) {
            return strpos($this->data, $needle) !== false;
        }
        return false;
    }

    /**
     * Truncate
     *
     * @param int $length
     * @param string $mask
     * @return String2
     */
    public function truncate(int $length, string $mask = '...'): String2
    {
        if ($this->data !== null) {
            if (strlen($this->data) > $length) {
                $this->data = substr($this->data, 0, $length - strlen($mask)) . $mask;
            }
        }
        return $this;
    }

    /**
     * Mask given string
     *
     * @param string $mask
     * @param int $skip_before
     * @param int $skip_after
     * @return String2
     */
    public function mask(string $mask = '*', int $skip_before = 0, int $skip_after = 0): String2
    {
        if ($this->data !== null) {
            $length = strlen($this->data);
            for ($i = 0; $i < $length; $i++) {
                if ($skip_before > 0 && $i < $skip_before) {
                    continue;
                }
                if ($skip_after > 0 && $i > $length - $skip_after - 1) {
                    continue;
                }
                $this->data[$i] = $mask;
            }
        }
        return $this;
    }

    /**
     * Get string after the needle
     *
     * @param string $needle
     * @return String2
     */
    public function after(string $needle): String2
    {
        if ($this->data !== null) {
            $exploded = explode($needle, $this->data, 2);
            // if we do not have needle
            if (count($exploded) == 1) {
                $this->data = null;
            } else {
                $this->data = array_reverse($exploded)[0];
            }
        }
        return $this;
    }

    /**
     * Get string before the needle
     *
     * @param string $needle
     * @return String2
     */
    public function before(string $needle): String2
    {
        if ($this->data !== null) {
            $exploded = explode($needle, $this->data, 2);
            // if we do not have needle
            if (count($exploded) == 1) {
                $this->data = null;
            } else {
                $this->data = $exploded[0];
            }
        }
        return $this;
    }

    /**
     * Between
     *
     * @param string $needle_from
     * @param string $needle_to
     * @return String2
     */
    public function between(string $needle_from, string $needle_to): String2
    {
        if ($this->data !== null) {
            $this->after($needle_from)->before($needle_to);
        }
        return $this;
    }

    /**
     * Print
     *
     * @param string $name
     */
    public function print(string $name = '')
    {
        print_r2($this->data, $name);
    }

    /**
     * Append
     *
     * @param string $string
     * @return String2
     */
    public function append(string $string): String2
    {
        $this->data = $this->data . $string;
        return $this;
    }

    /**
     * Prepend
     *
     * @param string $string
     * @return String2
     */
    public function prepend(string $string): String2
    {
        $this->data = $string . $this->data;
        return $this;
    }

    /**
     * New line
     *
     * @param int $count
     * @param string $separator
     * @return String2
     */
    public function nl(int $count = 1, string $separator = PHP_EOL): String2
    {
        if ($count < 1) {
            $count = 1;
        }
        return $this->append(str_repeat($separator, $count));
    }

    /**
     * Base name
     *
     * @param string $suffix
     * @return String2
     */
    public function basename(string $suffix = ''): String2
    {
        return new static(basename($this->data, $suffix));
    }


    /**
     * Dir name
     *
     * @param int $levels
     * @return String2
     */
    public function dirname(int $levels = 1): String2
    {
        return new static(dirname($this->data, $levels));
    }

    /**
     * Camel case
     *
     * @param string $separator
     * @return String2
     */
    public function camelCase(string $separator = ' '): String2
    {
        if ($this->data !== null) {
            $all_words = explode($separator, str_replace(['-', '_'], $separator, $this->data));
            $uc_words = array_map(fn ($word) => ucfirst($word), $all_words);
            $uc_words[0] = lcfirst($uc_words[0]);
            $this->data = implode('', $uc_words);
        }
        return $this;
    }

    /**
     * Pascal case
     *
     * @param string $separator
     * @return String2
     */
    public function pascalCase(string $separator = ' '): String2
    {
        if ($this->data !== null) {
            $all_words = explode($separator, str_replace(['-', '_'], $separator, $this->data));
            $uc_words = array_map(fn ($word) => ucfirst($word), $all_words);
            $this->data = implode('', $uc_words);
        }
        return $this;
    }

    /**
     * Space on uppercase characters
     *
     * @return String2
     */
    public function spaceOnUpperCase(): String2
    {
        if ($this->data !== null) {
            $this->data = trim(preg_replace("([A-Z])", " $0", $this->data));
        }
        return $this;
    }

    /**
     * Snake case
     *
     * @param string $separator
     * @return String2
     */
    public function snakeCase(string $separator = ' '): String2
    {
        if ($this->data !== null) {
            $all_words = explode($separator, str_replace(['-', '_'], $separator, $this->data));
            $this->data = implode('_', $all_words);
        }
        return $this;
    }

    /**
     * Kebab case
     *
     * @param string $separator
     * @return String2
     */
    public function kebabCase(string $separator = ' '): String2
    {
        if ($this->data !== null) {
            $all_words = explode($separator, str_replace(['-', '_'], $separator, $this->data));
            $this->data = implode('-', $all_words);
        }
        return $this;
    }

    /**
     * Exactly
     *
     * @param mixed $string
     * @return bool
     */
    public function exactly(mixed $string): bool
    {
        if ($string instanceof String2) {
            $string = $string->toString();
        }
        return $this->data === $string;
    }

    /**
     * Split returning \Array2
     *
     * @param mixed $regex
     * @param int $limit
     * @param int $flags
     * @return Array2
     */
    public function split(mixed $regex, int $limit = -1, int $flags = 0): Array2
    {
        if ($this->data === null) {
            return new Array2([]);
        }
        if (is_int($regex)) {
            return new Array2(mb_str_split($this->data, $regex));
        }
        $parts = preg_split($regex, $this->data, $limit, $flags);
        return !empty($parts) ? new Array2($parts) : new Array2([]);
    }

    /**
     * Explode returning \Array2
     *
     * @param string $delimiter
     * @param int $limit
     * @return Array2
     */
    public function explode(string $delimiter, int $limit = PHP_INT_MAX): Array2
    {
        if ($this->data !== null) {
            return new Array2(explode($delimiter, $this->data, $limit));
        } else {
            return new Array2([null]);
        }
    }

    /**
     * Is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->data === '' || $this->data === null;
    }

    /**
     * Is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * English only plus numbers
     *
     * @param bool $pascal_case
     * @return String2
     */
    public function englishOnly(bool $pascal_case = false): String2
    {
        if ($this->data !== null) {
            $this->pascalCase();
            if (str_starts_with($this->data, '#')) {
                $this->data = str_replace('#', 'Number', $this->data);
            } else {
                $this->data = str_replace('#', 'ID', $this->data);
            }
            $this->data = preg_replace('/[^a-zA-Z0-9]/i', '', $this->data);
        }
        return $this;
    }

    /**
     * Modulize
     *
     * @return String2
     */
    public function modulize(): String2
    {
        if ($this->data !== null) {
            $this->data = $this->data[0] . '/' . $this->data[1];
        }
        return $this;
    }

    /**
     * Replace parameters with options
     *
     * @param array $options
     * @return String2
     */
    public function replaceParametersOptions(array $options): String2
    {
        if ($this->data !== null && strpos($this->data, '{') !== false) {
            if (preg_match_all('/{(.*?)}/', $this->data, $matches, PREG_PATTERN_ORDER)) {
                foreach ($matches[1] as $v) {
                    $this->data = str_replace('{' . $v . '}', $options[$v], $this->data);
                }
            }
        }
        return $this;
    }
}
