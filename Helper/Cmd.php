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

class Cmd
{
    /**
     * Console resource
     *
     * @var resource
     */
    public static $console = null;

    /**
     * @var string
     */
    public static string $output = '';

    /**
     * Confirm
     *
     * @param string $message
     * @param array $options
     *		boolean suppress_echo
     * @return boolean
     */
    public static function confirm($message, $options = [])
    {
        $options['text_color'] = $options['text_color'] ?? 'red';
        $options['background_color'] = $options['background_color'] ?? null;
        $options['bold'] = $options['bold'] ?? true;
        echo self::colorString("\n{$message} [Yes/No]: ", $options['text_color'], $options['background_color'], $options['bold']);
        $line = fgets(STDIN);
        $line = strtolower(trim($line));
        if (!($line == 'y' || $line == 'yes')) {
            if (empty($options['suppress_echo'])) {
                echo self::colorString("\nAborted...\n\n", $options['text_color'], $options['background_color'], $options['bold']);
            }
            return false;
        } else {
            if (empty($options['suppress_echo'])) {
                echo "\n";
            }
            return true;
        }
    }

    /**
     * Ask
     *
     * @param string $message
     * @param array $options
     *		boolean mandatory
     *		array only_these
     *		boolean bold
     *		string background_color
     *		string text_color
     *      string type
     * @return string
     */
    public static function ask($message, $options = [])
    {
        if (isset($options['type'])) {
            $preset = self::PRESETS[$options['type']] ?? self::PRESETS[GENERAL];
            $options = array_merge_hard($preset, $options);
        } else {
            $options['text_color'] = $options['text_color'] ?? 'green';
            $options['background_color'] = $options['background_color'] ?? null;
            $options['bold'] = $options['bold'] ?? true;
        }
        reask:
            $temp = "\n" . self::colorString($message, $options['text_color'], $options['background_color'], $options['bold']) . ": ";
        self::$output .= $temp;
        echo $temp;
        $result = trim(fgets(STDIN));
        if (!empty($options['function'])) {
            $result = $options['function']($result);
        }
        if (!empty($options['mandatory'])) {
            if (empty($result)) {
                goto reask;
            }
        }
        if (!empty($options['only_these'])) {
            if (!is_array($options['only_these'])) {
                $options['only_these'] = [$options['only_these']];
            }
            if (!in_array($result, $options['only_these'])) {
                goto reask;
            }
        }
        self::$output .= $result;
        return $result;
    }

    /**
     * Background colors
     */
    public const color_background = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47'
    ];

    /**
     * Text colors
     */
    public const color_text = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37'
    ];

    /**
     * Presets
     */
    public const PRESETS = [
        GENERAL => ['text_color' => 'green', 'background_color' => null, 'bold' => false],
        SUCCESS => ['text_color' => 'green', 'background_color' => null, 'bold' => true],
    ];

    /**
     * Color string
     *
     * @param string $string
     * @param string $text_color
     * @param string $background_color
     * @param boolean $bold
     * @return string
     */
    public static function colorString($string, $text_color = null, $background_color = null, $bold = false)
    {
        $result = "";
        // text color
        if (isset(self::color_text[$text_color])) {
            $result .= "\033[" . self::color_text[$text_color] . "m";
        }
        // background color
        if (isset(self::color_background[$background_color])) {
            $result .= "\033[" . self::color_background[$background_color] . "m";
        }
        // bold
        if ($bold) {
            $result .= "\033[1m";
        }
        $result .=  $string . "\033[0m";
        return $result;
    }

    /**
     * Is command line interface (cli)
     *
     * @return boolean
     */
    public static function isCli(): bool
    {
        return (php_sapi_name() == 'cli');
    }

    /**
     * Show progress bar
     *
     * @param int $done
     * @param int $total
     */
    public static function progressBar(int $done, int $total = 100, string $description = '')
    {
        if (!self::isCli()) {
            return;
        }
        $percent = round(($done / $total) * 100, 0);
        $left = 100 - $percent;
        $write = sprintf("\033[0G\033[2K[%'={$percent}s>%-{$left}s] - $percent%% - $done/$total - $description", "", "");
        self::$output .= $write;
        fwrite(STDERR, $write);
    }

    /**
     * Execute command
     *
     * @param string $command
     * @return array
     */
    public static function executeCommand(string $command): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => null
        ];
        $escaped_command = escapeshellcmd($command);
        $temp = null;
        exec("{$escaped_command} 2>&1", $result['data'], $temp);
        if (empty($temp)) {
            $result['success'] = true;
        } else {
            $result['error'][] = 'Cmd error occured, status = ' . $temp;
        }
        return $result;
    }

    /**
     * Echo message
     *
     * @param string $string
     * @param string $text_color
     * @param string $background_color
     * @param boolean $bold
     */
    public static function message($string, $text_color = null, $background_color = null, $bold = false)
    {
        $result = "\n" . Cmd::colorString($string, $text_color, $background_color, $bold) . "\n";
        self::$output .= $result;
        if (self::$console) {
            if (@fwrite(self::$console, $result) === false) {
                self::$console = null;
            }
        } else {
            echo $result;
        }
    }

    /**
     * Writeln
     *
     * @param string $type
     * @param mixed $data
     * @param array $options
     * @return void
     */
    public static function writeln(string $type = GENERAL, mixed $data = null, array $options = []): void
    {
        if (is_null($data)) {
            return;
        }
        if (is_scalar($data)) {
            $data = [$data];
        }
        $preset = self::PRESETS[$type] ?? self::PRESETS[GENERAL];
        $preset = array_merge_hard($preset, $options);
        foreach ($data as $v) {
            $result = Cmd::colorString($v, $preset['text_color'], $preset['background_color'], $preset['bold']) . "\n";
            self::$output .= $result;
            if (self::$console) {
                if (@fwrite(self::$console, $result) === false) {
                    self::$console = null;
                }
            } else {
                echo $result;
            }
        }
    }

    /**
     * Initialize console
     *
     * @param string $console
     */
    public static function initializeConsole($console)
    {
        if (!empty($console) && ($fd = fopen($console, "c")) !== false) {
            self::$console = $fd;
        }
    }
}
