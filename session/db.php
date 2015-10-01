<?php

class session_db implements session_interface {

	/**
	 * Options are kept here
	 * 
	 * @var type 
	 */
	private static $options;

	/**
	 * Starting session
	 * 
	 * @param array $options
	 */
	public static function start($options) {
		// putting data into static variable
		self::$options = $options;
		
		// table and db link
		if (!isset(self::$options['table'])) self::$options['table'] = 'sessions';
		if (!isset(self::$options['db_link'])) self::$options['db_link'] = 'default';
		
		// overriding session handlers using new functions
		session_set_save_handler(
			array('session_db', '_open'),
			array('session_db', '_close'),
			array('session_db', '_read'),
			array('session_db', '_write'),
			array('session_db', '_destroy'),
			array('session_db', '_gc')
		);
	}

	/**
	 * Open a session
	 * 
	 * @param string $path
	 * @param string $name
	 * @return boolean
	 */
	public static function _open($path, $name) {
		return true;
	}

	/**
	 * Close session
	 * 
	 * @return boolean
	 */
	public static function _close() {
		return true;
	}

	/**
	 * Read session data
	 * 
	 * @param string $id
	 */
	public static function _read($id) {
		$result = db::query("SELECT * FROM " . self::$options['table'] . " WHERE ss_session_id = '" . db::escape($id, self::$options['db_link']) . "' AND ss_session_expires >= now()", null, array(), self::$options['db_link']);
		if ($result['num_rows'] == 1) {
			return $result['rows'][0]['ss_session_values'];
		}
	}

	/**
	 * Write session data
	 * 
	 * @param string $id
	 * @param array $data
	 * @return boolean
	 */
	public static function _write($id, $data) {
		$ip = request::ip();
		$user_id = !empty(self::$options['get_user_id_function']) ? call_user_func(self::$options['get_user_id_function']) : '';
		$result = db::query("UPDATE " . self::$options['table'] . " SET ss_session_expires = now() + interval '" . self::$options['gc_maxlifetime'] . " seconds', ss_session_values = '" . db::escape($data, self::$options['db_link']) . "', ss_session_last_requested = now(), ss_session_user_ip = '" . db::escape($ip, self::$options['db_link']) . "', ss_session_user_id = '" . db::escape($user_id, self::$options['db_link']) . "', ss_session_pages_count = ss_session_pages_count + 1 WHERE ss_session_id = '" . db::escape($id, self::$options['db_link']) . "'", null, array(), self::$options['db_link']);
		if (empty($result['affected_rows'])) {
			$result = db::query("INSERT INTO " . self::$options['table'] . " (ss_session_id, ss_session_expires, ss_session_values, ss_session_user_ip, ss_session_user_id, ss_session_pages_count) VALUES ('" .  db::escape($id, self::$options['db_link']) . "', now() + interval '" . self::$options['gc_maxlifetime'] . " seconds', '" .  db::escape($data, self::$options['db_link']) . "', '" . db::escape($ip, self::$options['db_link']) . "', '" . db::escape($user_id, self::$options['db_link']) . "', 1)", null, array(), self::$options['db_link']);
		}
		return true;
	}

	/**
	 * Destroy the session
	 * 
	 * @param string $id
	 * @return boolean
	 */
	public static function _destroy($id) {
		// we set session expired 10 seconds ago, gc will do the rest
		$result = db::query("UPDATE " . self::$options['table'] . " SET ss_session_expires = now() - interval '10 seconds', ss_session_last_requested = now() WHERE ss_session_id = '" .  db::escape($id, self::$options['db_link']) . "'", null, array(), self::$options['db_link']);
		return true;
	}

	/**
	 * Garbage collector
	 * 
	 * @param int $life
	 * @return boolean
	 */
	public static function _gc($life) {
		$result1 = db::query("DELETE FROM " . self::$options['table'] . " WHERE ss_session_expires < now()", null, array(), self::$options['db_link']);
		$result2 = db::query("VACUUM " . self::$options['table'], null, array(), self::$options['db_link']);
		return true;
	}

	/**
	 * Find active session by user id
	 * 
	 * @param string $user_id
	 */
	public static function get_active_session_by_user_id($user_id) {
		$result = Db::query("SELECT ss_session_id FROM " . self::$options['table'] . " WHERE ss_session_user_id = '" . db::escape($user_id, self::$options['db_link']) . "' AND sys_sess_expires >= now()");
		return @$result['rows'][0]['ss_session_id'];
	}
}
