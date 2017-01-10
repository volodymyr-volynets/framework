<?php

/**
 * Command line helper
 */
class helper_cmd {

	/**
	 * Confirm
	 */
	public static function confirm($message) {
		echo "\n" . $message . " [Yes/No]: ";
		$line = fgets(STDIN);
		$line = strtolower(trim($line));
		if (!($line == 'y' || $line == 'yes')) {
			echo "\nAborted...\n\n";
			return false;
		} else {
			echo "\n";
			return true;
		}
	}
}