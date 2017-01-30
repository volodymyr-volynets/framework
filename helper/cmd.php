<?php

/**
 * Command line helper
 */
class helper_cmd {

	/**
	 * Confirm
	 *
	 * @param string $message
	 * @param array $options
	 * @return boolean
	 */
	public static function confirm($message, $options = []) {
		$options['text_color'] = $options['text_color'] ?? 'red';
		$options['background_color'] = $options['background_color'] ?? null;
		$options['bold'] = $options['bold'] ?? true;
		echo self::color_string("\n{$message} [Yes/No]: ", $options['text_color'], $options['background_color'], $options['bold']);
		$line = fgets(STDIN);
		$line = strtolower(trim($line));
		if (!($line == 'y' || $line == 'yes')) {
			echo self::color_string("\nAborted...\n\n", $options['text_color'], $options['background_color'], $options['bold']);
			return false;
		} else {
			echo "\n";
			return true;
		}
	}

	/**
	 * Ask
	 *
	 * @param string $message
	 * @param array $options
	 * @return string
	 */
	public static function ask($message, $options = []) {
		$options['text_color'] = $options['text_color'] ?? 'green';
		$options['background_color'] = $options['background_color'] ?? null;
		$options['bold'] = $options['bold'] ?? true;
		echo "\n" . self::color_string($message, $options['text_color'], $options['background_color'], $options['bold']) . ": ";
		return trim(fgets(STDIN));
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
	public static function color_string($string, $text_color = null, $background_color = null, $bold = false) {
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
}