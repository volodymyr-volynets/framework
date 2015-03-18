<?php

class session {
	
    /**
     * Max life time
     * 
     * @var int
     */
    private static $gc_maxlifetime = 0;
    
    /**
     * Table name
     * 
     * @var string
     */
    private static $table;

    /**
     * Db link
     * 
     * @var string
     */
    private static $db_link;
    
    /**
     * Get current user id function
     * 
     * @var string
     */
    private static $get_user_id_function;
    
    /**
     * Array of default options
     * 
     * @var array
     */
    private static $_default_options = array(
        'save_path'                 => null,
        'name'                      => null,
        'save_handler'              => null,
        'gc_probability'            => 1,
        'gc_divisor'                => 1000,
        'gc_maxlifetime'            => 7200,
        'serialize_handler'         => null,
        'cookie_lifetime'           => null,
        'cookie_path'               => null,
        'cookie_domain'             => null,
        'cookie_secure'             => null,
        'cookie_httponly'           => null,
        'use_cookies'               => null,
        'use_only_cookies'          => 'on',
        'referer_check'             => null,
        'entropy_file'              => null,
        'entropy_length'            => null,
        'cache_limiter'             => null,
        'cache_expire'              => null,
        'use_trans_sid'             => null,
        'bug_compat_42'             => null,
        'bug_compat_warn'           => null,
        'hash_function'             => null,
        'hash_bits_per_character'   => null
    );
    
    public static function start($options) {
        // table, db link and lifetime
        self::$table = @$options['table'] ? $options['table'] : 'sessions';
        self::$db_link = @$options['db_link'] ? $options['db_link'] : 'default';
        self::$gc_maxlifetime = @$options['gc_maxlifetime'] ? $options['gc_maxlifetime'] : self::$_default_options['gc_maxlifetime'];
        self::$get_user_id_function = @$options['get_user_id_function'];
        unset($options['table'], $options['db_link'], $options['get_user_id_function']);
        
        // setting default options
        foreach (self::$_default_options as $k=>$v) {
            if (isset($options[$k])) {
                ini_set("session.$k", $options[$k]);
            } else if (isset(self::$_default_options[$k])) {
                ini_set("session.$k", $v);
            }
        }
	
        // overriding session handlers using new functions
        session_set_save_handler(
            array('Session', '_open'),
            array('Session', '_close'),
            array('Session', '_read'),
            array('Session', '_write'),
            array('Session', '_destroy'),
            array('Session', '_gc')
        );
        
        // starting session
        session_start();
    }

    /**
     * Destroy the session
     */
    public static function destroy() {
    	// remove session variable from cookies
    	setcookie(session_name(), '', time() - 3600, '/');
    	// destroy the session.
    	session_destroy();
    	session_write_close();
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
    	$result = db::query("SELECT * FROM " . self::$table . " WHERE ss_session_id = '" . db::escape($id, self::$db_link) . "' AND ss_session_expires >= now()", null, array(), self::$db_link);
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
    	$user_id = self::$get_user_id_function ? call_user_func(self::$get_user_id_function) : 0;
    	$result = db::query("UPDATE " . self::$table . " SET ss_session_expires = now() + interval '" . session::$gc_maxlifetime . " seconds', ss_session_values = '" . db::escape($data, self::$db_link) . "', ss_session_last_requested = now(), ss_session_user_ip = '" . db::escape($ip, self::$db_link) . "', ss_session_user_id = $user_id, ss_session_pages_count = ss_session_pages_count + 1 WHERE ss_session_id = '" . db::escape($id, self::$db_link) . "'", null, array(), self::$db_link);
    	if (empty($result['affected_rows'])) {
			$result = db::query("INSERT INTO " . self::$table . " (ss_session_id, ss_session_expires, ss_session_values, ss_session_user_ip, ss_session_user_id, ss_session_pages_count) VALUES ('" .  db::escape($id, self::$db_link) . "', now() + interval '" . session::$gc_maxlifetime . " seconds', '" .  db::escape($data, self::$db_link) . "', '" . db::escape($ip, self::$db_link) . "', $user_id, 1)", null, array(), self::$db_link);
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
    	$result = db::query("UPDATE " . self::$table . " SET ss_session_expires = now() - interval '10 seconds', ss_session_last_requested = now() WHERE ss_session_id = '" .  db::escape($id, self::$db_link) . "'", null, array(), self::$db_link);
    	return true;
    }

    /**
     * Garbage collector
     * 
     * @param int $life
     * @return boolean
     */
    public static function _gc($life) {
    	$result1 = db::query("DELETE FROM " . self::$table . " WHERE ss_session_expires < now()", null, array(), self::$db_link);
    	$result2 = db::query("VACUUM " . self::$table, null, array(), self::$db_link);
    	return true;
    }
    
    /**
     * Find active session by entity id
     * 
     * @param string $ss_session_user_id
     */
    public static function get_active_session_by_entity_id($ss_session_user_id) {
    	$result = Db::query("SELECT ss_session_id FROM " . self::$table . " WHERE ss_session_user_id = " . intval($ss_session_user_id) . " AND sys_sess_expires >= now()");
    	return @$result['rows'][0]['ss_session_id'];
    }
    
    /**
     * Set session values as static
     * 
     * @param type $key
     * @param type $value
     */
    public static function set($key, $value) {
		array_key_set($_SESSION, $key, $value);
    }
    
    /**
     * Get session values as static
     * 
     * @param string $key
     * @return type
     */
    public static function get($key = '') {
		return array_key_get($_SESSION, $key);
    }
    
    /**
     * Set session variable
     * 
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value) {
		array_key_set($_SESSION, $key, $value);
    }

    /**
     * Get session values
     * 
     * @param type $key
     * @return type
     */
    public function __get($key) {
		return array_key_get($_SESSION, $key);
    }

    /**
     * Isset check in sessions
     * 
     * @param type $key
     * @return type
     */
    public function __isset($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Unsetting a key in sessions
     * 
     * @param type $key
     */
    public function __unset($key) {
        unset($_SESSION[$key]);
    }
}