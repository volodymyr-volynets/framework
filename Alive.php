<?php

class Alive {

	public static $alive = false;
	public static $buffer = '';
	public static $ob_start = false;

	/**
	 * Start
	 */
	public static function start() {
		// get buffer
		if (ob_get_level()) {
			self::$ob_start = true;
			self::$buffer = @ob_get_clean();
		}
		register_tick_function('alive_tick');
		declare(ticks = 200000);
		self::$alive = true;
	}

	/**
	 * Stop
	 */
	public static function stop() {
		unregister_tick_function('alive_tick');
		if (self::$ob_start) {
			ob_start();
			echo self::$buffer;
		}
	}
}

/**
 * Echo and flush spaces
 */
function alive_tick() {
	// we exit if configured to
	if (\Application::get('flag.alive.exit_on_disconnect')) {
		if (connection_aborted()) {
			exit;
		}
	}
	// send space to frontend
	echo ' ';
	flush();
}