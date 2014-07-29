<?php
/**
 * Internal Session handler to store sessions into memcache
 *
 * @name SessionHandler
 */
class SessionHandler {

	/**
	 * The session lifetime
	 * 60*60*24*30 => 1 month
	 * @var int
	 */
	public $life_time = 2592000;

	/**
	 * Singleton instance of the session handler
	 * @var SessionHandler
	 */
	private static $instance;

	/**
	 * The initial value of the session when it was read
	 * @var array
	 */
	protected $initial_value = array();

	/**
	 * Create a new instance of SessionHandler
	 * @param string $session_id
	 */
	protected function __construct($session_id = null) {
		if(!is_null($session_id)) {
			session_id($session_id);
		}

		session_set_save_handler(
			array(&$this, 'open'),
			array(&$this, 'close'),
			array(&$this, 'read'),
			array(&$this, 'write'),
			array(&$this, 'destroy'),
			array(&$this, 'gc')
		);

		// set the cookie parameters for this session
		session_set_cookie_params($this->life_time, '/', Config::$cookie_domain);

		// attempt to start the session
		@session_start();
	}

	/**
	 * Retrieve the singleton instance of the session handler
	 *
	 * @param string $session_id
	 * @return SessionHandler
	 */
	public static function create($session_id = null) {
		if(is_null(self::$instance)) {
			self::$instance = new self($session_id);
		}
		return self::$instance;
	}

	/**
	 * destroys a session in the most paranoid of all possible ways
	 * 1. unset superglobal php vars
	 * 2. unset the session cookie
	 * 3. generate a new session id
	 * 4. call PHP's built in session destroyer
	 */
	public function destroySession() {
		$_SESSION = array();
		if(session_id() != '' || isset($_COOKIE[session_name()])) {
			session_regenerate_id(true);
			setcookie(session_name(), '', time() - 3600, '/', Config::$cookie_domain);
		}
		session_destroy();
	}

	/**
	 * returns session id
	 * @return string
	 */
	public function getSessionID() {
		return session_id();
	}

	/**
	 * Session handler callback function for open
	 *
	 * @param string $save_path
	 * @param string $session_name
	 * @return bool
	 */
	public function open($save_path, $session_name) {
		return true;
	}

	/**
	 * Session handler callback function for close
	 *
	 * @return bool
	 */
	public function close() {
		return true;
	}

	/**
	 * Session handler callback function for session retrieval
	 *
	 * @param string $session_id
	 * @return bool
	 */
	public function read($session_id) {
		$this->initial_value[$session_id] = SynMemcache::getInstance()->get('SESSION_A_' . $session_id);
		return $this->initial_value[$session_id];
	}

	/**
	 * Session handler callback function for session write
	 *
	 * @param string $session_id
	 * @param string $session_data
	 * @return bool
	 */
	public function write($session_id, $session_data) {
		/**
		 * Only write back to store if
		 * 1. There was no initial value?
		 * 2. If we didn't have an initial value and something changed
		 * 3. If something changed in general
		 */
		if(!isset($this->initial_value[$session_id]) || ($this->initial_value[$session_id] === false && $session_data) || $this->initial_value[$session_id] != $session_data) {
			$this->initial_value[$session_id] = $session_data;
			SynMemcache::getInstance()->set('SESSION_A_' . $session_id, $session_data, $this->life_time);
		}
		return true;
	}

	/**
	 * Session handler callback function for session destroy
	 *
	 * @param string $session_id
	 * @return bool
	 */
	public function destroy($session_id) {
		SynMemcache::getInstance()->delete('SESSION_A_' . $session_id);
		return true;
	}

	/**
	 * Session handler callback function for session garbage collection
	 *
	 * @param int $session_max_lifetime
	 * @return bool
	 */
	public function gc($session_max_lifetime) {
		return true;
	}
}