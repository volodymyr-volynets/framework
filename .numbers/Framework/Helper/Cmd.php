<?php

/**
 * Command line helper
 */
namespace Helper;
class Cmd {

	/**
	 * Confirm
	 *
	 * @param string $message
	 * @param array $options
	 *		boolean suppress_echo
	 * @return boolean
	 */
	public static function confirm($message, $options = []) {
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
	 * @return string
	 */
	public static function ask($message, $options = []) {
		$options['text_color'] = $options['text_color'] ?? 'green';
		$options['background_color'] = $options['background_color'] ?? null;
		$options['bold'] = $options['bold'] ?? true;
reask:
		echo "\n" . self::colorString($message, $options['text_color'], $options['background_color'], $options['bold']) . ": ";
		$result = trim(fgets(STDIN));
		if (!empty($options['function'])) {
			$result = $options['function']($result);
		}
		if (!empty($options['mandatory'])) {
			if (empty($result)) goto reask;
		}
		if (!empty($options['only_these'])) {
			if (!is_array($options['only_these'])) $options['only_these'] = [$options['only_these']];
			if (!in_array($result, $options['only_these'])) goto reask;
		}
		return $result;
	}

	/**
	 * Background colors
	 */
	const color_background = [
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
	const color_text = [
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
	 * Color string
	 *
	 * @param string $string
	 * @param string $text_color
	 * @param string $background_color
	 * @return string
	 */
	public static function colorString($string, $text_color = null, $background_color = null, $bold = false) {
		$result = "";
		// text color
		if (isset(self::color_text[$text_color])) {
			$result.= "\033[" . self::color_text[$text_color] . "m";
		}
		// background color
		if (isset(self::color_background[$background_color])) {
			$result.= "\033[" . self::color_background[$background_color] . "m";
		}
		// bold
		if ($bold) {
			$result.= "\033[1m";
		}
		$result.=  $string . "\033[0m";
		return $result;
	}

	/**
	 * Is command line interface (cli)
	 *
	 * @return boolean
	 */
	public static function isCli() : bool {
		return (php_sapi_name() == 'cli');
	}

	/**
	 * Show progress bar
	 *
	 * @param int $done
	 * @param int $total
	 */
	public static function progressBar(int $done, int $total = 100, string $description = '') {
		if (!self::isCli()) return;
		$percent = round(($done / $total) * 100, 0);
		$left = 100 - $percent;
		$write = sprintf("\033[0G\033[2K[%'={$percent}s>%-{$left}s] - $percent%% - $done/$total - $description", "", "");
		fwrite(STDERR, $write);
	}

	/**
	 * Execute command
	 *
	 * @param string $command
	 * @return array
	 */
	public static function executeCommand(string $command) : array {
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
}